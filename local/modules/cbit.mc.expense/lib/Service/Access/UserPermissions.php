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


namespace Cbit\Mc\Expense\Service\Access;

use Bitrix\Crm\Item;
use Cbit\Mc\Core\Config\Constants as CoreConstants;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Helper\Main\User;
use Cbit\Mc\Expense\Service\Container;

/**
 * @class UserPermissions
 * @package Cbit\Mc\Expense\Service\Access
 */
class UserPermissions extends \Bitrix\Crm\Service\UserPermissions
{
    private bool $userHasExpensesITRights;
    private bool $userHasExpensesTravelRights;
    private bool $userHasExpensesFinanceRights;
    private bool $userHasExpensesPayrollRights;
    private bool $userHasHrRights;

    private array $forbiddenTYBPositions = [
        CoreConstants::USER_POSITION_BAI,
        CoreConstants::USER_POSITION_PTI_1,
        CoreConstants::USER_POSITION_PTI_2,
        CoreConstants::USER_POSITION_PARTNER,
        CoreConstants::USER_POSITION_PRINCIPAL,
    ];

    /**
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        parent::__construct($userId);
        $this->userHasExpensesITRights      = Permission::isUserInExpensesITGroup($userId);
        $this->userHasExpensesTravelRights  = Permission::isUserInExpensesTravelGroup($userId);
        $this->userHasExpensesFinanceRights = Permission::isUserInExpensesFinanceGroup($userId);
        $this->userHasExpensesPayrollRights = Permission::isUserInExpensesPayrollGroup($userId);
        $this->userHasHrRights              = Permission::isUserInHrTeamGroup($userId);
    }

    /**
     * @param int $entityTypeId
     * @param int|null $categoryId
     * @param string|null $stageId
     * @return bool
     * @throws \Exception
     */
    public function checkAddPermissions(int $entityTypeId, ?int $categoryId = null, ?string $stageId = null): bool
    {
        /*$router = Container::getInstance()->getRouter();
        if ($router->isListPage())
        {
            $categoryId = $router->getCategoryIdFromUrl();
            if (empty($categoryId))
            {
                return false;
            }
        }*/

        if ($this->isAdmin())
        {
            return true;
        }

        if (!empty($categoryId))
        {
            $categoryCode = Dynamic::getInstance()->getCategoryCodeById($categoryId);
            if ($categoryCode === Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE)
            {
                return (User::getUserRatingById($this->userId) > 0)
                    && !in_array(User::getUserPositionEnById($this->userId), $this->forbiddenTYBPositions);
            }
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
    public function checkReadPermissions(int $entityTypeId, int $id = 0, ?int $categoryId = null): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }

        $item = Dynamic::getInstance()->getById($id);

        if (!$item && $this->hasUserAnyPermissionsForExpense())
        {
            return true;
        }

        if ($item)
        {
            $itCategories = [
                Constants::IT_EQUIPMENT_RECEIPT,
                Constants::IT_SOFT_RECEIPT,
                Constants::IT_LICENSES_RECEIPT,
                Constants::IT_HARDWARE_RECEIPT,
                Constants::IT_CELLULAR_RECEIPT,
            ];

            $travelCategories = [
                Constants::TRAVEL_LAUNDRY_GYM_RECEIPT,
                Constants::TRAVEL_WEEKEND_RECEIPT,
                Constants::TRAVEL_SO_TICKET_RECEIPT,
                Constants::TRAVEL_TOLL_ROAD_RECEIPT,
                Constants::TRAVEL_TAXI_RECEIPT,
                Constants::TRAVEL_PARKING_RECEIPT,
            ];

            $typeId            = Dynamic::getInstance()->getTypeId();
            $categoryOfReceipt = UserField::getUfListValueById($item->get('UF_CRM_'.$typeId.'_CATEGORY_OF_RECEIPT'));
            $isCreator         = (int)$item->getCreatedBy() === $this->userId;
            $isAssigned        = (int)$item->getAssignedById() === $this->userId;
            $isReceipt         = (int)$item->getCategoryId() === Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_DEFAULT_CODE
            );
            $isTYB             = (int)$item->getCategoryId() === Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE
            );

            if ($this->hasUserITPermissions())
            {
                return $isCreator || $isAssigned || in_array($categoryOfReceipt, $itCategories);
            }

            if ($this->hasUserTravelPermissions())
            {
                return $isCreator || $isAssigned || in_array($categoryOfReceipt, $travelCategories);
            }

            if ($this->hasUserFinancePermissions())
            {
                return $isCreator || $isAssigned || $isReceipt;
            }

            if ($this->hasUserPayrollPermissions())
            {
                return $isCreator || $isAssigned || $isTYB;
            }

            return $isCreator;
        }

        return parent::checkReadPermissions($entityTypeId, $id, $categoryId);
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
        if (Container::getInstance()->getRouter()->isListPage())
        {
            return false;
        }

        if ($this->isAdmin())
        {
            return true;
        }

        if ($this->hasUserAnyPermissionsForExpense())
        {
            return true;
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
    public function canUserChangeStage(Item $item): bool
    {
        if ($this->isAdmin())
        {
            return true;
        }

        if ($this->hasUserAnyPermissionsForExpense())
        {
            return true;
        }

        if (Dynamic::getInstance()->isItemInFirstStage($item, true)
            && Dynamic::getInstance()->isItemInSubmittedStage($item)
        ){
            return true;
        }

        if (Dynamic::getInstance()->isItemInRejectStage($item, true)
            && Dynamic::getInstance()->isItemInSubmittedStage($item)
        ){
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasUserITPermissions(): bool
    {
        return $this->userHasExpensesITRights;
    }

    /**
     * @return bool
     */
    public function hasUserTravelPermissions(): bool
    {
        return $this->userHasExpensesTravelRights;
    }

    /**
     * @return bool
     */
    public function hasUserFinancePermissions(): bool
    {
        return $this->userHasExpensesFinanceRights;
    }

    /**
     * @return bool
     */
    public function hasUserPayrollPermissions(): bool
    {
        return $this->userHasExpensesPayrollRights;
    }

    /**
     * @return bool
     */
    public function hasUserHrPermissions(): bool
    {
        return $this->userHasHrRights;
    }

    /**
     * @return bool
     */
    public function hasUserAnyPermissionsForExpense(): bool
    {
        return ($this->isAdmin()
            || $this->userHasExpensesITRights
            || $this->userHasExpensesTravelRights
            || $this->userHasExpensesFinanceRights
            || $this->userHasExpensesPayrollRights);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     * @throws \Exception
     */
    public function canUserSplitAmount(Item $item): bool
    {
        if ($this->hasUserAnyPermissionsForExpense())
        {
            return !(
                Dynamic::getInstance()->isItemInFirstStage($item, true)
                || Dynamic::getInstance()->isItemInApprovedStage($item)
                || Dynamic::getInstance()->isItemInFinalStage($item)
            );
        }
        return false;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     * @throws \Exception
     */
    public function canUserRejectRequest(Item $item): bool
    {
        if ($this->hasUserAnyPermissionsForExpense())
        {
            return !(
                Dynamic::getInstance()->isItemInFirstStage($item, true)
                || Dynamic::getInstance()->isItemInApprovedStage($item)
                || Dynamic::getInstance()->isItemInFinalStage($item)
            );
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canUserUploadRatingFile(): bool
    {
        return ($this->hasUserHrPermissions() || $this->isAdmin());
    }
}