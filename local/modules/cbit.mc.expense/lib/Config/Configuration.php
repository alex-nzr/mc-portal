<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 17.01.2023 12:00
 * ==================================================
 */
namespace Cbit\Mc\Expense\Config;

use Bitrix\Crm\Item;
use Bitrix\Main\Config\Option;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\Integration\Intranet\CustomSectionProvider;

/**
 * Class Configuration
 * @package Cbit\Mc\Expense\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Cbit\Mc\Expense\Config\Configuration
     */
    public static function getInstance(): Configuration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return '/local/logs/'.ServiceManager::getModuleId().'-log.txt';
    }

    /**
     * @return \string[][]
     */
    public function getCustomPagesMap(): array
    {
        return [
            /*Constants::CUSTOM_PAGE_EXAMPLE => [
                'TITLE'     => 'Some custom page',
                'COMPONENT' => 'vendor:project.dynamic.example-component',
            ],*/
            Constants::CUSTOM_PAGE_LIST => [
                'TITLE'     => 'Expenses',
                'COMPONENT' => CustomSectionProvider::DEFAULT_LIST_COMPONENT,
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    public function getTypeOfRequestToCategoryRelationsMap(): array
    {
        return [
            Constants::DYNAMIC_CATEGORY_DEFAULT_CODE         => Constants::TYPE_OF_REQUEST_EXPENSE,
            Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE => Constants::TYPE_OF_REQUEST_TRIP,
            Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE  => Constants::TYPE_OF_REQUEST_TYB,
        ];
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getStaffingEntityTypeId(): ?int
    {
        $typeId = $this->getStaffingTypeId();
        if ($typeId > 0)
        {
            /** @var  \Bitrix\Crm\Model\Dynamic\Type|null $typeObject */
            $typeObject = Container::getInstance()->getDynamicTypeDataClass()::getByPrimary($typeId)->fetchObject();
            if (!empty($typeObject))
            {
                return $typeObject->getEntityTypeId();
            }
        }
        return null;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getStaffingTypeId(): int
    {
        return (int)Option::get(
            Constants::STAFFING_MODULE_ID, Constants::OPTION_KEY_STAFFING_TYPE_ID
        );
    }

    /**
     * @param int $categoryId
     * @return string
     * @throws \Exception
     */
    public function getTypeOfRequestByCategoryId(int $categoryId): string
    {
        $categoryCode  = Dynamic::getInstance()->getCategoryCodeById($categoryId);
        $typesMap      = $this->getTypeOfRequestToCategoryRelationsMap();
        return array_key_exists($categoryCode, $typesMap) ? $typesMap[$categoryCode] : '';
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getDefaultTYBChargeCode(): ?int
    {
        $staffingFactory = Container::getInstance()->getFactory($this->getStaffingEntityTypeId());
        if ($staffingFactory)
        {
            $ccTitle = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_DEFAULT_TYB_CC);
            if (empty($ccTitle))
            {
                $ccTitle = Constants::OPTION_TYB_CC_DEFAULT_VALUE;
            }
            $staffingDataClass = $staffingFactory->getDataClass();
            $item = $staffingDataClass::query()
                ->setSelect(['ID'])
                ->where(Item::FIELD_NAME_TITLE, $ccTitle)
                ->fetch();

            if (is_array($item))
            {
                return (int)$item['ID'];
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function getTypeIdFromOption(): int
    {
        return (int)Option::get(
            ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID
        );
    }

    private function __clone(){}
    public function __wakeup(){}
}