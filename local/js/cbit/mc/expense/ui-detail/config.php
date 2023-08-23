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

use Cbit\Mc\Expense\Config\Configuration;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity;
use Cbit\Mc\Expense\Helper\UI\Toolbar;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Container;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

try
{
    $context       = Container::getInstance()->getContext();
    $typeOfRequest = '';
    $item          = $context->getItem();

    if (!empty($item))
    {
        if ($item->getCategoryId() > 0)
        {
            $typeOfRequest = Configuration::getInstance()->getTypeOfRequestByCategoryId($item->getCategoryId());
        }
    }

    $settings = [
        'moduleId'             => ServiceManager::getModuleId(),
        'typeId'               => Entity\Dynamic::getInstance()->getTypeId(),
        'entityTypeId'         => Entity\Dynamic::getInstance()->getEntityTypeId(),
        'entityId'             => $item ? $item->getId() : 0,
        'opportunity'          => !is_null($item) ? $item->getOpportunity() : null,
        'isNew'                => $item && $item->isNew(),
        'isAdmin'              => $context->isCurrentUserAdmin(),
        'typeOfRequest'        => $typeOfRequest,
        'splitBtnDatasetId'    => Toolbar::SPLIT_AMOUNT_BTN_ID,
        'pullActions'          => [
            'showRejectReasonPopup' => Constants::SHOW_REJECT_REASON_POPUP_ACTION
        ],
        'postActionKey'        => Constants::REQUEST_ACTION_KEY_CODE,
        'splitRequestAction'   => Constants::SPLIT_REQUEST_AMOUNT_ACTION
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