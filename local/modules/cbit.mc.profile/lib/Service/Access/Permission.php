<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Permission.php
 * 13.11.2022 20:21
 * ==================================================
 */


namespace Cbit\Mc\Profile\Service\Access;

use Bitrix\Main\UserTable;
use Bitrix\Main\Engine\CurrentUser;

/**
 * Class Permission
 * @package Cbit\Mc\Profile\Service\Access
 */
class Permission extends \Cbit\Mc\Core\Service\Access\Permission
{
    /**
     * @param int $profileId
     * @return bool
     */
    public static function canUserEditProfile(int $profileId): bool
    {
        if (!empty($GLOBALS['USER']))
        {
            $isOwnProfile = ((int)CurrentUser::get()->getId() === $profileId);
            $isAdmin      = CurrentUser::get()->isAdmin();
            return (
                $isAdmin
                || $isOwnProfile
                || Permission::isUserInHrTeamGroup()
                || Permission::isUserInEaLeadersGroup()
                || Permission::isUserInPdStaffingGroup()
                || Permission::isUserInRiManagersGroup()
            );
        }
        return false;
    }

    /**
     * @param int $profileId
     * @return bool
     * @throws \Exception
     */
    public static function canUserViewProfile(int $profileId): bool
    {
        if (!empty($GLOBALS['USER']) )
        {
            if (CurrentUser::get()->isAdmin())
            {
                return true;
            }

            $user = UserTable::query()
                ->setSelect(['ACTIVE'])
                ->where('ID', $profileId)
                ->fetch();

            if (is_array($user))
            {
                return ($user['ACTIVE'] === 'Y');
            }
        }
        return false;
    }
}