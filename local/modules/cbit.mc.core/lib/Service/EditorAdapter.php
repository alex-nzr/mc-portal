<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - EditorAdapter.php
 * 24.02.2023 19:43
 * ==================================================
 */

namespace Cbit\Mc\Core\Service;

use Cbit\Mc\Core\Internals\Contract\IEditorConfig;
use Cbit\Mc\Core\Service\EntityEditor\FieldManager;
use Bitrix\Crm\Field;
use CCrmFieldInfoAttr;

/**
 * @class EditorAdapter
 * @package Cbit\Mc\Core\Service
 */
abstract class EditorAdapter extends \Bitrix\Crm\Service\EditorAdapter
{
    protected static  ?Field\Collection $staticFieldsCollection = null;
    protected int     $entityTypeId;
    protected Context $crmContext;
    protected int $typeId;

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldsCollection
     * @param array $dependantFieldsMap
     * @throws \Exception
     */
    public function __construct(Field\Collection $fieldsCollection, array $dependantFieldsMap = [])
    {
        static::$staticFieldsCollection = $fieldsCollection;
        parent::__construct($fieldsCollection, $dependantFieldsMap);
    }

    /**
     * @param array $userFields
     * @param array $visibilityConfig
     * @param int $entityTypeId
     * @param int $entityId
     * @param string $fileHandlerUrl
     * @return array
     */
    public static function prepareEntityUserFields(array $userFields, array $visibilityConfig, int $entityTypeId, int $entityId, string $fileHandlerUrl = ''): array
    {
        $userFields = [];
        if (static::$staticFieldsCollection !== null)
        {
            foreach (static::$staticFieldsCollection as $field)
            {
                if ($field->isUserField())
                {
                    $userField = $field->getUserField();
                    $userField['ATTRIBUTES'] = $field->getAttributes();
                    $userFields[$field->getName()] = $userField;
                }
            }
        }

        $preparedFields = parent::prepareEntityUserFields($userFields, $visibilityConfig, $entityTypeId, $entityId, $fileHandlerUrl);

        foreach ($preparedFields as $key => $field)
        {
            $srcField = array_key_exists($field['name'], $userFields) ? $userFields[$field['name']] : [];

            $editable = !empty($srcField)
                && isset($srcField['EDIT_IN_LIST'])
                && ($srcField['EDIT_IN_LIST'] === 'Y')
                && !CCrmFieldInfoAttr::isFieldReadOnly($srcField);

            $preparedFields[$key]['enableAttributes'] = true;
            $preparedFields[$key]['editable'] = $editable;
        }

        return $preparedFields;
    }

    /**
     * @param \Cbit\Mc\Core\Internals\Contract\IEditorConfig $config
     * @return void
     * @throws \Exception
     */
    protected function markReadonlyFields(IEditorConfig $config): void
    {
        FieldManager::getInstance($this->entityTypeId)->markReadonlyFieldsByConfig(
            $this->fieldsCollection,
            $config
        );
    }

    /**
     * @param \Cbit\Mc\Core\Internals\Contract\IEditorConfig $config
     * @return void
     * @throws \Exception
     */
    protected function markHiddenFields(IEditorConfig $config): void
    {
        FieldManager::getInstance($this->entityTypeId)->markHiddenFieldsByConfig(
            $this->fieldsCollection,
            $config
        );
    }

    /**
     * @param \Cbit\Mc\Core\Internals\Contract\IEditorConfig|null $config
     * @return void
     * @throws \Exception
     */
    protected function processAdditionalFields(?IEditorConfig $config): void
    {
        $this->additionalFields = FieldManager::getInstance($this->entityTypeId)->markAdditionalFieldsByConfig(
            $this->additionalFields,
            $config
        );
    }


    /*protected function processFieldsAttributes(array $fields, int $mode, Item $item): array
    {
        return parent::processFieldsAttributes($fields, $mode, $item);
    }

    protected function getEntityDataForEntityFields(Item $item, array $entityFields, array $entityData): array
    {
        return parent::getEntityDataForEntityFields($item, $entityFields, $entityData);
    }*/

    /**
     * @param string $fieldCaption
     * @return void
     */
    public function addClientField(string $fieldCaption): void
    {
        $this->addEntityField(
            static::getClientField(
                $fieldCaption,
                static::FIELD_CLIENT,
                static::FIELD_CLIENT_DATA_NAME,
                ['entityTypeId' => $this->entityTypeId]
            )
        );
    }

    /**
     * @param string $fieldCaption
     * @param bool $isPaymentsEnabled
     * @return void
     */
    public function addOpportunityField(string $fieldCaption, bool $isPaymentsEnabled): void
    {
        $this->addEntityField(
            static::getOpportunityField($fieldCaption, static::FIELD_OPPORTUNITY, $isPaymentsEnabled)
        );
    }

    /**
     * @param string $fieldCaption
     * @return void
     */
    public function addProductRowSummaryField(string $fieldCaption): void
    {
        $this->addEntityField(static::getProductRowSummaryField($fieldCaption));
    }

    /**
     * @param int $entityTypeId
     * @return \Cbit\Mc\RI\Service\EditorAdapter
     */
    public function setEntityTypeId(int $entityTypeId): static
    {
        $this->entityTypeId = $entityTypeId;
        return $this;
    }

    /**
     * @param int $typeId
     * @return \Cbit\Mc\RI\Service\EditorAdapter
     */
    public function setTypeId(int $typeId): static
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * @param \Cbit\Mc\Core\Service\Context $crmContext
     * @return $this
     */
    public function setCrmContext(Context $crmContext): static
    {
        $this->crmContext = $crmContext;
        return $this;
    }
}