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

namespace Cbit\Mc\RI\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Cbit\Mc\Core\Internals\EditorConfig\BaseConfig;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Access\UserPermissions;
use Cbit\Mc\RI\Service\Container;

/**
 * @class CommonConfig
 * @package Cbit\Mc\RI\Internals\EditorConfig\Scheme
 */
class CommonConfig extends BaseConfig
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
                'title' => "General request info",
                'type' => "section",
                'elements' => [
                    [
                        'name' => 'TITLE',
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => 'CREATED_BY',
                        'title'=> 'Requester',
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_ASSIGNED_BY",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PER_DIEM"
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_CHARGE_CODE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_TYPE_OF_REQUEST",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_INDUSTRY",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_FUNCTION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_LOCATION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_DESCRIPTION",
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
                        'name' => "UF_CRM_". $this->typeId ."_MAX_BUDGET",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_DEADLINE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_LAST_STATUS_CHANGE",
                        'optionFlags' => '1'
                    ],
                ]
            ],
            [
                'name' =>  "scoring",
                'title' => "Scoring",
                'type' => "section",
                'elements' => [
                    ['name' => "UF_CRM_". $this->typeId ."_SCORE_SPEED"],
                    ['name' => "UF_CRM_". $this->typeId ."_SCORE_WORK"],
                    ['name' => "UF_CRM_". $this->typeId ."_SCORE_COMMUNICATION"],
                    ['name' => "UF_CRM_". $this->typeId ."_SCORE_COMMENT"],
                ]
            ],

            [
                'name' =>  "labor_cost",
                'title' => "Labor cost",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $this->typeId ."_LABOR_COSTS_PLAN",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_LABOR_COSTS_FACT",
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name' =>  "result",
                'title' => "Result",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $this->typeId ."_RESULT_DESCRIPTION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_RESULT_ATTACHMENTS",
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name' =>  "cancelling",
                'title' => "Cancelling",
                'type' => "section",
                'elements' => [
                    ['name' => "UF_CRM_". $this->typeId ."_CANCEL_REASON"],
                    ['name' => "UF_CRM_". $this->typeId ."_CANCEL_COMMENT"],
                ]
            ],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHiddenFields(): array
    {
        $fields   = parent::getHiddenFields();
        $fields[] = [Item::FIELD_NAME_ASSIGNED];

        if (!$this->perms->hasUserRiManagerPermissions())
        {
            $fields[] = 'UF_CRM_'.$this->typeId.'_PER_DIEM';
            $fields[] = 'UF_CRM_'.$this->typeId.'_LABOR_COSTS_PLAN';
            $fields[] = 'UF_CRM_'.$this->typeId.'_LABOR_COSTS_FACT';
        }

        if (!empty($this->item))
        {
            if (!Dynamic::getInstance()->isItemInFinalStage($this->item))
            {
                $fields[] = 'UF_CRM_'.$this->typeId.'_CANCEL_REASON';
                $fields[] = 'UF_CRM_'.$this->typeId.'_CANCEL_COMMENT';
                $fields[] = 'UF_CRM_'.$this->typeId.'_SCORE_SPEED';
                $fields[] = 'UF_CRM_'.$this->typeId.'_SCORE_WORK';
                $fields[] = 'UF_CRM_'.$this->typeId.'_SCORE_COMMUNICATION';
                $fields[] = 'UF_CRM_'.$this->typeId.'_SCORE_COMMENT';
            }

            if ($this->item->isNew())
            {
                $fields[] = 'UF_CRM_'.$this->typeId.'_ASSIGNED_BY';
                $fields[] = 'UF_CRM_'.$this->typeId.'_PER_DIEM';
                $fields[] = 'UF_CRM_'.$this->typeId.'_LABOR_COSTS_PLAN';
                $fields[] = 'UF_CRM_'.$this->typeId.'_LABOR_COSTS_FACT';
                $fields[] = 'UF_CRM_'.$this->typeId.'_RESULT_DESCRIPTION';
                $fields[] = 'UF_CRM_'.$this->typeId.'_RESULT_ATTACHMENTS';
            }
            else
            {
                if (!Dynamic::getInstance()->isItemInFailStage($this->item))
                {
                    $fields[] = 'UF_CRM_'.$this->typeId.'_CANCEL_REASON';
                    $fields[] = 'UF_CRM_'.$this->typeId.'_CANCEL_COMMENT';
                }

                if (!$this->perms->hasUserAnyPermissionsForRi())
                {
                    if (Dynamic::getInstance()->isItemInUnassignedStages($this->item))
                    {
                        $fields[] = 'UF_CRM_'.$this->typeId.'_ASSIGNED_BY';
                    }

                    if (!Dynamic::getInstance()->isItemInSuccessStage($this->item))
                    {
                        $fields[] = 'UF_CRM_'.$this->typeId.'_RESULT_DESCRIPTION';
                        $fields[] = 'UF_CRM_'.$this->typeId.'_RESULT_ATTACHMENTS';
                    }
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
        $fields   = parent::getReadonlyFields();
        $fields[] = Item::FIELD_NAME_ASSIGNED;

        if (!empty($this->item))
        {
            if (!$this->item->isNew())
            {
                $fields[] = Item::FIELD_NAME_TITLE;
                $fields[] = 'UF_CRM_'.$this->typeId.'_CHARGE_CODE';
                $fields[] = 'UF_CRM_'.$this->typeId.'_TYPE_OF_REQUEST';
                $fields[] = 'UF_CRM_'.$this->typeId.'_INDUSTRY';
                $fields[] = 'UF_CRM_'.$this->typeId.'_FUNCTION';
                $fields[] = 'UF_CRM_'.$this->typeId.'_LOCATION';
                $fields[] = 'UF_CRM_'.$this->typeId.'_DESCRIPTION';
                $fields[] = 'UF_CRM_'.$this->typeId.'_DEADLINE';
                $fields[] = 'UF_CRM_'.$this->typeId.'_COMMENT';
                $fields[] = 'UF_CRM_'.$this->typeId.'_MAX_BUDGET';
                $fields[] = 'UF_CRM_'.$this->typeId.'_LAST_STATUS_CHANGE';

                if (!$this->perms->hasUserAnyPermissionsForRi())
                {
                    $fields[] = 'UF_CRM_'.$this->typeId.'_LABOR_COSTS_PLAN';
                    $fields[] = 'UF_CRM_'.$this->typeId.'_LABOR_COSTS_FACT';
                    $fields[] = 'UF_CRM_'.$this->typeId.'_RESULT_DESCRIPTION';
                    $fields[] = 'UF_CRM_'.$this->typeId.'_RESULT_ATTACHMENTS';
                }

                if (!$this->perms->checkChangeAssignedPermissions())
                {
                    $fields[] = 'UF_CRM_'.$this->typeId.'_ASSIGNED_BY';
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

        ]);
    }
}