<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (SITE_TEMPLATE_ID !== "bitrix24")
{
	return;
}

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;

global $APPLICATION;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/.top.menu_ext.php");

if (!function_exists("getLeftMenuItemLink"))
{
	function getLeftMenuItemLink($sectionId, $defaultLink = "")
	{
		$settings = CUserOptions::GetOption("UI", $sectionId);
		return
			is_array($settings) && isset($settings["firstPageLink"]) && mb_strlen($settings["firstPageLink"]) ?
				$settings["firstPageLink"] :
				$defaultLink;
	}
}

if (!function_exists("getItemLinkId"))
{
	function getItemLinkId($link): string
    {
		$menuId = str_replace("/", "_", trim($link, "/"));
		return "top_menu_id_".$menuId;
	}
}

$userId = $GLOBALS["USER"]->GetID();

if (defined("BX_COMP_MANAGED_CACHE"))
{
	global $CACHE_MANAGER;
	$CACHE_MANAGER->registerTag("bitrix24_left_menu");
	$CACHE_MANAGER->registerTag("crm_change_role");
	$CACHE_MANAGER->registerTag("USER_NAME_".$userId);
}

global $USER;

$arMenuB24 = [
    [
        'My profile',
        SITE_DIR . "profile/",
        [SITE_DIR . "profile/"],
        [
            "real_link" => getLeftMenuItemLink(
                "top_menu_id_profile",
                SITE_DIR . "profile/"
            ),
            "menu_item_id" => "menu_profile_sect",
            "top_menu_id" => "top_menu_id_profile",
        ],
        '',
    ],
];

//merge with static items from top.menu
/*foreach ($aMenuLinks as $arItem)
{
	$menuLink = $arItem[1];

	$menuId = getItemLinkId($menuLink);
	$arItem[3]["real_link"] = getLeftMenuItemLink($menuId, $menuLink);
	$arItem[3]["top_menu_id"] = $menuId;
	$arMenuB24[] = $arItem;
}*/

$arMenuB24[] = [
	GetMessage("TOP_MENU_DEVOPS"),
	SITE_DIR . "devops/",
	[SITE_DIR . "devops/"],
	[
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_devops",
			SITE_DIR . "devops/"
		),
		"menu_item_id" => "menu_devops_sect",
		"top_menu_id" => "top_menu_id_devops",
	],
	'IsModuleInstalled("rest") && $USER->IsAdmin()',
];

$arMenuB24[] = Array(
	GetMessage("TOP_MENU_CONFIGS"),
	SITE_DIR."configs/",
	Array(SITE_DIR."configs/"),
	Array(
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_configs",
			SITE_DIR."configs/"
		),
		"menu_item_id" => "menu_configs_sect",
		"top_menu_id" => "top_menu_id_configs"
	),
	'$USER->IsAdmin()'
);

try
{
    $manager = ServiceLocator::getInstance()->get('intranet.customSection.manager');
    $manager->appendSuperLeftMenuSections($arMenuB24);


    if (Loader::includeModule('cbit.mc.profile'))
    {
        $arMenuB24[] = [
            "Photo approval",
            SITE_DIR."profile/approve/photo/list.php",
            [],
            [],
            "\Cbit\Mc\Profile\Service\Approval\PersonalPhoto::getInstance()->canCurrentUserApprovePhoto()"
        ];
    }
}
catch(Exception $e)
{
    //log error
}

$aMenuLinks = $arMenuB24;
