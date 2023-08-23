<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EventManager.php
 * 21.11.2022 12:46
 * ==================================================
 */
namespace Cbit\Mc\Zup\Internals\Control;

use Cbit\Mc\Core\Internals\Control\BaseEventManager;

/**
 * Class EventManager
 * @package Cbit\Mc\Zup\Internals\Control
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
        return [];
    }
}