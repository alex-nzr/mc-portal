<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ItemDataProvider.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Staffing\Filter;

use Bitrix\Crm\Filter\ItemDataProvider as CrmItemDataProvider;
use Cbit\Mc\Staffing\Entity\Dynamic;

/**
 * Class ItemDataProvider
 * @package Cbit\Mc\Staffing\Filter
 */
class ItemDataProvider extends CrmItemDataProvider
{
    /**
     * @param array $filter
     * @param array $requestFilter
     * @return void
     * @throws \Exception
     */
    public function prepareListFilter(array &$filter, array $requestFilter): void
    {
        parent::prepareListFilter($filter, $requestFilter);
        $filter["UF_CRM_" . Dynamic::getInstance()->getTypeId() . "_ALLOW_STAFFING"] = true;
    }
}