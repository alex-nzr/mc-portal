<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - config.php
 * 15.12.2022 15:08
 * ==================================================
 */

use Bitrix\Main\Engine\CurrentUser;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Helper\Main\User;
use Cbit\Mc\RI\Helper\UI\Toolbar;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Service\Access\FieldAccess;
use Cbit\Mc\RI\Service\Container;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

try
{
    $perms    = Container::getInstance()->getUserPermissions();
    $context  = Container::getInstance()->getContext();
    $entity   = Dynamic::getInstance();
    $typeId   = $entity->getTypeId();
    $unScoredRequests = User::getUnScoredRequestsOfCurrentUser();
    $settings = [
        'moduleId'                  => ServiceManager::getModuleId(),
        'typeId'                    => $typeId,
        'entityTypeId'              => $entity->getEntityTypeId(),
        'isAdmin'                   => $perms->isAdmin(),
        'cancelReasonsList'         => UserField::getUfListValuesByCode("UF_CRM_".$typeId."_CANCEL_REASON"),
        'cancelRequestBtnDatasetId' => Toolbar::CANCEL_REQUEST_BTN_ID,
        'unScoredRequests'          => $unScoredRequests,
        'hasUnScoredRequests'       => (count($unScoredRequests) > 0),
    ];

    $item = $context->getItem();
    if (!empty($item))
    {
        $settings = array_merge($settings, [
            'entityId'                   => $item->getId(),
            'isOnSuccessStage'           => $entity->isItemInSuccessStage($item),
            'isOnUnassignedStages'       => $entity->isItemInUnassignedStages($item),
            'isItemScored'               => $entity->isItemScoringCompleted($item),
            'isItemCreatedByCurrentUser' => $context->getUserId() === (int)$item->getCreatedBy(),
            'isCancelReasonRequired'     => !$entity->isItemInUnassignedStages($item)
                                            && !$entity->isItemInSuccessStage($item)
                                            && !$entity->isItemInFailStage($item),
            'isNew'                      => $item->isNew(),
            'hasRiPerms'                 => $perms->hasUserAnyPermissionsForRi(),
            'pullActions'                => [
                'showScoringPopup' => Constants::SHOW_SCORING_POPUP_ACTION
            ],
        ]);
    }
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