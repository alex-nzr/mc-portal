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
namespace Cbit\Mc\RI\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Internals\Control\ServiceManager;

/**
 * Class UserFieldInstaller
 * @package Cbit\Mc\RI\Internals\Installation
 */
class UserFieldInstaller
{
    /**
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function install(int $entityTypeId): Result
    {
        return UserField::setupUserFields(static::getFields($entityTypeId));
    }

    /**
     * @param int $entityTypeId
     * @return array
     * @throws \Exception
     */
    public static function getFields(int $entityTypeId): array
    {
        $userFields    = static::getUserFieldsDescription($entityTypeId);
        $typeId        = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID);
        $entityIdForUf = 'CRM_' . $typeId;
        $ufPrefix      = 'UF_CRM_' . $typeId . '_';

        return UserField::prepareUserFieldsData($userFields, $entityIdForUf, $ufPrefix);
    }

    /**
     * @param int $entityTypeId
     * @return array[]
     * @throws \Exception
     */
    protected static function getUserFieldsDescription(int $entityTypeId): array
    {
        $staffingEntityTypeId = Configuration::getInstance()->getStaffingEntityTypeId();
        return [
            [
                'TITLE_RU'     => 'Charge code',
                'TITLE_EN'     => "Charge code",
                'FIELD_NAME'   => 'CHARGE_CODE',
                'USER_TYPE_ID' => 'crm',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'LEAD'    => 'N',
                    'CONTACT' => 'N',
                    'COMPANY' => 'N',
                    'DEAL'    => 'N',
                    'ORDER'   => 'N',
                    "DYNAMIC_$staffingEntityTypeId" => 'Y'
                ]
            ],
            [
                'TITLE_RU'     => 'Deadline',
                'TITLE_EN'     => 'Deadline',
                'FIELD_NAME'   => 'DEADLINE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Last status change',
                'TITLE_EN'     => 'Last status change',
                'FIELD_NAME'   => 'LAST_STATUS_CHANGE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => [
                        'TYPE' => 'NOW',
                        'VALUE' => new DateTime()
                    ]
                ]
            ],
            [
                'TITLE_RU'     => 'Cancel comment',
                'TITLE_EN'     => 'Cancel comment',
                'FIELD_NAME'   => 'CANCEL_COMMENT',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                    'SIZE'    => 20,
                    'ROWS'    => 3,
                ]
            ],
            [
                'TITLE_RU'     => 'Cancel reason',
                'TITLE_EN'     => 'Cancel reason',
                'FIELD_NAME'   => 'CANCEL_REASON',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ],
                'LIST'         => [
                    'The information was found independently',
                    'The request was made incorrectly',
                    'The request is no longer relevant',
                    'Long work on the request',
                    'Other (specify in the comment)',
                ]
            ],
            [
                'TITLE_RU'     => 'Type of request',
                'TITLE_EN'     => 'Type of request',
                'FIELD_NAME'   => 'TYPE_OF_REQUEST',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ],
                'LIST'         => [
                    'news search',
                    'market analysis',
                    'uploading data',
                    'search for data by template',
                    'search for an external expert',
                    'other'
                ]
            ],
            [
                'TITLE_RU'     => 'Industry',
                'TITLE_EN'     => 'Industry',
                'FIELD_NAME'   => 'INDUSTRY',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DISPLAY' => 'CHECKBOX',
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
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DISPLAY' => 'CHECKBOX',
                    'LIST_HEIGHT' => 4,
                    'IBLOCK_ID' => CoreConfig::getInstance()->getFunctionsIBlockId(),
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            ],
            [
                'TITLE_RU'     => 'Geography',
                'TITLE_EN'     => 'Geography',
                'FIELD_NAME'   => 'LOCATION',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
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
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                    'SIZE'    => 20,
                    'ROWS'    => 3,
                ]
            ],
            [
                'TITLE_RU'     => 'Attachments',
                'TITLE_EN'     => 'Attachments',
                'FIELD_NAME'   => 'ATTACHMENTS',
                'USER_TYPE_ID' => 'file',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'TARGET_BLANK'     => 'Y',
                    'MAX_ALLOWED_SIZE' => 10485760,
                    'MAX_SHOW_SIZE'    => 10485760,
                    'EXTENSIONS'       => 'jpg,xlsx,xls,doc,docx,pdf,tiff,jpeg,png,webp,ppt,pptx'
                ]
            ],
            [
                'TITLE_RU'     => 'Comment',
                'TITLE_EN'     => 'Comment',
                'FIELD_NAME'   => 'COMMENT',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                    'SIZE'    => 20,
                    'ROWS'    => 3,
                ]
            ],
            [
                'TITLE_RU'     => 'Related requests',
                'TITLE_EN'     => "Related requests",
                'FIELD_NAME'   => 'RELATED_REQUESTS',
                'USER_TYPE_ID' => 'crm',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => '',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'LEAD'    => 'N',
                    'CONTACT' => 'N',
                    'COMPANY' => 'N',
                    'DEAL'    => 'N',
                    'ORDER'   => 'N',
                    "DYNAMIC_$entityTypeId" => 'Y'
                ]
            ],
            [
                'TITLE_RU'     => 'Request execution speed',
                'TITLE_EN'     => 'Request execution speed',
                'FIELD_NAME'   => 'SCORE_SPEED',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'MIN_VALUE'    => 0,
                    'MAX_VALUE'    => 5,
                    'DEFAULT_VALUE' => null,
                ]
            ],
            [
                'TITLE_RU'     => 'Work quality',
                'TITLE_EN'     => 'Work quality',
                'FIELD_NAME'   => 'SCORE_WORK',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'MIN_VALUE'    => 0,
                    'MAX_VALUE'    => 5,
                    'DEFAULT_VALUE' => null,
                ]
            ],
            [
                'TITLE_RU'     => 'Quality of communication with the R&I department',
                'TITLE_EN'     => 'Quality of communication with the R&I department',
                'FIELD_NAME'   => 'SCORE_COMMUNICATION',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'MIN_VALUE'    => 0,
                    'MAX_VALUE'    => 5,
                    'DEFAULT_VALUE' => null,
                ]
            ],
            [
                'TITLE_RU'     => 'Comment about scoring',
                'TITLE_EN'     => 'Comment about scoring',
                'FIELD_NAME'   => 'SCORE_COMMENT',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                    'SIZE'    => 20,
                    'ROWS'    => 3,
                ]
            ],
            [
                'TITLE_RU'     => 'Assigned to',
                'TITLE_EN'     => 'Assigned to',
                'FIELD_NAME'   => 'ASSIGNED_BY',
                'USER_TYPE_ID' => 'cbit.ri-user',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Max budget(euro)',
                'TITLE_EN'     => 'Max budget(euro)',
                'FIELD_NAME'   => 'MAX_BUDGET',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'MIN_VALUE' => 1,
                    'MAX_VALUE' => 9999999,
                ]
            ],
            [
                'TITLE_RU'     => 'Per diem',
                'TITLE_EN'     => 'Per diem',
                'FIELD_NAME'   => 'PER_DIEM',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'Labor costs(plan)',
                'TITLE_EN'     => 'Labor costs(plan)',
                'FIELD_NAME'   => 'LABOR_COSTS_PLAN',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Labor costs(fact)',
                'TITLE_EN'     => 'Labor costs(fact)',
                'FIELD_NAME'   => 'LABOR_COSTS_FACT',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Unassigned noted',
                'TITLE_EN'     => 'Unassigned noted',
                'FIELD_NAME'   => 'UNASSIGNED_NOTED',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => false
                ]
            ],
            [
                'TITLE_RU'     => 'Moved to assigned stage',
                'TITLE_EN'     => 'Moved to assigned stage',
                'FIELD_NAME'   => 'MOVED_TO_ASSIGNED_STAGE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Result description',
                'TITLE_EN'     => 'Result description',
                'FIELD_NAME'   => 'RESULT_DESCRIPTION',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'ROWS' => 4,
                ]
            ],
            [
                'TITLE_RU'     => 'Result of request',
                'TITLE_EN'     => 'Result of request',
                'FIELD_NAME'   => 'RESULT_ATTACHMENTS',
                'USER_TYPE_ID' => 'file',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'TARGET_BLANK'     => 'Y',
                    'MAX_ALLOWED_SIZE' => 10485760,
                    'MAX_SHOW_SIZE'    => 10485760,
                    'EXTENSIONS'       => 'jpg,xlsx,xls,doc,docx,pdf,tiff,jpeg,png,webp,ppt,pptx'
                ]
            ],
        ];
    }
}