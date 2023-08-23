<?php

use Cbit\Mc\Partner\Service\Integration\Intranet\CustomSectionProvider;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Cbit\\Mc\\Partner\\Controller',
        ],
        'readonly' => true,
    ],
    'intranet.customSection' => [
        'value' => [
            'provider' => CustomSectionProvider::class,
        ],
    ],
];