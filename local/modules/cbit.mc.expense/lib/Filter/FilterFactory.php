<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - FilterFactory.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Expense\Filter;

use Bitrix\Crm\Filter\Factory;
use Bitrix\Crm\Filter\Filter as CrmFilter;
use Bitrix\Crm\Filter\ItemSettings;
use Bitrix\Main\Filter\DataProvider;
use Bitrix\Main\Filter\EntitySettings;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Entity;

/**
 * Class FilterFactory
 * @package Cbit\Mc\Expense\Filter
 */
class FilterFactory extends Factory
{
    /**
     * @param \Bitrix\Main\Filter\EntitySettings $settings
     * @return \Bitrix\Main\Filter\DataProvider
     * @throws \Exception
     */
    public function getDataProvider(EntitySettings $settings): DataProvider
    {
        if ($settings instanceof ItemSettings)
        {
            $entityTypeId = $settings->getType()->getEntityTypeId();
            $tenderEntityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();
            if ($entityTypeId === $tenderEntityTypeId)
            {
                $factory = Container::getInstance()->getFactory($entityTypeId);
                if ($factory)
                {
                    return new ItemDataProvider($settings, $factory);
                }
            }
        }

        return parent::getDataProvider($settings);
    }

    /**
     * @param \Bitrix\Main\Filter\EntitySettings $settings
     * @return \Bitrix\Main\Filter\DataProvider
     * @throws \Exception
     */
    public function getUserFieldDataProvider(EntitySettings $settings): DataProvider
    {
        if ($settings instanceof ItemSettings)
        {
            $entityTypeId = $settings->getType()->getEntityTypeId();
            $tenderEntityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();
            if ($entityTypeId === $tenderEntityTypeId)
            {
                return new ItemUfDataProvider($settings);
            }
        }

        return parent::getUserFieldDataProvider($settings);
    }

    public function createFilter(
        $ID, DataProvider $entityDataProvider, array $extraDataProviders = null, array $params = null
    ): CrmFilter
    {
        if ($entityDataProvider instanceof ItemDataProvider)
        {
            return new Filter($ID, $entityDataProvider, (array)$extraDataProviders, (array)$params);
        }

        return parent::createFilter($ID, $entityDataProvider, (array)$extraDataProviders, (array)$params);
    }
}