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
namespace Cbit\Mc\Staffing\Internals\Control;

use Cbit\Mc\Core\Internals\Control\BaseEventManager;
use Cbit\Mc\Staffing\Handler;

/**
 * Class EventManager
 * @package Cbit\Mc\Staffing\Internals\Control
 */
class EventManager extends BaseEventManager
{
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
                        'sort'   => 400
                    ],
                ],
            ],
        ];
    }
}