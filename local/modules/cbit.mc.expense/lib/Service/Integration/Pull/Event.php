<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Event.php
 * 20.01.2023 14:23
 * ==================================================
 */

namespace Cbit\Mc\Expense\Service\Integration\Pull;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Pull\Event as BxPullEvent;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;

/**
 * @class Event
 * @package Cbit\Mc\Expense\Service\Integration\Pull
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