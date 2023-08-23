<?php
/**
 * @var CUser $USER
 * @var CMain $APPLICATION
 * @var array $arResult
 */

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Tasks\Internals\Counter\CounterDictionary as TasksCounterDictionary;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
// region for
Main\Localization\Loc::loadMessages(__FILE__);
use \Bitrix\Intranet\UI\LeftMenu;
//Make some preparations. I do not know what it means.
if ($presetId = \CUserOptions::GetOption("intranet", "left_menu_preset"))
{
	\CUserOptions::SetOption("intranet", "left_menu_preset_".SITE_ID, $presetId);
	\CUserOptions::DeleteOption("intranet", "left_menu_preset", false, $USER->GetID());
}
//endregion

$defaultItems = $arResult;
$menuUser = new LeftMenu\User();
$menu = new LeftMenu\Menu($defaultItems, $menuUser);
$activePreset = LeftMenu\Preset\Manager::getPreset();
$menu->applyPreset($activePreset);

$arResult = [
	'IS_ADMIN' => $menuUser->isAdmin(),
	'IS_EXTRANET' => isModuleInstalled("extranet") && SITE_ID    == \COption::GetOptionString("extranet", "extranet_site"),
	'SHOW_PRESET_POPUP' => \COption::GetOptionString("intranet", "show_menu_preset_popup", "N") == "Y",
    'SHOW_SITEMAP_BUTTON' => false,
    'SHOW_SETTINGS_BUTTON' => false,
	'ITEMS' => [
		'show' => $menu->getVisibleItems(),
		'hide' => $menu->getHiddenItems()
	],
	'IS_CUSTOM_PRESET_AVAILABLE' => LeftMenu\Preset\Custom::isAvailable(),
	'CURRENT_PRESET_ID' => $activePreset->getCode(),
	'WORKGROUP_COUNTER_DATA' => [],
];

foreach ($arResult['ITEMS'] as $visibility => $items)
{
    foreach ($items as $key => $item)
    {
        if (str_starts_with($item['ID'], 'menu_custom_section_'))
        {
            if (!is_array($item['PARAMS']))
            {
                $item['PARAMS'] = [];
            }

            $item['PARAMS']["counter_id"] = str_replace('menu_custom_section_', '', $item['ID']);
        }
        $arResult['ITEMS'][$visibility][$key] = $item;

        if (Loader::includeModule('cbit.mc.staffing'))
        {
            if ($item['ID'] === 'menu_custom_section_'.\Cbit\Mc\Staffing\Config\Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE)
            {
                if ($GLOBALS['USER'])
                {
                    if (!(Main\Engine\CurrentUser::get()->isAdmin()
                        || \Cbit\Mc\Staffing\Service\Container::getInstance()->getUserPermissions()->hasPdStaffingPermissions())
                    ){
                        unset($arResult['ITEMS'][$visibility][$key]);
                    }
                }
            }
        }
    }
}

/*if ($arResult["IS_EXTRANET"] === false && count($defaultItems) > 0)
{
	$arResult['SHOW_SITEMAP_BUTTON'] = true;
}*/

if ($menuUser->isAdmin())
{
	$appImport = Option::get("rest", "import_configuration_app", '');
	if ($appImport != '')
	{
		try
		{
			$appList = \Bitrix\Main\Web\Json::decode($appImport);
			$app = array_shift($appList);
			if ($app && Main\Loader::includeModule('rest'))
			{
				$arResult["SHOW_IMPORT_CONFIGURATION"] = 'Y';
				$url = \Bitrix\Rest\Marketplace\Url::getConfigurationImportAppUrl($app);
				$uri = new Bitrix\Main\Web\Uri($url);
				$uri->addParams(
					[
						'create_install' => 'Y'
					]
				);
				$arResult['URL_IMPORT_CONFIGURATION'] = $uri->getUri();
			}
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			Option::set("rest", "import_configuration_app", '');
		}
	}
}

$counters = \CUserCounter::GetValues($USER->GetID(), SITE_ID);
$counters = is_array($counters) ? $counters : [];


if (Loader::includeModule('cbit.mc.expense'))
{
    $counterId = \Cbit\Mc\Expense\Config\Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE;
    if (\Cbit\Mc\Expense\Service\Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForExpense())
    {
        $counters[$counterId] = \Cbit\Mc\Expense\Helper\Main\User::getCountOfNewRequestsAssignedByCurrentUser();
    }
    else
    {
        $counters[$counterId] = \Cbit\Mc\Expense\Helper\Main\User::getCountOfRejectedRequestsByCurrentUser();
    }
}

$workgroupCounterData = [
	'livefeed' => ($counters[\CUserCounter::LIVEFEED_CODE . 'SG0']),
];

if (Loader::includeModule('tasks'))
{
	$tasksCounterInstance = \Bitrix\Tasks\Internals\Counter::getInstance($USER->GetID());

	$workgroupCounterData[TasksCounterDictionary::COUNTER_PROJECTS_MAJOR] = (
		$tasksCounterInstance->get(TasksCounterDictionary::COUNTER_GROUPS_TOTAL_COMMENTS)
		+ $tasksCounterInstance->get(TasksCounterDictionary::COUNTER_PROJECTS_TOTAL_COMMENTS)
		+ $tasksCounterInstance->get(TasksCounterDictionary::COUNTER_GROUPS_TOTAL_EXPIRED)
		+ $tasksCounterInstance->get(TasksCounterDictionary::COUNTER_PROJECTS_TOTAL_EXPIRED)
	);

	$workgroupCounterData[TasksCounterDictionary::COUNTER_SCRUM_TOTAL_COMMENTS] = $tasksCounterInstance->get(TasksCounterDictionary::COUNTER_SCRUM_TOTAL_COMMENTS);
}

$counters['workgroups'] = array_sum($workgroupCounterData);

$arResult["COUNTERS"] = $counters;
$arResult['WORKGROUP_COUNTER_DATA'] = $workgroupCounterData;

$arResult["GROUPS"] = array();
if (!$arResult["IS_EXTRANET"] && $GLOBALS["USER"]->isAuthorized())
{
	$arResult["GROUPS"] = include(__DIR__."/groups.php");
}

$arResult["IS_PUBLIC_CONVERTED"] = file_exists($_SERVER["DOCUMENT_ROOT"].SITE_DIR."stream/");

$shouldShowWhatsNew = function() {
	if (Loader::includeModule('extranet') && \CExtranet::isExtranetSite())
	{
		return false;
	}

	if (\COption::getOptionString('intranet', 'new_portal_structure', 'N') === 'Y')
	{
		return false;
	}

	$option = \CUserOptions::getOption('intranet', 'left_menu_whats_new_dialog');
	if (isset($option['closed']) && $option['closed'] === 'Y')
	{
		return false;
	}

	$spotlight = new Main\UI\Spotlight('left_menu_whats_new_dialog');
	$spotlight->setUserTimeSpan(3600 * 24 * 7);
	if (ModuleManager::isModuleInstalled('bitrix24'))
	{
		$spotlight->setEndDate(gmmktime(8, 30, 0, 5, 10, 2022));
	}

	return $spotlight->isAvailable();
};

$arResult["SHOW_WHATS_NEW"] = $shouldShowWhatsNew();
