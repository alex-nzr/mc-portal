<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use CBit\Mc\Profile\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();
Loc::loadMessages(__FILE__);

$arComponentDescription = [
    "NAME" => Loc::getMessage($moduleId.'_BINDER_COMPONENT_NAME'),
    "DESCRIPTION" => Loc::getMessage($moduleId.'_BINDER_COMPONENT_DESC'),
    "PATH" => [
        "ID" => "cbit_mc",
        "NAME" => Loc::getMessage($moduleId.'_BINDER_COMPONENT_VENDOR_NAME'),
        "CHILD" => [
            "ID" => "mc.staffing.binder",
            "NAME" => Loc::getMessage($moduleId.'_BINDER_COMPONENT_CATEGORY_NAME')
        ]
    ],
    "CACHE_PATH" => "Y",
];