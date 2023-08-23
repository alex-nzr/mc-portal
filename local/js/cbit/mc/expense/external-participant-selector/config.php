<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - config.php
 * 31.01.2023 15:08
 * ==================================================
 */

use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Integration\UI\EntitySelector\ExternalParticipantsProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

return [
    'css' => 'dist/index.bundle.css',
    'js' => 'dist/index.bundle.js',
    'rel' => [
		'main.core',
	],
    'skip_core' => false,
    'settings'  => [
        'moduleId'           => ServiceManager::getModuleId(),
        'providerEntityId'   => ExternalParticipantsProvider::ENTITY_ID,
        'providerEntityType' => ExternalParticipantsProvider::ENTITY_TYPE,
    ],
    'lang' => [
        'lang/ru/js_lang_phrases.php',
        'lang/en/js_lang_phrases.php'
    ],
];