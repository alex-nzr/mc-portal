<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ChargeCodeProvider.php
 * 01.02.2023 12:52
 * ==================================================
 */


namespace Cbit\Mc\Expense\Service\Integration\UI\EntitySelector;

use Bitrix\Crm;
use Bitrix\Crm\Service\Factory;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Cbit\Mc\Expense\Config\Configuration;
use Cbit\Mc\Expense\Internals\Debug\Logger;
use Cbit\Mc\Expense\Service\Container;
use Throwable;

/**
 * @class ChargeCodeProvider
 * @package Cbit\Mc\Expense\Service\Integration\UI\EntitySelector
 */
class ChargeCodeProvider extends BaseProvider
{
    const ENTITY_ID = 'expense-cc';
    const ENTITY_TYPE = 'expense-charge-code';
    const COUNT_LIMIT = 30;

    private ?Factory $staffingFactory;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $staffingEntityTypeId = Configuration::getInstance()->getStaffingEntityTypeId();
        $this->staffingFactory = $staffingEntityTypeId ? Container::getInstance()->getFactory($staffingEntityTypeId) : null;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @param array $ids
     * @return array|\Bitrix\UI\EntitySelector\Item[]
     * @throws \Exception
     */
    public function getItems(array $ids = []): array
    {
        $items = [];

        foreach ($this->getItemsByIds($ids) as $item)
        {
            $items[] = $this->makeItem($item);
        }

        return $items;
    }

    /**
     * @param array $ids
     * @return array
     * @throws \Exception
     */
    private function getItemsByIds(array $ids = []): array
    {
        if ($this->staffingFactory !== null)
        {
            $query = $this->getDefaultQuery();

            if (!empty($ids))
            {
                $query->where(Crm\Item::FIELD_NAME_ID, $ids);
            }

            return $query->fetchAll();
        }

        return [];
    }

    /**
     * @param string $searchString
     * @return array
     * @throws \Exception
     */
    private function getItemsBySearchString(string $searchString): array
    {
        if ($this->staffingFactory !== null)
        {
            return $this->getDefaultQuery()
                    ->whereLike(Crm\Item::FIELD_NAME_TITLE, $searchString)
                    ->fetchAll();
        }

        return [];
    }

    /**
     * @return \Bitrix\Main\ORM\Query\Query|null
     * @throws \Exception
     */
    private function getDefaultQuery(): ?Query
    {
        if ($this->staffingFactory !== null)
        {
            $staffingTypeId = Configuration::getInstance()->getStaffingTypeId();
            return $this->staffingFactory->getDataClass()::query()
                ->setSelect([Crm\Item::FIELD_NAME_ID, Crm\Item::FIELD_NAME_TITLE])
                ->setLimit(self::COUNT_LIMIT)
                ->where('UF_CRM_'.$staffingTypeId.'_ALLOW_EXPENSE', true);
        }
        return null;
    }

    /**
     * @param array $item
     * @return \Bitrix\UI\EntitySelector\Item
     */
    private function makeItem(array $item): Item
    {
        return new Item([
            'id' => $item['ID'],
            'entityId' => static::ENTITY_ID,
            'entityType' => static::ENTITY_TYPE,
            'title' => $item['TITLE'],
            'customData' => []
        ]);
    }
    /**
     * @throws \Exception
     */
    public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
    {
        try {
            $items = $this->getItemsBySearchString($searchQuery->getQuery());

            if (!empty($items))
            {
                foreach ($items as $item)
                {
                    $dialog->addItem(
                        $this->makeItem($item)
                    );
                }
            }
        }
        catch(Throwable $e)
        {
            Logger::printToFile(
                date('d.m.Y H:i:s') . " " . self::class . " " . $e->getMessage()
            );
        }
    }

    /**
     * @param \Bitrix\UI\EntitySelector\Dialog $dialog
     * @return void
     * @throws \Exception
     */
    public function fillDialog(Dialog $dialog): void
    {
        $dialog->loadPreselectedItems();

        if ($dialog->getItemCollection()->count() > 0)
        {
            foreach ($dialog->getItemCollection() as $item)
            {
                $dialog->addRecentItem($item);
            }
        }

        $recentItemsCount = count($dialog->getRecentItems()->getEntityItems(self::ENTITY_ID));

        if ($recentItemsCount < self::COUNT_LIMIT)
        {
            foreach ($this->getItems() as $item)
            {
                $dialog->addRecentItem($item);
            }
        }
    }
}