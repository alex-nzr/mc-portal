<?php

use Cbit\Mc\Subscription\Service\Integration\Intranet\CustomSectionProvider;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Cbit\\Mc\\Subscription\\Controller',
        ],
        'readonly' => true,
    ],
    'intranet.customSection' => [
        'value' => [
            'provider' => CustomSectionProvider::class,
        ],
    ],
];