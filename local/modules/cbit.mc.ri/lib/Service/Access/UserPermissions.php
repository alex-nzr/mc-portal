<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - UserPermissions.php
 * 27.12.2022 13:03
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Access;

use Bitrix\Crm\Item;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Container;

/**
 * @class UserPermissions
 * @package Cbit\Mc\RI\Service\Access
 */
class UserPermissions extends \Bitrix\Crm\Service\UserPermissions
{
    private bool   $userInRiAnalysts;
    private bool   $userInRiManagers;
    private bool   $userCoordinatorToday;

    /**
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        parent::__construct($userId);
        $this->userInRiAnalysts     = Permission::isUserInRiAnalystsGroup($userId);
        $this->userInRiManagers     = Permission::isUserInRiManagersGroup($userId);
        $this->userCoordinatorToday = ($userId === Configuration::getInstance()->getCurrentCoordinatorId());
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     * @throws \Exception
     */
    public function checkReadPermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }

        return parent::checkReadPermissions($entityTypeId, $id, $categoryId);
    }

    /**
     * @param int $entityTypeId
     * @param int|null $categoryId
     * @param string|null $stageId
     * @return bool
     */
    public function checkAddPermissions(int $entityTypeId, ?int $categoryId = null, ?string $stageId = null): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }

        return parent::checkAddPermissions($entityTypeId, $categoryId, $stageId);
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     * @throws \Exception
     */
    public function checkUpdatePermissions(int $entityTypeId, int $id, ?int $categoryId = null): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }

        if ($this->hasUserAnyPermissionsForRi())
        {
            return true;
        }

        if (Container::getInstance()->getRouter()->isListPage())
        {
            return false;
        }

        $item = Dynamic::getInstance()->getById($id);
        if ($item)
        {
            $createdBy = $item->getCreatedBy();
            return ($this->userId === $createdBy);
        }

        return parent::checkUpdatePermissions($entityTypeId, $id, $categoryId);
    }

    /**
     * @param int $entityTypeId
     * @param int $id
     * @param int|null $categoryId
     * @return bool
     */
    public function checkDeletePermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }
        return false;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     * @throws \Exception
     */
    public function canUserCancelRequest(Item $item): bool
    {
        if (!Dynamic::getInstance()->isItemInFinalStage($item))
        {
            if ($this->isAdmin())
            {
                return true;
            }

            if ($this->hasUserAnyPermissionsForRi())
            {
                return true;
            }

            if ($this->userId === (int)$item->getCreatedBy())
            {
                $stagePrefix    = Dynamic::getInstance()->getStatusPrefix($item->getCategoryId());
                $shortStageCode = substr($item->getStageId(), strlen($stagePrefix));
                if (
                    $shortStageCode === Constants::DYNAMIC_STAGE_DEFAULT_NEW
                    || $shortStageCode === Constants::DYNAMIC_STAGE_DEFAULT_REVIEW
                    || $shortStageCode === Constants::DYNAMIC_STAGE_DEFAULT_ASSIGNED
                ){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     * @throws \Exception
     */
    public function canUserChangeStage(Item $item): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }

        //only admin can change stage without "cancel" button
        if (Dynamic::getInstance()->isItemInFailStage($item))
        {
            //check if stage changed by click on cancel button
            $typeId = Dynamic::getInstance()->getTypeId();
            return !empty($item->get('UF_CRM_'.$typeId.'_CANCEL_REASON'))
                    && !empty($item->get('UF_CRM_'.$typeId.'_CANCEL_COMMENT'));
        }

        if (Dynamic::getInstance()->isItemInSuccessStage($item))
        {
            $typeId = Dynamic::getInstance()->getTypeId();
            return !empty($item->get('UF_CRM_'.$typeId.'_RESULT_DESCRIPTION'));
        }

        if ($this->hasUserAnyPermissionsForRi())
        {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasUserRiManagerPermissions(): bool
    {
        return ($this->isAdmin() || $this->userInRiManagers);
    }

    /**
     * @return bool
     */
    public function hasUserRiAnalystPermissions(): bool
    {
        return ($this->isAdmin() || $this->userInRiAnalysts);
    }

    /**
     * @return bool
     */
    public function hasUserAnyPermissionsForRi(): bool
    {
        return ($this->isAdmin() || $this->userInRiManagers || $this->userInRiAnalysts);
    }

    /**
     * @return bool
     */
    public function checkChangeAssignedPermissions(): bool
    {
        return ($this->isAdmin() || $this->userInRiManagers || $this->userCoordinatorToday);
    }
}