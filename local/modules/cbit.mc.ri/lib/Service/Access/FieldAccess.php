<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - FieldAccess.php
 * 10.01.2023 18:20
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Access;

use Bitrix\Crm\Item;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Container;

/**
 * @class FieldAccess
 * @package Cbit\Mc\RI\Service\Access
 */
class FieldAccess
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getGridHiddenFields(): array
    {
        $typeId = Dynamic::getInstance()->getTypeId();
        $fields = [Item::FIELD_NAME_ASSIGNED];
        if (Container::getInstance()->getUserPermissions()->hasUserRiAnalystPermissions()
            && !Container::getInstance()->getUserPermissions()->hasUserRiManagerPermissions()
        ){
            $fields[] = 'UF_CRM_'.$typeId.'_PER_DIEM';
        }

        if (!Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForRi())
        {
            $fields[] = 'UF_CRM_'.$typeId.'_PER_DIEM';
            $fields[] = 'UF_CRM_'.$typeId.'_LABOR_COSTS_PLAN';
            $fields[] = 'UF_CRM_'.$typeId.'_LABOR_COSTS_FACT';
        }

        return array_unique($fields);
    }
}