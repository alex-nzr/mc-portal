<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - UserPermissions.php
 * 24.01.2023 11:02
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Service\Access;


/**
 * @class UserPermissions
 * @package Cbit\Mc\Staffing\Service\Access
 */
class UserPermissions extends \Bitrix\Crm\Service\UserPermissions
{
    private bool $userHasPdStaffingRights;

    /**
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        parent::__construct($userId);
        $this->userHasPdStaffingRights = Permission::isUserInPdStaffingGroup();
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkReadPermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        return ($this->isAdmin() || $this->userHasPdStaffingRights);
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkUpdatePermissions(int $entityTypeId, int $id, ?int $categoryId = null): bool
    {
        return $this->isAdmin();
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkDeletePermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        return $this->isAdmin();
    }

    /**
     * @return bool
     */
    public function hasPdStaffingPermissions(): bool
    {
        return ($this->isAdmin() || $this->userHasPdStaffingRights);
    }
}