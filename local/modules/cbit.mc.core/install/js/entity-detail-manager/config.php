<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - config.php
 * 21.02.2023 23:08
 * ==================================================
 */


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}



return [
    'css' => 'dist/index.bundle.css',
    'js' => 'dist/index.bundle.js',
    'rel' => [
		'main.core',
		'ui.stageflow',
	],
    'skip_core' => false,
    'settings'  => [],
    'lang' => [
        'lang/ru/js_lang.php',
        'lang/en/js_lang.php'
    ],
];