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
namespace Cbit\Mc\Partner\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Cbit\Mc\Core\Internals\EditorConfig\BaseConfig;
use Cbit\Mc\Partner\Service\Access\UserPermissions;
use Cbit\Mc\Partner\Service\Container;

/**
 * @class CommonConfig
 * @package Cbit\Mc\Partner\Internals\EditorConfig\Scheme
 */
class CommonConfig extends BaseConfig
{
    protected ?Item $item;
    protected UserPermissions $perms;

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
                        'name' => Item::FIELD_NAME_TITLE,
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_SITE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_TYPE",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_DESC",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_INDUSTRY",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_COUNTRY",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_EXPERTISE_LOCATION",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_INTERACTION_SCHEME",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => Item::FIELD_NAME_ASSIGNED,
                        'optionFlags' => '1'
                    ],
                ]
            ],
            [
                'name' =>  "contact",
                'title' => "Contact",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_CONTACT_FIO",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_CONTACT_EMAIL",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_PARTNER_CONTACT_PHONE",
                        'optionFlags' => '1'
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
        if (!$this->perms->hasUserAnyPermissionsForRi())
        {
            $fields[] = "UF_CRM_". $this->typeId ."_PARTNER_CONTACT_FIO";
            $fields[] = "UF_CRM_". $this->typeId ."_PARTNER_CONTACT_EMAIL";
            $fields[] = "UF_CRM_". $this->typeId ."_PARTNER_CONTACT_PHONE";
        }
        return $fields;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReadonlyFields(): array
    {
        $fields = parent::getReadonlyFields();

        if (!$this->perms->hasUserAnyPermissionsForRi())
        {
            $fields = array_keys($this->entityFields);
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        return parent::getRequiredFields();
    }
}