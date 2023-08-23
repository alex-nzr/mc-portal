<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EventManager.php
 * 17.01.2023 12:46
 * ==================================================
 */
namespace Cbit\Mc\Partner\Internals\Control;

use Bitrix\Main\Event;
use Cbit\Mc\Core\Internals\Control\BaseEventManager;
use Cbit\Mc\Partner\Handler;

/**
 * Class EventManager
 * @package Cbit\Mc\Partner\Internals\Control
 */
class EventManager extends BaseEventManager
{
    const ON_ENTITY_DETAILS_CONTEXT = 'onEntityDetailsContextReady';

    /**
     * @return array
     */
    public static function getBasicEvents(): array
    {
        return [
            'main' => [
                'onPageStart' => [
                    [
                        'module' => ServiceManager::getModuleId(),
                        'class'  => static::class,
                        'method' => 'addRuntimeEventHandlers',
                        'sort'   => 100
                    ],
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getRunTimeEvents(): array
    {
        return [
            'crm' => [
                'onEntityDetailsTabsInitialized' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'changeDetailCardTabs',
                        'sort'   => 410
                    ],
                ],
            ],
            ServiceManager::getModuleId() => [
                static::ON_ENTITY_DETAILS_CONTEXT => [
                    [
                        'class'  => ServiceManager::class,
                        'method' => 'addDetailPageExtensions',
                        'sort'   => 500
                    ],
                ],
            ]
        ];
    }

    /**
     * @return void
     */
    public static function sendEntityDetailsContextReadyEvent(): void
    {
        $event = new Event(ServiceManager::getModuleId(),static::ON_ENTITY_DETAILS_CONTEXT);
        $event->send();
    }
}