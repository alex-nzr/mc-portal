<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = [
    "NAME" => Loc::getMessage("CBIT_MC_PROFILE_APPROVE_LIST_COMPONENT_NAME"),
    "DESCRIPTION" => Loc::getMessage("CBIT_MC_PROFILE_APPROVE_LIST_COMPONENT_DESC"),
    "PATH" => [
        "ID" => "cbit_mc",
        "NAME" => Loc::getMessage("CBIT_MC_PROFILE_APPROVE_LIST_COMPONENT_VENDOR_NAME"),
        "CHILD" => [
            "ID" => "mc.profile.photo-approver.LIST",
            "NAME" => Loc::getMessage("CBIT_MC_PROFILE_APPROVE_LIST_COMPONENT_CATEGORY_NAME")
        ]
    ],
    "CACHE_PATH" => "Y",
];