<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EventManager.php
 * 25.11.2022 12:46
 * ==================================================
 */
namespace Cbit\Mc\Core\Internals\Control;

use Cbit\Mc\Core\Handler\Main;
use Cbit\Mc\Core\Internals\UserField\Type\AssignedRI;
use Cbit\Mc\Core\Internals\UserField\Type\Employee;
use Cbit\Mc\Core\Internals\UserField\Type\FileType;

/**
 * Class EventManager
 * @package Cbit\Mc\Core\Internals\Control
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
                        'sort'   => 100,
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
                'onProlog' => [
                    [
                        'class'  => Main::class,
                        'method' => 'includeCoreDependencies',
                        'sort'   => 100
                    ],
                ],
                'OnUserTypeBuildList' => [
                    [
                        'class'     => Employee::class,
                        'method'    => 'getUserTypeDescription',
                        'sort'      => 500
                    ],
                    [
                        'class'     => AssignedRI::class,
                        'method'    => 'getUserTypeDescription',
                        'sort'      => 500
                    ],
                    [
                        'class'     => FileType::class,
                        'method'    => 'getUserTypeDescription',
                        'sort'      => 500
                    ],
                ]
            ]
        ];
    }
}