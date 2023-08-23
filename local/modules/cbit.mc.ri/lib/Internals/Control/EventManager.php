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
namespace Cbit\Mc\RI\Internals\Control;

use Bitrix\Main\Event;
use Cbit\Mc\Core\Internals\Control\BaseEventManager;
use Cbit\Mc\RI\Handler;
use Cbit\Mc\RI\Service\Integration\Pull;

/**
 * Class EventManager
 * @package Cbit\Mc\RI\Internals\Control
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
            'main' => [
                'OnEpilog' => [
                    [
                        'class'  => Handler\Main::class,
                        'method' => 'changeItemStageOnFirstView',
                        'sort'   => 100
                    ],
                ]
            ],
            'crm' => [
                'onEntityDetailsTabsInitialized' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'changeDetailCardTabs',
                        'sort'   => 410
                    ],
                ],
                '\Bitrix\Crm\Timeline\Entity\Timeline::OnBeforeAdd' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'onBeforeCrmTimelineCommentAdd',
                        'sort'   => 410
                    ],
                ],
                '\Bitrix\Crm\Timeline\Entity\Timeline::OnAfterAdd' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'onAfterCrmTimelineCommentAdd',
                        'sort'   => 410
                    ],
                ],
                '\Bitrix\Crm\Timeline\Entity\Timeline::OnBeforeUpdate' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'onBeforeCrmTimelineCommentUpdate',
                        'sort'   => 410
                    ],
                ],
                '\Bitrix\Crm\Timeline\Entity\Timeline::OnBeforeDelete' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'onBeforeCrmTimelineCommentDelete',
                        'sort'   => 410
                    ],
                ],
            ],
            'pull' => [
                'OnGetDependentModule' => [
                    [
                        'class'     => Pull\Handler::class,
                        'method'    => 'bindDependentModule',
                        'sort'      => 500
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