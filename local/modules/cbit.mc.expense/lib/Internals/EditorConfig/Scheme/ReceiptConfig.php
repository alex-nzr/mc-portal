<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ReceiptConfig.php
 * 23.02.2023 18:04
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\EditorAdapter;

/**
 * @class ReceiptConfig
 * @package Cbit\Mc\Expense\Internals\EditorConfig\Scheme
 */
class ReceiptConfig extends CommonConfig
{
    /**
     * @return array
     */
    protected function getConfigScheme(): array
    {
        $commonScheme = parent::getConfigScheme();
        $commonScheme[] = [
            'name' =>  "receipt",
            'title' => "Receipt",
            'type' => "section",
            'elements' => [
                [
                    'name' => "UF_CRM_". $this->typeId ."_CATEGORY_OF_RECEIPT",
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
                [
                    'name' => "UF_CRM_". $this->typeId ."_PSSS",
                    'optionFlags' => '1'
                ],
                [
                    'name' => "UF_CRM_". $this->typeId ."_PARTICIPANTS_INTERNAL",
                    'optionFlags' => '1'
                ],
                [
                    'name' => "UF_CRM_". $this->typeId ."_PARTICIPANTS_EXTERNAL",
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
            'UF_CRM_' . $this->typeId . '_CATEGORY_OF_RECEIPT',
            'UF_CRM_' . $this->typeId . '_EXPENSE_DATE',
            'UF_CRM_' . $this->typeId . '_CATEGORY_OF_RECEIPT',
            'UF_CRM_' . $this->typeId . '_CHARGE_CODE',
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReadonlyFields(): array
    {
        return parent::getReadonlyFields();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHiddenFields(): array
    {
        return parent::getHiddenFields();
    }
}