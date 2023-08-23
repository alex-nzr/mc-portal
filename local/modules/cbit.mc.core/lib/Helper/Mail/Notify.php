<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Notify.php
 * 02.02.2023 13:49
 * ==================================================
 */


namespace Cbit\Mc\Core\Helper\Mail;

use Cbit\Mc\Core\Helper\Main\User;
use CEvent;

/**
 * @class Notify
 * @package Cbit\Mc\Core\Helper\Mail
 */
class Notify
{
    /**
     * @param int $toUserId
     * @param int $fromUserId
     * @param string $title
     * @param string $message
     * @param string $additionalEmailTo
     * @param array $fileIds
     * @return void
     * @throws \Exception
     */
    public static function sendImmediate(
        int $toUserId, int $fromUserId, string $title, string $message,
        string $additionalEmailTo = '', array $fileIds = []
    ): void
    {
        $arFields = [
            'EMAIL_TO'     => User::getUserEmailById($toUserId),
            'FROM_USER_ID' => $fromUserId,
            'FROM_USER'    => User::getUserNameById($fromUserId),
            'TITLE'        => $title,
            'MESSAGE'      => $message,
        ];

        if (!empty($additionalEmailTo))
        {
            $arFields['EMAIL_TO'] = $arFields['EMAIL_TO'] . ',' . $additionalEmailTo;
        }

        CEvent::SendImmediate( 'IM_NEW_NOTIFY', 's1', $arFields, 'Y', '', $fileIds);
    }
}