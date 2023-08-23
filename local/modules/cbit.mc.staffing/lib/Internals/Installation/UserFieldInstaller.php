<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - UserFieldInstaller.php
 * 17.01.2023 18:18
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;

/**
 * Class UserFieldInstaller
 * @package Cbit\Mc\Staffing\Internals\Installation
 */
class UserFieldInstaller
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function install(): Result
    {
        return UserField::setupUserFields(static::getFields());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getFields(): array
    {
        $userFields    = static::getUserFieldsDescription();
        $typeId        = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID);
        $entityIdForUf = 'CRM_' . $typeId;
        $ufPrefix      = 'UF_CRM_' . $typeId . '_';

        return UserField::prepareUserFieldsData($userFields, $entityIdForUf, $ufPrefix);
    }

    /**
     * @return array[]
     * @throws \Exception
     */
    protected static function getUserFieldsDescription(): array
    {
        return [
            [
                'TITLE_RU'     => 'СС',
                'TITLE_EN'     => 'СС',
                'FIELD_NAME'   => 'CHARGE_CODE',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Description',
                'TITLE_EN'     => 'Description',
                'FIELD_NAME'   => 'DESCRIPTION',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Activity',
                'TITLE_EN'     => 'Activity',
                'FIELD_NAME'   => 'ACTIVITY',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getActivitiesIBlockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'Phase',
                'TITLE_EN'     => 'Phase',
                'FIELD_NAME'   => 'PHASE',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getProjectPhasesIBlockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'State',
                'TITLE_EN'     => 'State',
                'FIELD_NAME'   => 'STATE',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getProjectStatesIBlockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'ED',
                'TITLE_EN'     => 'ED',
                'FIELD_NAME'   => 'ED',
                'USER_TYPE_ID' => 'cbit.employee',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Industry',
                'TITLE_EN'     => 'Industry',
                'FIELD_NAME'   => 'INDUSTRY',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getIndustriesIblockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'Function',
                'TITLE_EN'     => 'Function',
                'FIELD_NAME'   => 'FUNCTION',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getFunctionsIBlockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'Start date',
                'TITLE_EN'     => 'Start date',
                'FIELD_NAME'   => 'START_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'End date',
                'TITLE_EN'     => 'End date',
                'FIELD_NAME'   => 'END_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Duration',
                'TITLE_EN'     => 'Duration',
                'FIELD_NAME'   => 'DURATION',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE# weeks'
                ]
            ],
            [
                'TITLE_RU'     => 'Location',
                'TITLE_EN'     => 'Location',
                'FIELD_NAME'   => 'LOCATION',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Master client',
                'TITLE_EN'     => 'Master client',
                'FIELD_NAME'   => 'MASTER_CLIENT',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Client',
                'TITLE_EN'     => 'Client',
                'FIELD_NAME'   => 'CLIENT',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Team composition',
                'TITLE_EN'     => 'Team composition',
                'FIELD_NAME'   => 'TEAM_COMPOSITION',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY' => 'LIST',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getTeamCompositionsIBlockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'DiscussionDate',
                'TITLE_EN'     => 'DiscussionDate',
                'FIELD_NAME'   => 'DISCUSSION_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'DevelopmentDate',
                'TITLE_EN'     => 'DevelopmentDate',
                'FIELD_NAME'   => 'DEVELOPMENT_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'ConfirmedDate',
                'TITLE_EN'     => 'ConfirmedDate',
                'FIELD_NAME'   => 'CONFIRMED_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'FinishedOrOutDate',
                'TITLE_EN'     => 'FinishedOrOutDate',
                'FIELD_NAME'   => 'FINISH_OR_OUT_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Allow staffing',
                'TITLE_EN'     => 'Allow staffing',
                'FIELD_NAME'   => 'ALLOW_STAFFING',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => true
                ]
            ],
            [
                'TITLE_RU'     => 'Allow expense',
                'TITLE_EN'     => 'Allow expense',
                'FIELD_NAME'   => 'ALLOW_EXPENSE',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => true
                ]
            ],
            [
                'TITLE_RU'     => 'PSSS',
                'TITLE_EN'     => 'PSSS',
                'FIELD_NAME'   => 'PSSS',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Employment type',
                'TITLE_EN'     => "Employment type",
                'FIELD_NAME'   => 'EMPLOYMENT_TYPE',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY'          => 'UI',
                    'LIST_HEIGHT'      => 5,
                    'SHOW_NO_VALUE'    => 'Y',
                ],
                'LIST' => Configuration::getInstance()->getStaffingEmploymentTypes()
            ],
        ];
    }
}