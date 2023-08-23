<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - User.php
 * 20.01.2023 18:38
 * ==================================================
 */

namespace Cbit\Mc\Expense\Helper\Main;

use Bitrix\Crm\Item;
use Bitrix\Main\Engine\CurrentUser;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Internals\Debug\Logger;
use Cbit\Mc\Expense\Service\Container;
use CUser;

/**
 * @class User
 * @package Cbit\Mc\Expense\Helper\Main
 */
class User extends \Cbit\Mc\Core\Helper\Main\User
{
    /**
     * @return int
     * @throws \Exception
     */
    public static function getCountOfRejectedRequestsByCurrentUser(): int
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        $userId = (int)CurrentUser::get()->getId();
        if ($userId > 0)
        {
            $entity = Dynamic::getInstance();
            $items = $entity->getItemFactory()->getDataClass()::query()
                ->setSelect([Item::FIELD_NAME_ID])
                ->setFilter([
                    Item::FIELD_NAME_CREATED_BY => $userId,
                    Item::FIELD_NAME_STAGE_ID => [
                        $entity->getStatusPrefix($entity->getDefaultCategoryId()).Constants::DYNAMIC_STAGE_DEFAULT_REJECTED,
                        $entity->getStatusPrefix($entity->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE)).Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_REJECTED,
                        $entity->getStatusPrefix($entity->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE)).Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_REJECTED,
                    ],
                ])
                ->fetchAll();
            return count($items);
        }
        return 0;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getCountOfNewRequestsAssignedByCurrentUser(): int
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        $userId = (int)CurrentUser::get()->getId();

        if ($userId > 0)
        {
            $entity = Dynamic::getInstance();
            $items = $entity->getItemFactory()->getDataClass()::query()
                ->setSelect([Item::FIELD_NAME_ID, Item::FIELD_NAME_ASSIGNED])
                ->setFilter([
                    Item::FIELD_NAME_ASSIGNED => $userId,
                    Item::FIELD_NAME_STAGE_ID => [
                        $entity->getStatusPrefix($entity->getDefaultCategoryId()).Constants::DYNAMIC_STAGE_DEFAULT_SUBMITTED,
                        $entity->getStatusPrefix($entity->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE)).Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_SUBMITTED,
                        $entity->getStatusPrefix($entity->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE)).Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_SUBMITTED,
                    ],
                ])
                ->fetchAll();

            return count($items);
        }
        return 0;
    }
}