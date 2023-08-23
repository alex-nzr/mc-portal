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
namespace Cbit\Mc\Expense\Filter;

use Bitrix\Crm\Filter\ItemDataProvider as CrmItemDataProvider;
use Bitrix\Crm\Item;
use Bitrix\Main\Engine\CurrentUser;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Service\Access\FieldAccess;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Service\Container;

/**
 * Class ItemDataProvider
 * @package Cbit\Mc\Expense\Filter
 */
class ItemDataProvider extends CrmItemDataProvider
{
    public const FIELD_STAGE_SEMANTIC = 'STAGE_SEMANTIC_ID';

    /**
     * @param array $filter
     * @param array $requestFilter
     * @return void
     * @throws \Exception
     */
    public function prepareListFilter(array &$filter, array $requestFilter): void
    {
        parent::prepareListFilter($filter, $requestFilter);

        if (array_key_exists(Item::FIELD_NAME_CATEGORY_ID, $requestFilter)
            && !empty($requestFilter[Item::FIELD_NAME_CATEGORY_ID])
        ){
            $filter['='.Item::FIELD_NAME_CATEGORY_ID] = $requestFilter[Item::FIELD_NAME_CATEGORY_ID];
        }

        if (!empty($GLOBALS['USER']))
        {
            $perms = Container::getInstance()->getUserPermissions();
            $currentUserId = (int)CurrentUser::get()->getId();
            if (!CurrentUser::get()->isAdmin())
            {
                if ($perms->hasUserAnyPermissionsForExpense())
                {
                    $typeId             = Dynamic::getInstance()->getTypeId();
                    $catOfReceiptUfCode = 'UF_CRM_'.$typeId.'_CATEGORY_OF_RECEIPT';
                    $draftStages        = Dynamic::getInstance()->getDraftStages();
                    $prefilter = [
                        'LOGIC' => 'OR',
                        ['='.Item::FIELD_NAME_CREATED_BY => $currentUserId],
                        [
                            '='.Item::FIELD_NAME_ASSIGNED  => $currentUserId,
                            '!='.Item::FIELD_NAME_STAGE_ID => $draftStages
                        ],
                    ];
                    if ($perms->hasUserITPermissions())
                    {
                        $prefilter[] = [
                            $catOfReceiptUfCode => [
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::IT_EQUIPMENT_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::IT_SOFT_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::IT_LICENSES_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::IT_HARDWARE_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::IT_CELLULAR_RECEIPT),
                            ],

                            '!='.Item::FIELD_NAME_STAGE_ID => $draftStages
                        ];
                    }

                    if ($perms->hasUserTravelPermissions())
                    {
                        $prefilter[] = [
                            $catOfReceiptUfCode => [
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::TRAVEL_LAUNDRY_GYM_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::TRAVEL_WEEKEND_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::TRAVEL_SO_TICKET_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::TRAVEL_TOLL_ROAD_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::TRAVEL_TAXI_RECEIPT),
                                UserField::getUfListIdByValue($catOfReceiptUfCode, Constants::TRAVEL_PARKING_RECEIPT),
                            ],

                            '!='.Item::FIELD_NAME_STAGE_ID => $draftStages
                        ];
                    }

                    if ($perms->hasUserFinancePermissions())
                    {
                        $prefilter[] = [
                            Item::FIELD_NAME_CATEGORY_ID => Dynamic::getInstance()->getCategoryIdByCode(
                                Constants::DYNAMIC_CATEGORY_DEFAULT_CODE
                            ),

                            '!='.Item::FIELD_NAME_STAGE_ID => $draftStages
                        ];
                    }

                    if ($perms->hasUserPayrollPermissions())
                    {
                        $prefilter[] = [
                            Item::FIELD_NAME_CATEGORY_ID => Dynamic::getInstance()->getCategoryIdByCode(
                                Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE
                            ),

                            '!='.Item::FIELD_NAME_STAGE_ID => $draftStages
                        ];
                    }

                    $filter[] = $prefilter;
                }
                else
                {
                    $filter['='.Item::FIELD_NAME_CREATED_BY] = $currentUserId;
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
        return array_filter(parent::getGridColumns(), function($item){
            return in_array($item['id'], FieldAccess::getGridVisibleFields());
        });
    }

    /**
     * @return array|\Bitrix\Crm\Filter\Field[]
     * @throws \Exception
     */
    public function prepareFields(): array
    {
        $fields = parent::prepareFields();
        $fields[Item::FIELD_NAME_CATEGORY_ID] = $this->createField(
            Item::FIELD_NAME_CATEGORY_ID,
            [
                'type' => 'list',
                'name' => "Type",
                'partial' => true,
            ]
        );
        return array_filter($fields, function($field){
            return in_array($field->getId(), FieldAccess::getFilterAvailableFields());
        });
    }

    /**
     * @param $fieldID
     * @return array|null
     * @throws \Exception
     */
    public function prepareFieldData($fieldID): ?array
    {
        $result =  parent::prepareFieldData($fieldID);
        if (Item::FIELD_NAME_CATEGORY_ID)
        {
            $result = [
                'params' => ['multiple' => 'Y'],
                'items' => [
                    Dynamic::getInstance()->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_DEFAULT_CODE) => Constants::DYNAMIC_CATEGORY_DEFAULT_TITLE,
                    Dynamic::getInstance()->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE) => Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_TITLE,
                    //Dynamic::getInstance()->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE) => Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_TITLE,
                ],
            ];
        }
        return $result;
    }
}