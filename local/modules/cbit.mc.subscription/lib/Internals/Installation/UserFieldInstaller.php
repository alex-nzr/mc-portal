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
namespace Cbit\Mc\Subscription\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Subscription\Config\Constants;
use Cbit\Mc\Subscription\Internals\Control\ServiceManager;

/**
 * @class UserFieldInstaller
 * @package Cbit\Mc\Subscription\Internals\Installation
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
                'TITLE_RU'     => 'Website',
                'TITLE_EN'     => 'Website',
                'FIELD_NAME'   => 'SUBSCRIPTION_SITE',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="#VALUE#">#VALUE#</a>',
                ]
            ],
            [
                'TITLE_RU'     => 'Description',
                'TITLE_EN'     => 'Description',
                'FIELD_NAME'   => 'SUBSCRIPTION_DESC',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                    'ROWS'    => 3
                ]
            ],
            [
                'TITLE_RU'     => 'Industry',
                'TITLE_EN'     => 'Industry',
                'FIELD_NAME'   => 'SUBSCRIPTION_INDUSTRY',
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
                'TITLE_RU'     => 'Subscription end date',
                'TITLE_EN'     => 'Subscription end date',
                'FIELD_NAME'   => 'END_DATE',
                'USER_TYPE_ID' => 'date',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => ''
                ]
            ],
            [
                'TITLE_RU'     => 'Russian Federation data',
                'TITLE_EN'     => 'Russian Federation data',
                'FIELD_NAME'   => 'IS_RF_DATA',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'World data',
                'TITLE_EN'     => 'World data',
                'FIELD_NAME'   => 'IS_WORLD_DATA',
                'USER_TYPE_ID' => 'boolean',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Criticality of the resource',
                'TITLE_EN'     => 'Criticality of the resource',
                'FIELD_NAME'   => 'RESOURCE_CRITICALLY',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => '',
                    'DISPLAY'       => 'DIALOG'
                ],
                'LIST'         => [
                    'MUST'     => 'MUST',
                    'OPTIONAL' => 'Optional',
                ]
            ],
            [
                'TITLE_RU'     => 'Login / Password',
                'TITLE_EN'     => 'Login / Password',
                'FIELD_NAME'   => 'LOGIN_PASSWORD',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'Subscription price',
                'TITLE_EN'     => "Subscription price",
                'FIELD_NAME'   => 'SUBSCRIPTION_PRICE',
                'USER_TYPE_ID' => 'money',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => '0|EUR'
                ]
            ],
            [
                'TITLE_RU'     => 'Subscription period (months)',
                'TITLE_EN'     => 'Subscription period (months)',
                'FIELD_NAME'   => 'SUBSCRIPTION_PERIOD',
                'USER_TYPE_ID' => 'double',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PRECISION' => '2',
                    'MIN_VALUE' => 0,
                ]
            ],
            [
                'TITLE_RU'     => 'The scheme of interaction with the contractor',
                'TITLE_EN'     => 'The scheme of interaction with the contractor',
                'FIELD_NAME'   => 'SUBSCRIPTION_INTERACTION_SCHEME',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => '',
                    'DISPLAY'       => 'DIALOG'
                ],
                'LIST'         => [
                    'SCHEME_1' => 'Прямой контакт - юр.лицо РФ',
                    'SCHEME_2' => 'Прямой контакт - юр.лицо иностранное',
                    'SCHEME_3' => 'Контакт через иностранных подрядчиков',
                ]
            ],
            [
                'TITLE_RU'     => 'FIO',
                'TITLE_EN'     => 'FIO',
                'FIELD_NAME'   => 'CONTACT_FIO',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'Email',
                'TITLE_EN'     => 'Email',
                'FIELD_NAME'   => 'CONTACT_EMAIL',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="mailto:#VALUE#">#VALUE#</a>',
                ]
            ],
            [
                'TITLE_RU'     => 'Phone',
                'TITLE_EN'     => 'Phone',
                'FIELD_NAME'   => 'CONTACT_PHONE',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'Subscription version',
                'TITLE_EN'     => 'Subscription version',
                'FIELD_NAME'   => 'VERSION',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
        ];
    }
}