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
namespace Cbit\Mc\RI\Filter;

use Bitrix\Crm\Filter\ItemDataProvider as CrmItemDataProvider;
use Bitrix\Crm\Item;
use Bitrix\Main\Engine\CurrentUser;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Access\FieldAccess;
use Cbit\Mc\RI\Service\Access\Permission;
use Cbit\Mc\RI\Service\Container;
use Cbit\Mc\RI\Service\Integration\Staffing\Employment;

/**
 * Class ItemDataProvider
 * @package Cbit\Mc\RI\Filter
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

        if (!empty($GLOBALS['USER']))
        {
            if (!Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForRi())
            {
                $currentUserId = (int)CurrentUser::get()->getId();

                $typeId = Dynamic::getInstance()->getTypeId();
                $userProjects = Employment::getCurrentUserProjectsIds($currentUserId);
                if (!empty($userProjects))
                {
                    //юзер видит свои реквесты и реквесты по проектам, на которых он в данный момент застафлен
                    $filter[] = [
                        'LOGIC' => 'OR',
                        ['=UF_CRM_'.$typeId.'_CHARGE_CODE' => $userProjects],
                        ['=CREATED_BY' => $currentUserId]
                    ];
                }
                else
                {
                    $filter['=CREATED_BY'] = $currentUserId;
                }
            }
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getGridColumns(): array
    {
        return array_filter(parent::getGridColumns(), function($item) use($typeId){
            return !in_array($item['id'] , FieldAccess::getGridHiddenFields());
        });
    }

    /**
     * @return array|\Bitrix\Crm\Filter\Field[]
     * @throws \Exception
     */
    public function prepareFields(): array
    {
        return array_filter(parent::prepareFields(), function($field){
            if ($field->getId() === Item::FIELD_NAME_ASSIGNED)
            {
                return true;
            }

            return !in_array($field->getId(), FieldAccess::getGridHiddenFields());
        });
    }
}