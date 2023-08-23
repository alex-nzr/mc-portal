<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - TreatYourselfBudgetConfig.php
 * 23.02.2023 19:08
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Cbit\Mc\Expense\Service\EditorAdapter;

/**
 * @class TreatYourselfBudgetConfig
 * @package Cbit\Mc\Expense\Internals\EditorConfig\Scheme
 */
class TreatYourselfBudgetConfig extends CommonConfig
{
    /**
     * @return array
     */
    protected function getConfigScheme(): array
    {
        $commonScheme = parent::getConfigScheme();

        $commonScheme[] = [
            'name' =>  "treat_yourself_budget",
            'title' => "Treat yourself budget",
            'type' => "section",
            'elements' => [
                [
                    'name' => "UF_CRM_". $this->typeId ."_CATEGORY_OF_TR_YOUR_BUDGET",
                    'optionFlags' => '1'
                ],
                [
                    'name' => "UF_CRM_". $this->typeId ."_EXPENSE_DATE",
                    'optionFlags' => '1'
                ],
                [
                    'name' => EditorAdapter::FIELD_OPPORTUNITY,
                    'optionFlags' => '1'
                ],
            ]
        ];

        return $commonScheme;
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        return array_merge(parent::getRequiredFields(), [
            'UF_CRM_' . $this->typeId . '_CATEGORY_OF_TR_YOUR_BUDGET',
            'UF_CRM_' . $this->typeId . '_EXPENSE_DATE',
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReadonlyFields(): array
    {
        $fields = parent::getReadonlyFields();

        $fields[] = 'UF_CRM_' . $this->typeId . '_CHARGE_CODE';

        return $fields;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHiddenFields(): array
    {
        $fields = parent::getHiddenFields();
        if ($this->item->isNew())
        {
            $fields[] = 'UF_CRM_' . $this->typeId . '_CHARGE_CODE';
        }
        return $fields;
    }
}