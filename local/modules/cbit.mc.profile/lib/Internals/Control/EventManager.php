<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EventManager.php
 * 25.10.2022 19:46
 * ==================================================
 */
namespace Cbit\Mc\Profile\Internals\Control;

use Cbit\Mc\Core\Internals\Control\BaseEventManager;
use Cbit\Mc\Profile\Handler\User;

/**
 * Class EventManager
 * @package Cbit\Mc\Profile\Internals\Control
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
            'main' => [
                'OnBeforeUserUpdate' => [
                    [
                        'class'  => User::class,
                        'method' => 'onBeforeUpdate',
                        'sort'   => 100
                    ],
                ],
                'OnAfterUserUpdate' => [
                    [
                        'class'  => User::class,
                        'method' => 'onAfterUpdate',
                        'sort'   => 100
                    ],
                ],
                'OnAfterUserAdd' => [
                    [
                        'class'  => User::class,
                        'method' => 'onAfterAdd',
                        'sort'   => 100
                    ],
                ],
            ]
        ];
    }
}