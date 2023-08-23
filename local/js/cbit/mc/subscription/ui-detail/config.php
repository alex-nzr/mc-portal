<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - config.php
 * 18.01.2023 15:08
 * ==================================================
 */

use Cbit\Mc\Subscription\Entity;
use Cbit\Mc\Subscription\Internals\Control\ServiceManager;
use Cbit\Mc\Subscription\Service\Container;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

try
{
    $context       = Container::getInstance()->getContext();
    $typeOfRequest = '';
    $item          = $context->getItem();

    $settings = [
        'moduleId'             => ServiceManager::getModuleId(),
        'typeId'               => Entity\Dynamic::getInstance()->getTypeId(),
        'entityTypeId'         => Entity\Dynamic::getInstance()->getEntityTypeId(),
        'entityId'             => $item ? $item->getId() : 0,
        'isNew'                => $item && $item->isNew(),
        'isAdmin'              => $context->isCurrentUserAdmin(),
    ];
}
catch (Exception $e)
{
    $settings = [];
}

return [
    'css' => 'dist/index.bundle.css',
    'js' => 'dist/index.bundle.js',
    'rel' => [
		'main.core',
	],
    'skip_core' => false,
    'settings'  => $settings,
    'lang' => [
        'lang/ru/js_lang_phrases.php',
        'lang/en/js_lang_phrases.php'
    ],
];