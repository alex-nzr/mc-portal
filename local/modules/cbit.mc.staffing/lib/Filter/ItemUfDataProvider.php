<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ItemUfDataProvider.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Staffing\Filter;

use Bitrix\Crm\Filter\ItemSettings;
use Bitrix\Crm\Filter\ItemUfDataProvider as CrmItemUfDataProvider;
use Cbit\Mc\Staffing\Entity;

/**
 * Class ItemUfDataProvider
 * @package Cbit\Mc\Staffing\Filter
 */
class ItemUfDataProvider extends CrmItemUfDataProvider
{
    protected int $typeId;

    /**
     * ItemUfDataProvider constructor.
     * @param \Bitrix\Crm\Filter\ItemSettings $settings
     * @throws \Exception
     */
    public function __construct(ItemSettings $settings)
    {
        $this->typeId = Entity\Dynamic::getInstance()->getTypeId();
        parent::__construct($settings);
    }

    /**
     * @param array $filter
     * @param array $filterFields
     * @param array $requestFilter
     */
    public function prepareListFilter(array &$filter, array $filterFields, array $requestFilter)
    {
        parent::prepareListFilter($filter, $filterFields, $requestFilter);

        $userFields = $this->getUserFields();
        foreach($filterFields as $filterField)
        {
            $id = $filterField['id'];
            if (isset($userFields[$id]))
            {
                if (isset($filterField['type']))
                {
                    if (isset($requestFilter[$id]))
                    {
                        if ($filterField['type'] === 'string' || $filterField['type'] === 'text')
                        {
                            unset($filter[$id]);
                            $filter["%".$id] = $requestFilter[$id];
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $userField
     * @return array
     */
    protected function getGridColumn(array $userField): array
    {
        return [
            'id'      => $userField['FIELD_NAME'],
            'name'    => $this->getFieldName($userField),
            'default' => ($userField['SHOW_FILTER'] !== 'N'),
            'sort'    => $userField['FIELD_NAME'],
        ];
    }
}