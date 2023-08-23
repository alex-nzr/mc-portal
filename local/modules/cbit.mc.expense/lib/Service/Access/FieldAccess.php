<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - FieldAccess.php
 * 18.01.2023 14:48
 * ==================================================
 */


namespace Cbit\Mc\Expense\Service\Access;

use Bitrix\Crm\Item;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Cbit\Mc\Expense\Config\Configuration;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Container;

/**
 * @class FieldAccess
 * @package Cbit\Mc\Expense\Service\Access
 */
class FieldAccess
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getGridVisibleFields(): array
    {
        $typeId = Configuration::getInstance()->getTypeIdFromOption();
        $fields =  [
            Item::FIELD_NAME_ID,
            Item::FIELD_NAME_CREATED_TIME,
            //Item::FIELD_NAME_TITLE,
            Item::FIELD_NAME_CREATED_BY,
            Item::FIELD_NAME_ASSIGNED,
            Item::FIELD_NAME_ID,
            Item::FIELD_NAME_OPPORTUNITY,
            Item::FIELD_NAME_CURRENCY_ID,
            Item::FIELD_NAME_STAGE_ID,
            'UF_CRM_' . $typeId . '_CHARGE_CODE',
            'UF_CRM_' . $typeId . '_APPROVAL_DATE',
            'UF_CRM_' . $typeId . '_COMMENT',
            'UF_CRM_' . $typeId . '_DUPLICATE_OF',
            'UF_CRM_' . $typeId . '_CATEGORY_OF_RECEIPT',
            'UF_CRM_' . $typeId . '_CATEGORY_OF_TR_YOUR_BUDGET',
            'UF_CRM_' . $typeId . '_EXPENSE_DATE',
            'UF_CRM_' . $typeId . '_AMOUNT_REJECTED',
            'UF_CRM_' . $typeId . '_REASON',
            'UF_CRM_' . $typeId . '_PSSS',
        ];

        if (Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForExpense())
        {
            $fields[] = 'UF_CRM_' . $typeId . '_REQUESTER_FMNO';
        }

        return $fields;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getFilterAvailableFields(): array
    {
        $typeId = Configuration::getInstance()->getTypeIdFromOption();
        $fields = [
            Item::FIELD_NAME_ID,
            Item::FIELD_NAME_CREATED_TIME,
            Item::FIELD_NAME_CREATED_BY,
            Item::FIELD_NAME_ASSIGNED,
            Item::FIELD_NAME_ID,
            Item::FIELD_NAME_OPPORTUNITY,
            Item::FIELD_NAME_CURRENCY_ID,
            Item::FIELD_NAME_STAGE_ID,
            Item::FIELD_NAME_STAGE_SEMANTIC_ID,
            Item::FIELD_NAME_CATEGORY_ID,
            'UF_CRM_' . $typeId . '_CHARGE_CODE',
            'UF_CRM_' . $typeId . '_APPROVAL_DATE',
            'UF_CRM_' . $typeId . '_COMMENT',
            'UF_CRM_' . $typeId . '_DUPLICATE_OF',
            'UF_CRM_' . $typeId . '_CATEGORY_OF_RECEIPT',
            'UF_CRM_' . $typeId . '_CATEGORY_OF_TR_YOUR_BUDGET',
            'UF_CRM_' . $typeId . '_EXPENSE_DATE',
            'UF_CRM_' . $typeId . '_AMOUNT_REJECTED',
            'UF_CRM_' . $typeId . '_REASON',
            'UF_CRM_' . $typeId . '_PSSS',
        ];

        if (Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForExpense())
        {
            $fields[] = 'UF_CRM_' . $typeId . '_REQUESTER_FMNO';
        }

        return $fields;
    }
}