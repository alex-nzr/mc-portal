<?php
/** @var \CMain $APPLICATION */
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Partner\Config\OptionManager;
use Cbit\Mc\Partner\Internals\Control\ServiceManager;

Loc::loadMessages(__FILE__);

$module_id = ServiceManager::getModuleId();

try
{
    if ($APPLICATION->GetGroupRight($module_id) < "W")
    {
        $APPLICATION->AuthForm(Loc::getMessage($module_id."_ACCESS_DENIED"));
    }

    if(!Loader::includeModule($module_id)){
        throw new Exception(Loc::getMessage($module_id."_MODULE_NOT_LOADED"));
    }

    $optionManager = new OptionManager($module_id);
    $optionManager->processRequest();
    $optionManager->startDrawHtml();

    $optionManager->tabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");

    $optionManager->endDrawHtml();
}
catch(Exception $e)
{
    ShowError($e->getMessage());
}