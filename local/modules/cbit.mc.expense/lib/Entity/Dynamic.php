<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Dynamic.php
 * 17.01.2023 12:17
 * ==================================================
 */

namespace Cbit\Mc\Expense\Entity;

use Bitrix\Crm\Item;
use Bitrix\Crm\Model\Dynamic\TypeTable;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Crm\Service\Factory;
use Bitrix\Main\Config\Option;
use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Container;
use CCrmStatus;

/**
 * Class Dynamic
 * @package Cbit\Mc\Expense\Entity
 */
class Dynamic
{
    private string|TypeTable $typeDataClass;
    private ?Type $typeObject;
    private int   $entityTypeId;
    private int   $typeId;
    private array $customCategories = [];

    private static ?Dynamic $instance = null;

    /**
     * Dynamic constructor.
     * @param int $typeId
     * @throws \Exception
     */
    private function __construct(int $typeId)
    {
        $this->typeId        = $typeId;
        $this->typeDataClass = Container::getInstance()->getDynamicTypeDataClass();
        $this->setTypeObject();
        $this->setEntityTypeId();
    }

    /**
     * @return \Cbit\Mc\Expense\Entity\Dynamic
     * @throws \Exception
     */
    public static function getInstance(): Dynamic
    {
        if(static::$instance === null)
        {
            $typeId = (int)Option::get(
                ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID
            );
            static::$instance = new static($typeId);
        }
        return static::$instance;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @return string
     */
    public function getTypeCode(): string
    {
        return Constants::DYNAMIC_TYPE_CODE;
    }

    /**
     * @return int
     */
    public function getEntityTypeId(): int
    {
        return $this->entityTypeId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getDefaultCategoryId(): int
    {
        $category = $this->getItemFactory()->getDefaultCategory();
        return !empty($category) ? (int)$category->getId() : 0;
    }

    /**
     * @return \Bitrix\Crm\Service\Factory|null
     * @throws \Exception
     */
    public function getItemFactory(): ?Factory
    {
        return Container::getInstance()->getFactory($this->entityTypeId);
    }

    /**
     * @param int $categoryId
     * @return string
     */
    public function getStatusPrefix(int $categoryId): string
    {
        return CCrmStatus::getDynamicEntityStatusPrefix($this->entityTypeId, $categoryId) . ":";
    }

    /**
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function add(array $fields): Result
    {
        $item = $this->getItemFactory()->createItem();

        foreach ($fields as $field => $value) {
            $item->set($field, $value);
        }

        $saveOperation = $this->getItemFactory()->getAddOperation($item);
        $res = $saveOperation->disableCheckAccess()->launch();
        if ($res->isSuccess())
        {
            $res->setData(['ID' => $item->getId()]);
        }
        return $res;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function update(Item $item, array $fields): Result
    {
        foreach ($fields as $field => $value) {
            $item->set($field, $value);
        }
        $updateOperation = $this->getItemFactory()->getUpdateOperation($item);
        $res = $updateOperation->disableAllChecks()->launch();
        if ($res->isSuccess())
        {
            $res->setData(['ID' => $item->getId()]);
        }
        return $res;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function delete(Item $item): Result
    {
        $deleteOperation = $this->getItemFactory()->getDeleteOperation($item);
        return $deleteOperation->launch();
    }

    /**
     * @param array $select
     * @param array $filter
     * @param bool $checkPermissions
     * @return \Bitrix\Crm\Item[]
     * @throws \Exception
     */
    public function select(array $select = [], array $filter = [], bool $checkPermissions = false): array
    {
        $params = [
            'order'  => ['ID' => 'ASC'],
            'select' => count($select) > 0 ? $select : ['ID'],
            'filter' => count($filter) > 0 ? $filter : [],
        ];

        if ($checkPermissions)
        {
            return $this->getItemFactory()->getItemsFilteredByPermissions($params);
        }
        else
        {
            return $this->getItemFactory()->getItems($params);
        }
    }

    /**
     * @param int $entityID
     * @return \Bitrix\Crm\Item|null
     * @throws \Exception
     */
    public function getById(int $entityID): ?Item
    {
        $items = $this->select(['*', 'UF_*'], ['=ID' => $entityID]);
        if (!empty($items))
        {
            return current($items);
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    private function setTypeObject(): void
    {
        $this->typeObject = $this->typeDataClass::getByPrimary($this->typeId)->fetchObject();
    }

    /**
     * @return void
     */
    private function setEntityTypeId(): void
    {
        $this->entityTypeId = !empty($this->typeObject) ? (int)$this->typeObject->getEntityTypeId() : 0;
    }

    /**
     * @param int $categoryId
     * @return string
     * @throws \Exception
     */
    public function getCategoryCodeById(int $categoryId): string
    {
        if (!array_key_exists($categoryId, $this->customCategories))
        {
            $this->fillCategories();
        }
        return (string)$this->customCategories[$categoryId];
    }

    /**
     * @param string $categoryCode
     * @return int
     * @throws \Exception
     */
    public function getCategoryIdByCode(string $categoryCode): int
    {
        if (!in_array($categoryCode, $this->customCategories))
        {
            $this->fillCategories();
        }
        return (int)array_search($categoryCode, $this->customCategories);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function fillCategories(): void
    {
        $categories = ItemCategoryTable::query()
            ->setSelect(['ID', 'CODE', 'IS_DEFAULT'])
            ->setFilter(['=ENTITY_TYPE_ID' => $this->entityTypeId])
            ->fetchAll();
        if (!empty($categories))
        {
            foreach ($categories as $category)
            {
                $id   = (int)$category['ID'];
                $code = $category['CODE'];
                if (empty($code) && ($id === $this->getDefaultCategoryId()))
                {
                    $code = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
                }
                $this->customCategories[$id] = $code;
            }
        }
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param bool $useActualValue
     * @return bool
     * @throws \Exception
     */
    public function isItemInFirstStage(Item $item, bool $useActualValue): bool
    {
        if ($item->isChangedStageId())
        {
            $stageId = $useActualValue ? $item->remindActual(Item::FIELD_NAME_STAGE_ID) : $item->getStageId();
        }
        else
        {
            $stageId = $item->getStageId();
        }

        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = str_replace($stagePrefix, '', $stageId);
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_NEW,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_NEW,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_NEW,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     */
    public function isItemInSubmittedStage(Item $item): bool
    {
        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = substr($item->getStageId(), strlen($stagePrefix));
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_SUBMITTED,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_SUBMITTED,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_SUBMITTED,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param bool $useActualValue
     * @return bool
     * @throws \Exception
     */
    public function isItemInRejectStage(Item $item, bool $useActualValue):bool
    {
        if ($item->isChangedStageId())
        {
            $stageId = $useActualValue ? $item->remindActual(Item::FIELD_NAME_STAGE_ID) : $item->getStageId();
        }
        else
        {
            $stageId = $item->getStageId();
        }

        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = str_replace($stagePrefix, '', $stageId);
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_REJECTED,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_REJECTED,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_REJECTED,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     */
    public function isItemInApprovedStage(Item $item):bool
    {
        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = substr($item->getStageId(), strlen($stagePrefix));
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_APPROVED,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_APPROVED,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_APPROVED,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     */
    public function isItemInFinalStage(Item $item):bool
    {
        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = substr($item->getStageId(), strlen($stagePrefix));
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_SUCCESS,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_SUCCESS,
            Constants::DYNAMIC_STAGE_DEFAULT_FAIL,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_FAIL,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_FAIL,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     */
    public function isItemInSuccessStage(Item $item):bool
    {
        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = substr($item->getStageId(), strlen($stagePrefix));
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_SUCCESS,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_SUCCESS,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     */
    public function isItemInFailStage(Item $item):bool
    {
        $stagePrefix = $this->getStatusPrefix($item->getCategoryId());
        $stageCode   = substr($item->getStageId(), strlen($stagePrefix));
        return in_array($stageCode, [
            Constants::DYNAMIC_STAGE_DEFAULT_FAIL,
            Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_FAIL,
            Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_FAIL,
        ]);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function moveItemToRejectStage(Item $item): Result
    {
        $categoryId   = $item->getCategoryId();
        $categoryCode = $this->getCategoryCodeById($categoryId);
        $stagePrefix  = $this->getStatusPrefix($categoryId);
        $stageCode    = null;
        switch ($categoryCode)
        {
            case Constants::DYNAMIC_CATEGORY_DEFAULT_CODE:
                $stageCode = Constants::DYNAMIC_STAGE_DEFAULT_REJECTED;
                break;
            case Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE:
                $stageCode = Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_REJECTED;
                break;
            case Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE:
                $stageCode = Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_REJECTED;
                break;
        }

        if ($stageCode !== null)
        {
            $item->set('UF_CRM_'.$this->getTypeId().'_REJECT_COMMENT_ADDED', true);
            $item->setStageId($stagePrefix . $stageCode);
            return $this->getItemFactory()->getUpdateOperation($item)->launch();
        }
        else
        {
            return (new Result)->addError(
                new Error("Can not find relevant stage by categoryId '$categoryId', categoryCode '$categoryCode'")
            );
        }
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function moveItemToReviewStage(Item $item): Result
    {
        $categoryId   = $item->getCategoryId();
        $categoryCode = $this->getCategoryCodeById($categoryId);
        $stagePrefix  = $this->getStatusPrefix($categoryId);
        $stageCode    = null;
        switch ($categoryCode)
        {
            case Constants::DYNAMIC_CATEGORY_DEFAULT_CODE:
                $stageCode = Constants::DYNAMIC_STAGE_DEFAULT_UNDER_REVIEW;
                break;
            case Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE:
                $stageCode = Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_UNDER_REVIEW;
                break;
            case Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE:
                $stageCode = Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_UNDER_REVIEW;
                break;
        }

        if ($stageCode !== null)
        {
            $item->setStageId($stagePrefix . $stageCode);
            return $this->getItemFactory()->getUpdateOperation($item)->launch();
        }
        else
        {
            return (new Result)->addError(
                new Error(__METHOD__." Can not find relevant stage by categoryId '$categoryId', categoryCode '$categoryCode'")
            );
        }
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return bool
     * @throws \Exception
     */
    public function isItemOpenedByAssigned(Item $item): bool
    {
        return (Container::getInstance()->getContext()->getUserId() === (int)$item->getAssignedById());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDraftStages(): array
    {
        $res = [];
        foreach ($this->getItemFactory()?->getCategories() as $category)
        {
            $res[] = $this->getStatusPrefix($category->getId()) . 'NEW';
        }
        return $res;
    }

    private function __clone() {}
    public  function __wakeup() {}
}