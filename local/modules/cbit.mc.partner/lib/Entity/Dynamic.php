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

namespace Cbit\Mc\Partner\Entity;

use Bitrix\Crm\Model\Dynamic\TypeTable;
use Bitrix\Crm\Service\Factory;
use Bitrix\Main\Config\Option;
use Bitrix\Crm\Model\Dynamic\Type;
use Cbit\Mc\Partner\Config\Constants;
use Cbit\Mc\Partner\Internals\Control\ServiceManager;
use Cbit\Mc\Partner\Service\Container;
use CCrmStatus;

/**
 * Class Dynamic
 * @package Cbit\Mc\Partner\Entity
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
     * @return \Cbit\Mc\Partner\Entity\Dynamic
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
     * @return void
     * @throws \Exception
     */
    protected function fillCategories(): void
    {
        $categories = $this->getItemFactory()->getCategories();
        if (!empty($categories))
        {
            foreach ($categories as $category)
            {
                $id   = $category->getId();
                $code = $category->getCode();
                if (empty($code) && ($category->getIsDefault() === true))
                {
                    $code = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
                }
                $this->customCategories[$id] = $code;
            }
        }
    }

    private function __clone() {}
    public  function __wakeup() {}
}