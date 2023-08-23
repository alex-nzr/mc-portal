<?php

use Cbit\Mc\Expense\Service\Integration\Intranet\CustomSectionProvider;
use Cbit\Mc\Expense\Service\Integration\UI\EntitySelector\ChargeCodeProvider;
use Cbit\Mc\Expense\Service\Integration\UI\EntitySelector\ExternalParticipantsProvider;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Cbit\\Mc\\Expense\\Controller',
        ],
        'readonly' => true,
    ],
    'intranet.customSection' => [
        'value' => [
            'provider' => CustomSectionProvider::class,
        ],
    ],
    'ui.entity-selector' => [
        'value'    => [
            'entities'   => [
                [
                    'entityId' => ExternalParticipantsProvider::ENTITY_ID,
                    'provider' => [
                        'moduleId'  => 'cbit.mc.expense',
                        'className' => ExternalParticipantsProvider::class,
                    ],
                ],
                [
                    'entityId' => ChargeCodeProvider::ENTITY_ID,
                    'provider' => [
                        'moduleId'  => 'cbit.mc.expense',
                        'className' => ChargeCodeProvider::class,
                    ],
                ],
            ],
            'extensions' => [
                'cbit.mc.expense.external-participant-selector',
                'cbit.mc.expense.charge-code-selector'
            ],
        ],
        'readonly' => true,
    ],
];