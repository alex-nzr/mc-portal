<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ExternalParticipantsProvider.php
 * 30.01.2023 12:37
 * ==================================================
 */


namespace Cbit\Mc\Expense\Service\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Cbit\Mc\Expense\Internals\Debug\Logger;
use Cbit\Mc\Expense\Internals\Model\ExternalParticipantsTable;
use Throwable;

/**
 * @class ExternalParticipantsProvider
 * @package Cbit\Mc\Expense\Service\Integration\UI\EntitySelector
 */
class ExternalParticipantsProvider extends BaseProvider
{
    const ENTITY_ID = 'external-participant';
    const ENTITY_TYPE = 'expense-external-participant';
    const COUNT_LIMIT = 30;

    public function __construct()
    {
        parent::__construct();
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
     * @return \Bitrix\UI\EntitySelector\Item[]
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
        $query = ExternalParticipantsTable::query()
            ->setSelect(['*'])
            ->setLimit(self::COUNT_LIMIT);
        if (!empty($ids))
        {
            $query->where('ID', $ids);
        }

        return $query->fetchAll();
    }

    /**
     * @param string $searchString
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getItemsBySearchString(string $searchString): array
    {
        return ExternalParticipantsTable::query()
            ->setSelect(['*'])
            ->setFilter([
                [
                    'LOGIC' => 'OR',
                    ['%NAME' => $searchString],
                    ['%LAST_NAME' => $searchString],
                    ['%SECOND_NAME' => $searchString],
                ]
            ])
            ->fetchAll();
    }

    /**
     * @param array $item
     * @return \Bitrix\UI\EntitySelector\Item
     */
    private function makeItem(array $item): Item
    {
        $uiItem = new Item([
            'id' => $item['ID'],
            'entityId' => static::ENTITY_ID,
            'entityType' => static::ENTITY_TYPE,
            'title' => $this->formatUserName($item),
            'customData' => [
                'position' => $item['POSITION'],
                'company' => $item['COMPANY'],
            ]
        ]);

        $uiItem->setBadges([
            [
                'title' => $item['COMPANY'],
                'textColor' => '#000',
                'bgColor' => 'lightgrey',
            ],
            [
                'title' => $item['POSITION'],
                'textColor' => '#000',
                'bgColor' => 'lightgrey',
            ],
        ]);

        return $uiItem;
    }

    /**
     * @param array $item
     * @return string
     */
    private function formatUserName(array $item): string
    {
        if (!empty($item['SECOND_NAME']))
        {
            return $item['NAME'] . " " . $item['SECOND_NAME'] . " " . $item['LAST_NAME'];
        }

        return $item['NAME'] . " " . $item['LAST_NAME'];
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