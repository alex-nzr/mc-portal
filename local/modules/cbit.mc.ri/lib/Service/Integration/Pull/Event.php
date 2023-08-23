<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Event.php
 * 15.12.2022 14:23
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Integration\Pull;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Pull\Event as BxPullEvent;
use Cbit\Mc\RI\Internals\Control\ServiceManager;

/**
 * Class Event
 * @package Cbit\Mc\RI\Service\Integration\Pull
 */
class Event
{
    /**
     * @param string $command
     * @param array $params
     * @param array $userIds
     */
    public static function addToStack(string $command, array $params, array $userIds = []): void
    {
        if (empty($userIds) && !empty($GLOBALS['USER']))
        {
            $userIds = [ (int)CurrentUser::get()->getId() ];
        }

        BxPullEvent::add($userIds, [
            'module_id' => ServiceManager::getModuleId(),
            'command' => $command,
            'params' => $params,
        ]);
    }
}