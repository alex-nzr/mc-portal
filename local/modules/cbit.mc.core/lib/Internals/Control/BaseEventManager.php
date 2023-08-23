<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - BaseEventManager.php
 * 01.03.2023 12:20
 * ==================================================
 */
namespace Cbit\Mc\Core\Internals\Control;

use Bitrix\Main\EventManager as BitrixEventManager;
use Cbit\Mc\Core\Internals\Contract\IEventManager;

/**
 * @class BaseEventManager
 * @package Cbit\Mc\Core\Internals\Control
 */
class BaseEventManager implements IEventManager
{
    /**
     * @return void
     */
    public static function addBasicEventHandlers(): void
    {
        static::addEventHandlersFromArray(static::getBasicEvents(), true);
    }

    /**
     * @return void
     */
    public static function addRuntimeEventHandlers(): void
    {
        static::addEventHandlersFromArray(static::getRunTimeEvents());
    }

    /**
     * @param array $events
     * @param bool $register
     * @return void
     */
    public static function addEventHandlersFromArray(array $events, bool $register = false): void
    {
        foreach ($events as $moduleId => $event)
        {
            foreach ($event as $eventName => $handlers)
            {
                foreach ($handlers as $handler)
                {
                    if ($register)
                    {
                        BitrixEventManager::getInstance()->registerEventHandler(
                            $moduleId,
                            $eventName,
                            $handler['module'],
                            $handler['class'],
                            $handler['method'],
                            $handler['sort'] ?? 100,
                        );
                    }
                    else
                    {
                        BitrixEventManager::getInstance()->addEventHandler(
                            $moduleId,
                            $eventName,
                            [$handler['class'], $handler['method']],
                            false,
                            $handler['sort'] ?? 100
                        );
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    public static function removeBasicEventHandlers(): void
    {
        foreach (static::getBasicEvents() as $moduleId => $event)
        {
            foreach ($event as $eventName => $handlers)
            {
                foreach ($handlers as $handler)
                {
                    BitrixEventManager::getInstance()->unRegisterEventHandler(
                        $moduleId,
                        $eventName,
                        $handler['module'],
                        $handler['class'],
                        $handler['method'],
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getBasicEvents(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getRunTimeEvents(): array
    {
        return [];
    }
}