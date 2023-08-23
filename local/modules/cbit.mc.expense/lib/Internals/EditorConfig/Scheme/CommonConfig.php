<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - CommonConfig.php
 * 23.02.2023 14:19
 * ==================================================
 */


namespace Cbit\Mc\Expense\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Cbit\Mc\Core\Internals\EditorConfig\BaseConfig;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Service\Access\UserPermissions;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\EditorAdapter;

/**
 * @class CommonConfig
 * @package Cbit\Mc\Expense\Internals\EditorConfig\Scheme
 */
abstract class CommonConfig extends BaseConfig
{
    protected ?Item $item;
    private UserPermissions $perms;

    /**
     * @param int $typeId
     * @param int $entityTypeId
     * @throws \Exception
     */
    public function __construct(int $typeId, int $entityTypeId)
    {
        parent::__construct($typeId, $entityTypeId);
        $this->item  = Container::getInstance()->getContext()->getItem();
        $this->perms = Container::getInstance()->getUserPermissions();
    }

    /**
     * @return array[]
     */
    protected function getConfigScheme(): array
    {
        return [
            [
                'name' =>  "general",
                'title' => "General",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $this->typeId ."_DUPLICATE_OF",
                    ],
                    [
                        'name' => Item::FIELD_NAME_CREATED_BY,
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => Item::FIELD_NAME_ASSIGNED,
                        'optionFlags' => '1'
                    ],

                    [
                        'name' => "UF_CRM_". $this->typeId ."_CHARGE_CODE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_ATTACHMENTS",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_COMMENT",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_APPROVAL_DATE",
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_AMOUNT_REJECTED",
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_REASON",
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_INITIAL_AMOUNT",
                    ],
                ]
            ]
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHiddenFields(): array
    {
        $fields = parent::getHiddenFields();

        if (!empty($this->item))
        {
            $fields[] = Item::FIELD_NAME_TITLE;

            if ($this->item->isNew())
            {
                $fields[] = Item::FIELD_NAME_ASSIGNED;
                $fields[] = 'UF_CRM_' . $this->typeId . '_PSSS';
                $fields[] = 'UF_CRM_' . $this->typeId . '_APPROVAL_DATE';
                $fields[] = 'UF_CRM_' . $this->typeId . '_AMOUNT_REJECTED';
                $fields[] = 'UF_CRM_' . $this->typeId . '_REASON';
                $fields[] = 'UF_CRM_' . $this->typeId . '_DUPLICATE_OF';
                $fields[] = 'UF_CRM_' . $this->typeId . '_PARTICIPANTS_TOTAL';
                $fields[] = 'UF_CRM_' . $this->typeId . '_INITIAL_AMOUNT';

                if (!Dynamic::getInstance()->isItemInFirstStage($this->item, $this->item->isChangedStageId())
                    && !Dynamic::getInstance()->isItemInRejectStage($this->item, $this->item->isChangedStageId())
                ){
                    $fields[] = Item::FIELD_NAME_ASSIGNED;
                }
            }
        }

        return array_values(array_unique($fields));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReadonlyFields(): array
    {
        $fields = parent::getReadonlyFields();

        if (!empty($this->item))
        {
            $fields[] = 'UF_CRM_'.$this->typeId.'_PSSS';
            $fields[] = 'UF_CRM_'.$this->typeId.'_INITIAL_AMOUNT';
            $fields[] = 'UF_CRM_'.$this->typeId.'_AMOUNT_REJECTED';
            $fields[] = 'UF_CRM_'.$this->typeId.'_REASON';

            if (!$this->item->isNew())
            {
                if (!$this->perms->hasUserAnyPermissionsForExpense())
                {
                    $fields[] = Item::FIELD_NAME_ASSIGNED;
                }

                if (!Dynamic::getInstance()->isItemInFirstStage($this->item, $this->item->isChangedStageId())
                    && !Dynamic::getInstance()->isItemInRejectStage($this->item, $this->item->isChangedStageId())
                ){
                    $fields[] = EditorAdapter::FIELD_OPPORTUNITY;
                    if (!$this->perms->hasUserAnyPermissionsForExpense())
                    {
                        foreach ($this->getFieldCodesFromScheme() as $code)
                        {
                            if ($code !== "UF_CRM_".$this->typeId."_ATTACHMENTS")
                            {
                                $fields[] = $code;
                            }
                        }
                    }
                }
            }
        }

        return array_values(array_unique($fields));
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        return array_merge(parent::getRequiredFields(), [
            Item::FIELD_NAME_OPPORTUNITY,
            Item::FIELD_NAME_CURRENCY_ID,
            'OPPORTUNITY_WITH_CURRENCY',
            'UF_CRM_' . $this->typeId . '_ATTACHMENTS',
        ]);
    }
}