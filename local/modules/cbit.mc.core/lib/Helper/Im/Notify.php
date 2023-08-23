<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Notify.php
 * 25.11.2022 19:48
 * ==================================================
 */

namespace Cbit\Mc\Core\Helper\Im;

use Bitrix\Main\Localization\Loc;
use CIMMessageParamAttach;
use CIMNotify;

Loc::loadMessages(__FILE__);

/**
 * Class Notify
 * @package Cbit\Mc\Core\Helper\Im
 */
class Notify
{
    /**
     * @param array $toUsers
     * @param string $message
     * @param array $attachments
     * @param string $module
     */
    public static function add(array $toUsers, string $message, array $attachments = [], string $module = 'crm'): void
    {
        foreach ($toUsers as $toUser)
        {
            $arMessageFields = [
                "TO_USER_ID"         => $toUser,
                "FROM_USER_ID"       => 0,
                "NOTIFY_TYPE"        => IM_NOTIFY_SYSTEM,
                "NOTIFY_MODULE"      => $module,
                "NOTIFY_MESSAGE"     => $message,
                "NOTIFY_MESSAGE_OUT" => $message,
                "ATTACH"             => $attachments
            ];

            CIMNotify::Add($arMessageFields);
        }
    }

    /**
     * @param int $userId
     * @param string $title
     * @param string $message
     */
    public static function createTextNotify(int $userId, string $title, string $message): void
    {
        $attach = new CIMMessageParamAttach(null, "#95c255");
        $attach->AddMessage($message);

        $users = [$userId];
        static::add( $users, $title, [$attach] );
    }

    /**
     * @param int $userId
     * @param string $title
     * @param string $linkText
     * @param string $linkUrl
     * @return void
     */
    public static function createLinkNotify(int $userId, string $title, string $linkText, string $linkUrl): void
    {
        $attach = new CIMMessageParamAttach(null, "#95c255");
        $attach->AddLink(Array(
            "NAME" => $linkText,
            "DESC" => '',
            "LINK" => $linkUrl
        ));

        $users = [$userId];
        static::add( $users, $title, [$attach] );
    }
}