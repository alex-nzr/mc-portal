<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EntityEditorConfig.php
 * 17.01.2023 13:46
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Service\Integration\Crm\Entity;

use Bitrix\Crm\Entity\EntityEditorConfigScope;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Exception;

/**
 * Class EntityEditorConfig
 * @package Cbit\Mc\Staffing\Service\Integration\Crm\Entity
 */
class EntityEditorConfig extends \Bitrix\Crm\Entity\EntityEditorConfig
{
    /**
     * Factory in \Bitrix\Crm\Entity\EntityEditorConfig can not find additional categories while module installing,
     * that's why needle to manually set an ID of card's configuration for category
     * @return string
     * @throws \Exception
     */
    protected function getConfigId(): string
    {
        $categoryId = $this->extras['CATEGORY_ID'];
        if (is_numeric($categoryId) && (int)$categoryId > 0)
        {
            return 'DYNAMIC_'. $this->entityTypeID .'_details_C'. $categoryId;
        }
        return parent::getConfigId();
    }

    private static function getDefaultEditorConfig($typeId): array
    {
        return [
            [
                'name' =>  "general",
                'title' => "General",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $typeId ."_CHARGE_CODE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_DESCRIPTION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_EMPLOYMENT_TYPE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_INDUSTRY",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_ACTIVITY",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_PHASE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_STATE",
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name'  =>  "additional",
                'title' => "Additional",
                'type'  => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $typeId ."_ED",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_FUNCTION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_TEAM_COMPOSITION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_MASTER_CLIENT",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_CLIENT",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_START_DATE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_END_DATE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_DURATION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_LOCATION",
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name'  => "step_dates",
                'title' => "Step dates",
                'type'  => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $typeId ."_DISCUSSION_DATE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_DEVELOPMENT_DATE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_CONFIRMED_DATE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_FINISH_OR_OUT_DATE",
                        'optionFlags' => '1'
                    ],
                ]
            ],
        ];
    }

    /**
     * @param int $entityTypeId
     * @param int $typeId
     * @return \Bitrix\Main\Result
     */
    public static function setTypeCardConfig(int $entityTypeId, int $typeId): Result
    {
        $result = new Result();
        try
        {
            $userID            = !empty($GLOBALS['USER']) ? (int)CurrentUser::get()->getId() : 1;
            $scope             = EntityEditorConfigScope::COMMON;
            $cardConfiguration = static::getDefaultEditorConfig($typeId);

            if (!empty($cardConfiguration))
            {
                $categories = ItemCategoryTable::query()->where('ENTITY_TYPE_ID', $entityTypeId)->fetchCollection();
                foreach ($categories as $category)
                {
                    $extras = [
                        'CATEGORY_ID' => $category->getId(),
                    ];

                    $config = new static($entityTypeId, $userID, $scope, $extras);
                    $data = $config->normalize($cardConfiguration);
                    $data = $config->sanitize($data);
                    $config->set($data);
                }
            }
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }
}