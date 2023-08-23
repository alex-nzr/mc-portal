<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Permission.php
 * 25.11.2022 20:21
 * ==================================================
 */


namespace Cbit\Mc\Core\Service\Access;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserGroupTable;
use Cbit\Mc\Core\Config\Configuration;
use CUser;
use Exception;

/**
 * @class Permission
 * @package Cbit\Mc\Core\Service\Access
 */
class Permission
{
    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInHrTeamGroup(?int $userId = null): bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getHrTeamIds());
    }

    /**
     * @return int[]
     */
    public static function getHrTeamIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getHrTeamGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            //log error
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInVgLeadersGroup(?int $userId = null): bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getVgLeadersIds());
    }

    /**
     * @return int[]
     */
    public static function getVgLeadersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getVgLeadersGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            //log error
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInEaLeadersGroup(?int $userId = null):bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getEaLeadersIds());
    }

    /**
     * @return int[]
     */
    public static function getEaLeadersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getEaLeadersGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            //log error
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInPdStaffingGroup(?int $userId = null):bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getPdStaffingIds());
    }

    /**
     * @return int[]
     */
    public static function getPdStaffingIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getPdStaffingGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            //log error
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInRiAnalystsGroup(?int $userId = null):bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getRiAnalystsIds());
    }

    /**
     * @return int[]
     */
    public static function getRiAnalystsIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getRiAnalystsGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            //log error
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInRiManagersGroup(?int $userId = null):bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getRiManagersIds());
    }

    /**
     * @return int[]
     */
    public static function getRiManagersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getRiManagersGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            //log error
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInExpensesITGroup(?int $userId = null): bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getExpensesITUsersIds());
    }

    /**
     * @return int[]
     */
    public static function getExpensesITUsersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getExpensesITGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInExpensesTravelGroup(?int $userId = null): bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getExpensesTravelUsersIds());
    }

    /**
     * @return int[]
     */
    public static function getExpensesTravelUsersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getExpensesTravelGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInExpensesFinanceGroup(?int $userId = null): bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getExpensesFinanceUsersIds());
    }

    /**
     * @return int[]
     */
    public static function getExpensesFinanceUsersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getExpensesFinanceGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            return [];
        }
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public static function isUserInExpensesPayrollGroup(?int $userId = null): bool
    {
        if (empty($GLOBALS['USER']))
        {
            $GLOBALS['USER'] = new CUser;
        }

        if (empty($userId))
        {
            $userId = (int)CurrentUser::get()->getId();
        }

        return in_array($userId, static::getExpensesPayrollUsersIds());
    }

    /**
     * @return int[]
     */
    public static function getExpensesPayrollUsersIds(): array
    {
        try
        {
            $relations = UserGroupTable::query()
                ->setFilter([
                    'GROUP_ID' => Configuration::getInstance()->getExpensesPayrollGroupIds(),
                ])
                ->setSelect(['USER_ID'])
                ->fetchAll();

            $ids = [];
            foreach ($relations as $relation) {
                $ids[] = (int)$relation['USER_ID'];
            }
            return $ids;
        }
        catch (Exception)
        {
            return [];
        }
    }
}