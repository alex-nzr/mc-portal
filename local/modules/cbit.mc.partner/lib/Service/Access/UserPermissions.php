<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - UserPermissions.php
 * 18.01.2023 17:35
 * ==================================================
 */

namespace Cbit\Mc\Partner\Service\Access;

/**
 * @class UserPermissions
 * @package Cbit\Mc\Partner\Service\Access
 */
class UserPermissions extends \Cbit\Mc\RI\Service\Access\UserPermissions
{
    /**
     * @param int $entityTypeId
     * @param int|null $categoryId
     * @param string|null $stageId
     * @return bool
     */
    public function checkAddPermissions(int $entityTypeId, ?int $categoryId = null, ?string $stageId = null): bool
    {
        return ($this->isAdmin() || $this->hasUserAnyPermissionsForRi());
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkUpdatePermissions(int $entityTypeId, int $id, ?int $categoryId = null): bool
    {
        return ($this->isAdmin() || $this->hasUserAnyPermissionsForRi());
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkDeletePermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        return ($this->isAdmin() || $this->hasUserAnyPermissionsForRi());
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkReadPermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        return ($GLOBALS['USER'] instanceof \CUser) ? $GLOBALS['USER']->IsAuthorized() : false;
    }
}