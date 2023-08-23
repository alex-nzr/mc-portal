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
namespace Cbit\Mc\Partner\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Partner\Config\Constants;
use Cbit\Mc\Partner\Internals\Control\ServiceManager;

/**
 * @class UserFieldInstaller
 * @package Cbit\Mc\Partner\Internals\Installation
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
                'FIELD_NAME'   => 'PARTNER_SITE',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="#VALUE#">#VALUE#</a>',
                ]
            ],
            [
                'TITLE_RU'     => 'Type',
                'TITLE_EN'     => 'Type',
                'FIELD_NAME'   => 'PARTNER_TYPE',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => '',
                    'DISPLAY'       => 'DIALOG'
                ],
                'LIST'         => [
                    'EXPERT_PLATFORM' => 'экспертная платформа',
                    'RESEARCH_AGENCY' => 'ресерч-агентство',
                    'CONSULTING'      => 'консалтинг',
                ]
            ],
            [
                'TITLE_RU'     => 'Description',
                'TITLE_EN'     => 'Description',
                'FIELD_NAME'   => 'PARTNER_DESC',
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
                'FIELD_NAME'   => 'PARTNER_INDUSTRY',
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
                'TITLE_RU'     => 'Country location',
                'TITLE_EN'     => 'Country location',
                'FIELD_NAME'   => 'PARTNER_COUNTRY',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'Region, country of expertise',
                'TITLE_EN'     => 'Region, country of expertise',
                'FIELD_NAME'   => 'PARTNER_EXPERTISE_LOCATION',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'The scheme of interaction with the contractor',
                'TITLE_EN'     => 'The scheme of interaction with the contractor',
                'FIELD_NAME'   => 'PARTNER_INTERACTION_SCHEME',
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
                    'SCHEME_3' => 'Контакт через РФ подрядчиков',
                    'SCHEME_4' => 'Контакт через иностранных подрядчиков',
                ]
            ],
            [
                'TITLE_RU'     => 'FIO',
                'TITLE_EN'     => 'FIO',
                'FIELD_NAME'   => 'PARTNER_CONTACT_FIO',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
            [
                'TITLE_RU'     => 'Email',
                'TITLE_EN'     => 'Email',
                'FIELD_NAME'   => 'PARTNER_CONTACT_EMAIL',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="mailto:#VALUE#">#VALUE#</a>',
                ]
            ],
            [
                'TITLE_RU'     => 'Phone',
                'TITLE_EN'     => 'Phone',
                'FIELD_NAME'   => 'PARTNER_CONTACT_PHONE',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'Y',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#',
                ]
            ],
        ];
    }
}