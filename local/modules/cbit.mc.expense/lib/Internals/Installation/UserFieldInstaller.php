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
namespace Cbit\Mc\Expense\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Internals\UserField\Type\ChargeCode;
use Cbit\Mc\Expense\Internals\UserField\Type\ExternalParticipant;

/**
 * @class UserFieldInstaller
 * @package Cbit\Mc\Expense\Internals\Installation
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
        return [
            [
                'TITLE_RU'     => 'Category',
                'TITLE_EN'     => 'Category',
                'FIELD_NAME'   => 'CATEGORY_OF_RECEIPT',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => '',
                    'DISPLAY'       => 'DIALOG'
                ],
                'LIST'         => [
                    Constants::MEAL_TEAM_EVENT_RECEIPT,
                    'Meal: Client Meal',
                    'Meal: Individual Dinner',
                    'Meal: Other',
                    'Meal: Buddy Meals',
                    'Meal: Mentorship',
                    Constants::IT_EQUIPMENT_RECEIPT,
                    Constants::IT_SOFT_RECEIPT,
                    Constants::IT_LICENSES_RECEIPT,
                    Constants::IT_HARDWARE_RECEIPT,
                    Constants::IT_CELLULAR_RECEIPT,
                    Constants::TRAVEL_LAUNDRY_GYM_RECEIPT,
                    Constants::TRAVEL_WEEKEND_RECEIPT,
                    Constants::TRAVEL_SO_TICKET_RECEIPT,
                    Constants::TRAVEL_TOLL_ROAD_RECEIPT,
                    Constants::TRAVEL_TAXI_RECEIPT,
                    Constants::TRAVEL_PARKING_RECEIPT,
                    'Gifts External',
                    'Gifts Internal',
                    'Other: Information consulting',
                    'Other: Medical Examinations / Supplies',
                    'Other: Subscriptions / Membership fees',
                    'Other: Bank / Currency Fees',
                    'Office services: Books',
                    'Office services: Event entertainment',
                    'Office services: Administrative support services',
                ]
            ],
            [
                'TITLE_RU'     => 'Category',
                'TITLE_EN'     => 'Category',
                'FIELD_NAME'   => 'CATEGORY_OF_TR_YOUR_BUDGET',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => '',
                    'DISPLAY'       => 'DIALOG'
                ],
                'LIST'         => [
                    'Массаж',
                    'Психолог',
                    'Прочее'
                ]
            ],
            [
                'TITLE_RU'     => 'Charge code',
                'TITLE_EN'     => "Charge code",
                'FIELD_NAME'   => 'CHARGE_CODE',
                'USER_TYPE_ID' => ChargeCode::USER_TYPE_ID,
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Duplicate of',
                'TITLE_EN'     => "Duplicate of",
                'FIELD_NAME'   => 'DUPLICATE_OF',
                'USER_TYPE_ID' => 'crm',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
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
                'TITLE_RU'     => 'PSSS',
                'TITLE_EN'     => 'PSSS',
                'FIELD_NAME'   => 'PSSS',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Participants (Internal)',
                'TITLE_EN'     => 'Participants (Internal)',
                'FIELD_NAME'   => 'PARTICIPANTS_INTERNAL',
                'USER_TYPE_ID' => 'employee',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Participants (External)',
                'TITLE_EN'     => 'Participants (External)',
                'FIELD_NAME'   => 'PARTICIPANTS_EXTERNAL',
                'USER_TYPE_ID' => ExternalParticipant::USER_TYPE_ID,
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Participants total',
                'TITLE_EN'     => 'Participants total',
                'FIELD_NAME'   => 'PARTICIPANTS_TOTAL',
                'USER_TYPE_ID' => 'double',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PRECISION' => '0'
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
                'TITLE_RU'     => 'Attachments',
                'TITLE_EN'     => 'Attachments',
                'FIELD_NAME'   => 'ATTACHMENTS',
                'USER_TYPE_ID' => 'cbit-file',
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
                'TITLE_RU'     => 'Approval date',
                'TITLE_EN'     => 'Approval date',
                'FIELD_NAME'   => 'APPROVAL_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Initial amount',
                'TITLE_EN'     => "Initial amount",
                'FIELD_NAME'   => 'INITIAL_AMOUNT',
                'USER_TYPE_ID' => 'money',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Amount rejected',
                'TITLE_EN'     => "Amount rejected",
                'FIELD_NAME'   => 'AMOUNT_REJECTED',
                'USER_TYPE_ID' => 'money',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Split reason',
                'TITLE_EN'     => 'Split reason',
                'FIELD_NAME'   => 'REASON',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Expense date',
                'TITLE_EN'     => 'Expense date',
                'FIELD_NAME'   => 'EXPENSE_DATE',
                'USER_TYPE_ID' => 'date',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Trip date',
                'TITLE_EN'     => 'Trip date',
                'FIELD_NAME'   => 'TRIP_DATE',
                'USER_TYPE_ID' => 'date',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Departure date',
                'TITLE_EN'     => 'Departure date',
                'FIELD_NAME'   => 'DEPARTURE_DATE',
                'USER_TYPE_ID' => 'date',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Arrival date',
                'TITLE_EN'     => 'Arrival date',
                'FIELD_NAME'   => 'ARRIVAL_DATE',
                'USER_TYPE_ID' => 'date',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'City',
                'TITLE_EN'     => 'City',
                'FIELD_NAME'   => 'CITY',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Reject comment added ',
                'TITLE_EN'     => 'Reject comment added ',
                'FIELD_NAME'   => 'REJECT_COMMENT_ADDED',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Requester fmno',
                'TITLE_EN'     => 'Requester fmno',
                'FIELD_NAME'   => 'REQUESTER_FMNO',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
        ];
    }
}