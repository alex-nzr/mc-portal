<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - User.php
 * 15.12.2022 23:08
 * ==================================================
 */

namespace Cbit\Mc\RI\Helper\Main;

use Bitrix\Crm\Item;
use Bitrix\Main\Engine\CurrentUser;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Container;
use CUser;

/**
 * Class User
 * @package Cbit\Mc\RI\Helper\Main
 */
class User extends \Cbit\Mc\Core\Helper\Main\User
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getUnScoredRequestsOfCurrentUser(): array
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        $result = [];
        $userId = (int)CurrentUser::get()->getId();
        if ($userId > 0)
        {
            $entity = Dynamic::getInstance();
            $typeId = $entity->getTypeId();
            $items = $entity->select(
                ['ID', 'TITLE'],
                [
                    Item::FIELD_NAME_CREATED_BY => $userId,
                    Item::FIELD_NAME_STAGE_ID => [
                        $entity->getStatusPrefix($entity->getDefaultCategoryId()).Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS,
                        //$entity->getStatusPrefix($entity->getDefaultCategoryId()).Constants::DYNAMIC_STAGE_DEFAULT_FAIL
                    ],
                    [
                        'LOGIC' => 'OR',
                        ['UF_CRM_'.$typeId.'_SCORE_SPEED' => null],
                        ['UF_CRM_'.$typeId.'_SCORE_WORK' => null],
                        ['UF_CRM_'.$typeId.'_SCORE_COMMUNICATION' => null],
                    ]
                ]
            );
            if (!empty($items))
            {
                foreach ($items as $key => $item) {
                    $result[$item->getId()] = [
                        'TITLE' => $item->getTitle(),
                        'NUMBER'=> $key + 1,
                        'URL'   => Container::getInstance()->getRouter()->getItemDetailUrlById($item->getId())
                    ];
                }
            }
        }
        return $result;
    }
}