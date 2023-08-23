<?php

use Cbit\Mc\Core\Service\Integration\UI\EntitySelector\RIUserProvider;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Cbit\\Mc\\Core\\Controller',
        ],
        'readonly' => true,
    ],
    'ui.entity-selector' => [
        'value'    => [
            'entities'   => [
                [
                    'entityId' => RIUserProvider::ENTITY_ID,
                    'provider' => [
                        'moduleId'  => 'cbit.mc.core',
                        'className' => RIUserProvider::class,
                    ],
                ],
            ],
            'extensions' => ['cbit.mc.core.ri-user-selector'],
        ],
        'readonly' => true,
    ],
];