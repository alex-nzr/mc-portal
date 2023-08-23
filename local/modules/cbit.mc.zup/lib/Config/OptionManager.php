<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - OptionManager.php
 * 21.11.2022 11:46
 * ==================================================
 */
namespace Cbit\Mc\Zup\Config;

use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Core\Config\BaseOptionManager;

Loc::loadMessages(__FILE__);

/**
 * Class OptionManager
 * @package Cbit\Mc\Zup\Config
 */
class OptionManager extends BaseOptionManager
{
    /**
     * @return void
     */
    protected function setTabs(): void
    {
        $this->tabs = [
            [
                'DIV'   => "settings_tab",
                'TAB'   => Loc::getMessage("CBIT_MC_CORE_MODULE_SETTINGS"),
                'ICON'  => '',
                'TITLE' => Loc::getMessage("CBIT_MC_CORE_MODULE_SETTINGS"),
                "OPTIONS" => [
                    Loc::getMessage($this->moduleId."_MAIN_SETTINGS"),
                    [
                        Constants::OPTION_KEY_API_URL,
                        Loc::getMessage($this->moduleId."_OPTION_API_ADDRESS"),
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_API_LOGIN,
                        Loc::getMessage($this->moduleId."_OPTION_API_LOGIN"),
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_API_PASSWORD,
                        Loc::getMessage($this->moduleId."_OPTION_API_PASSWORD"),
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_API_CLIENT_ID,
                        Loc::getMessage($this->moduleId."_OPTION_API_CLIENT_ID"),
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_API_CLIENT_SECRET,
                        Loc::getMessage($this->moduleId."_OPTION_API_CLIENT_SECRET"),
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_API_API_KEY,
                        Loc::getMessage($this->moduleId."_OPTION_API_API_KEY"),
                        "",
                        ['text', 50]
                    ],

                    Loc::getMessage($this->moduleId."_ZUP_FIELDS_SETTINGS"),
                    [
                        Constants::OPTION_KEY_SYNC_FMNO_XML_ID,
                        Loc::getMessage($this->moduleId."_OPTION_FMNO_XML_ID"),
                        "",
                        ['text', 50]
                    ],

                    Loc::getMessage($this->moduleId."_SYNC_SETTINGS"),
                    [
                        Constants::OPTION_KEY_SYNC_LAST_GET_EDU_TYPES,
                        Loc::getMessage($this->moduleId."_OPTION_LAST_GET_EDU_TYPES"),
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_SYNC_LAST_GET_EMP_EDU,
                        Loc::getMessage($this->moduleId."_OPTION_LAST_GET_EMP_EDU"),
                        "",
                        ['text', 50]
                    ],
                ]
            ],
            [
                'DIV'   => "access_tab",
                'TAB'   => Loc::getMessage("CBIT_MC_CORE_TAB_RIGHTS"),
                'ICON'  => '',
                'TITLE' => Loc::getMessage("CBIT_MC_CORE_TAB_TITLE_RIGHTS"),
            ]
        ];
    }
}