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
namespace Cbit\Mc\Expense\Config;

use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Core\Config\BaseOptionManager;

/**
 * Class OptionManager
 * @package Cbit\Mc\Expense\Config
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
                    Loc::getMessage("CBIT_MC_CORE_MAIN_SETTINGS"),
                    [
                        Constants::OPTION_KEY_DEFAULT_TYB_CC,
                        'Default charge code for TYB',
                        Constants::OPTION_TYB_CC_DEFAULT_VALUE,
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