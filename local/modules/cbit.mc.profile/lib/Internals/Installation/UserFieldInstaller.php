<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - UserFieldInstaller.php
 * 10.11.2022 21:18
 * ==================================================
 */


namespace Cbit\Mc\Profile\Internals\Installation;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Config\Constants as CoreConstants;
use Cbit\Mc\Core\Helper\Main\UserField;
use Exception;

/**
 * Class UserFieldInstaller
 * @package Cbit\Mc\Profile\Internals\Installation
 */
class UserFieldInstaller
{
    /**
     * @return Result
     * @throws Exception
     */
    public static function install(): Result
    {
        return UserField::setupUserFields(static::getFields());
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getFields(): array
    {
        $userFields    = static::getUserFieldsDescription();
        $entityIdForUf = 'USER';
        $ufPrefix      = 'UF_';
        return UserField::prepareUserFieldsData($userFields, $entityIdForUf, $ufPrefix);
    }

    /**
     * @return array[]
     * @throws Exception
     */
    protected static function getUserFieldsDescription(): array
    {
        return [
            [
                'TITLE_RU'     => 'Work format',
                'TITLE_EN'     => "Work format",
                'FIELD_NAME'   => 'WORK_FORMAT',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DISPLAY'          => 'UI',
                    'LIST_HEIGHT'      => 5,
                    //'CAPTION_NO_VALUE' => 'Not selected',
                    'SHOW_NO_VALUE'    => 'Y',
                ],
                'LIST'         => [
                    'Home',
                    'Office',
                    'Client',
                ]
            ],
            [
                'TITLE_RU'     => 'Availability',
                'TITLE_EN'     => "Availability",
                'FIELD_NAME'   => 'USER_AVAILABLE',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DISPLAY'          => 'UI',
                    'LIST_HEIGHT'      => 5,
                    //'CAPTION_NO_VALUE' => 'Not selected',
                    'SHOW_NO_VALUE'    => 'Y',
                ],
                'LIST'         => [
                    CoreConstants::USER_AVAILABILITY_STATUS_FREE,
                    CoreConstants::USER_AVAILABILITY_STATUS_LEARNING,
                    CoreConstants::USER_AVAILABILITY_STATUS_STAFFED,
                    CoreConstants::USER_AVAILABILITY_STATUS_BEACH,
                    CoreConstants::USER_AVAILABILITY_STATUS_LOA,
                ]
            ],
            [
                'TITLE_RU'     => 'Staffing CV',
                'TITLE_EN'     => 'Staffing CV',
                'FIELD_NAME'   => 'STAFFING_CV',
                'USER_TYPE_ID' => 'file',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'TARGET_BLANK'     => 'Y',
                    'MAX_ALLOWED_SIZE' => 10485760,
                    'MAX_SHOW_SIZE'    => 10485760,
                    'EXTENSIONS'       => 'jpg,xlsx,xls,doc,docx,pdf,tiff,jpeg,png,webp,ppt,pptx'
                ]
            ],
            [
                'TITLE_RU'     => 'Recruitment CV',
                'TITLE_EN'     => 'Recruitment CV',
                'FIELD_NAME'   => 'RECRUITMENT_CV',
                'USER_TYPE_ID' => 'file',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'TARGET_BLANK'     => 'Y',
                    'MAX_ALLOWED_SIZE' => 10485760,
                    'MAX_SHOW_SIZE'    => 10485760,
                    'EXTENSIONS'       => 'jpg,xlsx,xls,doc,docx,pdf,tiff,jpeg,png,webp,ppt,pptx'
                ]
            ],
            [
                'TITLE_RU'     => 'Knowledge contributions',
                'TITLE_EN'     => 'Knowledge contributions',
                'FIELD_NAME'   => 'UPLOADED_DOCS',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="#VALUE#" target="_blank">Go to view</a>'
                ]
            ],
            [
                'TITLE_RU'     => 'Telegram login',
                'TITLE_EN'     => 'Telegram login',
                'FIELD_NAME'   => 'TELEGRAM',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="https://t.me/#VALUE#">#VALUE#</a>'
                ]
            ],
            [
                'TITLE_RU'     => 'Additional email',
                'TITLE_EN'     => 'Additional email',
                'FIELD_NAME'   => 'EMAIL',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '<a href="mailto:#VALUE#">#VALUE#</a>'
                ]
            ],
            [
                'TITLE_RU'     => 'Executive assistant',
                'TITLE_EN'     => 'Executive assistant',
                'FIELD_NAME'   => 'ASSISTANT',
                'USER_TYPE_ID' => 'cbit.employee',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Executive',
                'TITLE_EN'     => 'Executive',
                'FIELD_NAME'   => 'EXECUTIVE',
                'USER_TYPE_ID' => 'cbit.employee',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'DGL',
                'TITLE_EN'     => 'DGL',
                'FIELD_NAME'   => 'DGL',
                'USER_TYPE_ID' => 'cbit.employee',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Staffing manager',
                'TITLE_EN'     => 'Staffing manager',
                'FIELD_NAME'   => 'STAFFING_MANAGER',
                'USER_TYPE_ID' => 'cbit.employee',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => []
            ],
            [
                'TITLE_RU'     => 'Short summary',
                'TITLE_EN'     => 'Short summary',
                'FIELD_NAME'   => 'SHORT_SUMMARY',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Covered industries',
                'TITLE_EN'     => 'Covered industries',
                'FIELD_NAME'   => 'COVERED_INDUSTRIES',
                'USER_TYPE_ID' => 'iblock_element',
                'MULTIPLE'     => 'Y',
                'MANDATORY'    => 'N',
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
                'TITLE_RU'     => 'Tyb rating',
                'TITLE_EN'     => 'Tyb rating',
                'FIELD_NAME'   => 'TYB_RATING',
                'USER_TYPE_ID' => 'integer',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => 'N',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => 0,
                ]
            ],
        ];
    }
}