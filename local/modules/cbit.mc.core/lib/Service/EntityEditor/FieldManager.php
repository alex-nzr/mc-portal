<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - FieldManager.php
 * 19.02.2023 13:07
 * ==================================================
 */
namespace Cbit\Mc\Core\Service\EntityEditor;

use Bitrix\Crm\Attribute\Entity\FieldAttributeTable;
use Bitrix\Crm\Attribute\FieldAttributeManager;
use Bitrix\Crm\Attribute\FieldAttributePhaseGroupType;
use Bitrix\Crm\Attribute\FieldAttributeType;
use Bitrix\Crm\Field\Collection;
use Cbit\Mc\Core\Internals\Contract\IEditorConfig;
use CCrmFieldInfoAttr;
use Exception;

/**
 * @class FieldManager
 * @package Cbit\Mc\Core\Service\EntityEditor
 */
class FieldManager
{
    private static array $instances = [];
    private int $entityTypeId;

    /**
     * @param int $entityTypeId
     * @throws \Exception
     */
    private function __construct(int $entityTypeId)
    {
        $this->entityTypeId = $entityTypeId;
    }

    /**
     * @param int $entityTypeId
     * @return \Cbit\Mc\Core\Service\EntityEditor\FieldManager|null
     * @throws \Exception
     */
    public static function getInstance(int $entityTypeId): ?FieldManager
    {
        if (!array_key_exists($entityTypeId, static::$instances))
        {
            static::$instances[$entityTypeId] = new static($entityTypeId);
        }
        return static::$instances[$entityTypeId];
    }

    /**
     * @param string $requiredFieldName
     * @param int $categoryId
     * @return void
     * @throws \Exception
     */
    public function saveFieldAsRequired(string $requiredFieldName, int $categoryId): void
    {
        $entityScope = 'category_' . $categoryId;
        $exists = FieldAttributeTable::query()
            ->setSelect(['ID'])
            ->setFilter([
                'ENTITY_TYPE_ID' => $this->entityTypeId,
                'ENTITY_SCOPE'   => $entityScope,
                'TYPE_ID'        => FieldAttributeType::REQUIRED,
                'FIELD_NAME'     => $requiredFieldName,
            ])
            ->fetch();

        if (is_array($exists))
        {
            $delRes = FieldAttributeTable::delete($exists['ID']);
            if (!$delRes->isSuccess())
            {
                throw new Exception(implode('; ', $delRes->getErrorMessages()));
            }
        }

        FieldAttributeManager::saveEntityConfiguration(
            [
                'typeId' => FieldAttributeType::REQUIRED,
                'groups' => [
                    [
                        'phaseGroupTypeId' => FieldAttributePhaseGroupType::ALL
                    ]
                ]
            ],
            $requiredFieldName,
            $this->entityTypeId,
            $entityScope
        );
    }

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldCollection
     * @param \Cbit\Mc\Core\Internals\Contract\IEditorConfig|null $config
     * @return void
     */
    public function markReadonlyFieldsByConfig(Collection $fieldCollection, ?IEditorConfig $config = null): void
    {
        if (!empty($config))
        {
            $readonlyFields = $config->getReadonlyFields();

            foreach ($fieldCollection as $field)
            {
                if (in_array($field->getName(), $readonlyFields))
                {
                    $field->setAttributes(
                        array_unique(array_merge($field->getAttributes(), [CCrmFieldInfoAttr::ReadOnly]))
                    );
                }
            }
        }
    }

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldCollection
     * @param \Cbit\Mc\Core\Internals\Contract\IEditorConfig|null $config
     * @return void
     */
    public function markHiddenFieldsByConfig(Collection $fieldCollection, ?IEditorConfig $config = null): void
    {
        if (!empty($config))
        {
            $hiddenFields = $config->getHiddenFields();

            foreach ($fieldCollection as $field)
            {
                if (in_array($field->getName(), $hiddenFields))
                {
                    $field->setAttributes(
                        array_unique(array_merge($field->getAttributes(), [CCrmFieldInfoAttr::NotDisplayed]))
                    );
                }
            }
        }
    }

    /**
     * Check only readonly attr, because required and hidden already works
     * @param array $additionalFields
     * @param \Cbit\Mc\Core\Internals\Contract\IEditorConfig|null $config
     * @return array
     */
    public function markAdditionalFieldsByConfig(array $additionalFields, ?IEditorConfig $config): array
    {
        $preparedFields = $additionalFields;
        foreach ($config->getReadonlyFields() as $readonlyField)
        {
            if (array_key_exists($readonlyField, $preparedFields) && is_array($preparedFields[$readonlyField]))
            {
                $preparedFields[$readonlyField]['editable'] = false;
            }
        }
        return $preparedFields;
    }

    private function __clone(){}
    public function __wakeup(){}
}