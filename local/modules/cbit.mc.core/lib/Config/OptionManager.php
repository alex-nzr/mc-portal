<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - OptionManager.php
 * 25.11.2022 11:46
 * ==================================================
 */
namespace Cbit\Mc\Core\Config;

use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);
/**
 * Class OptionManager
 * @package Cbit\Mc\Core\Config
 */
class OptionManager extends BaseOptionManager {

    /**
     * @return void
     * @throws \Exception
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
                    Loc::getMessage($this->moduleId.'_ROLES_SETTINGS'),
                    [
                        Constants::OPTION_KEY_PD_STAFFING_ROLE,
                        'Staffing system (coordinator access to staff people on projects)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_EA_LEADERS_ROLE,
                        'My profile (executive assistant access to apply themselves as EA on other profiles)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_VG_LEADERS_ROLE,
                        'My profile (profile avatar review process role)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_HR_TEAM_ROLE,
                        "My profile (edit profile's contacts)",
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_RI_ANALYSTS_ROLE,
                        'R&I system (tickets processing)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_RI_MANAGERS_ROLE,
                        'R&I system (tickets processing + admin role)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_EXPENSES_IT_ROLE,
                        'Expenses Category IT (IT expenses processing)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_EXPENSES_TRAVEL_ROLE,
                        'Expenses Category Travel (travel expenses processing)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_EXPENSES_FINANCE_ROLE,
                        'Expenses FO (receipt processing)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],
                    [
                        Constants::OPTION_KEY_EXPENSES_PAYROLL_ROLE,
                        'Expenses Payroll (treat yourself expenses processing)',
                        "[1]",
                        ['role', Configuration::getInstance()->getUserGroupsToOption()]
                    ],

                    Loc::getMessage($this->moduleId."_ZUP_SETTINGS"),
                    [
                        Constants::OPTION_KEY_TENURE_COMPANY_UF_CODE,
                        'TENURE_COMPANY userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_TENURE_POSITION_UF_CODE,
                        'TENURE_POSITION userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_ABSENCE_UF_CODE,
                        'ABSENCE userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_ZUP_STATUS_UF_CODE,
                        'ZUP_STATUS userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_FIO_EN_UF_CODE,
                        'FIO_EN userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_FMNO_UF_CODE,
                        'FMNO userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_BASE_PER_DIEM_UF_CODE,
                        'BASE_PER_DIEM userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_POSITION_EN_UF_CODE,
                        'POSITION_EN userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_TRACK_UF_CODE,
                        'TRACK userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_CSP_UF_CODE,
                        'CSP/OSP userField code',
                        "",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_RATING_UF_CODE,
                        'Rating userField code',
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