<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - TypeInstaller.php
 * 17.01.2023 20:08
 * ==================================================
 */
namespace Cbit\Mc\RI\Internals\Installation;

use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Crm\Model\Dynamic\TypeTable;
use Bitrix\Crm\Relation;
use Bitrix\Crm\RelationIdentifier;
use Bitrix\Crm\UserField\UserFieldManager;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\Result;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Service\Container;
use CCrmOwnerType;

/**
 * Class TypeInstaller
 * @package Cbit\Mc\RI\Internals\Installation
 */
class TypeInstaller
{
    /**
     * @param int $customSectionId
     * @return \Bitrix\Main\Orm\Data\UpdateResult | \Bitrix\Main\Orm\Data\AddResult
     * @throws \Exception
     */
    public static function install(int $customSectionId): UpdateResult|AddResult
    {
        $container     = Container::getInstance();
        $typeDataClass = $container->getDynamicTypeDataClass();
        $typeCode      = Constants::DYNAMIC_TYPE_CODE;
        $title         = Constants::DYNAMIC_TYPE_TITLE;

        $existsId = static::getTypeIdByCode($typeCode, $typeDataClass);

        if ((int)$existsId > 0)
        {
            $type   = $typeDataClass::getByPrimary($existsId)->fetchObject();
            $fields = static::getFields(
                $title, $type->get('NAME'), $typeCode, $type->get('ENTITY_TYPE_ID'), $customSectionId
            );
            return static::setTypeData($type, $fields, true);
        }
        else
        {
            $type         = $typeDataClass::createObject();
            $name         = $typeDataClass::generateName($title);
            $entityTypeId = $typeDataClass::getNextAvailableEntityTypeId() ?? 0;

            $fields = static::getFields($title, $name, $typeCode, $entityTypeId, $customSectionId);

            return static::setTypeData($type, $fields);
        }
    }

    /**
     * @throws \Exception
     */
    public static function uninstall(): Result
    {
        $container = Container::getInstance();
        $typeDataClass = $container->getDynamicTypeDataClass();
        $typeCode = Constants::DYNAMIC_TYPE_CODE;
        $existsId = static::getTypeIdByCode($typeCode, $typeDataClass);

        if (!empty($existsId))
        {
            $type = $typeDataClass::getByPrimary($existsId)->fetchObject();
            return $type->delete();
        }

        return new Result;
    }

    /**
     * @param \Bitrix\Main\ORM\Objectify\EntityObject | \Bitrix\Crm\Model\Dynamic\Type | null $type
     * @param array $fields
     * @param bool $isUpdate
     * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult
     * @throws \Exception
     */
    protected static function setTypeData(EntityObject|Type|null $type, array $fields, bool $isUpdate = false): UpdateResult|AddResult
    {
        foreach($fields as $name => $value)
        {
            if($type->entity->hasField($name))
            {
                $type->set($name, $value);
            }
        }

        $finalRes = $isUpdate ? new UpdateResult() : new AddResult();

        $result = $type->save();
        if($result->isSuccess())
        {
            $entityTypeId = $type->getEntityTypeId();

            static::saveConversionMap($entityTypeId, $fields);

            if ($type->getIsUseInUserfieldEnabled())
            {
                static::saveLinkedUserFields(CCrmOwnerType::ResolveName($entityTypeId), $fields);
            }

            $relationsResult = static::saveRelations($entityTypeId, $fields);
            if (!$relationsResult->isSuccess())
            {
                $finalRes->addErrors($relationsResult->getErrors());
            }

            $typeId = $type->getId() ?? $result->getId();
            $finalRes->setPrimary($typeId);
            $finalRes->setData($fields);
        }
        else
        {
            $finalRes->addErrors($result->getErrors());
        }

        return $finalRes;
    }

    /**
     * @param string $entityTypeName
     * @param array $fields
     */
    protected static function saveLinkedUserFields(string $entityTypeName, array $fields): void
    {
        $settings = $fields['LINKED_USER_FIELDS'] ?? null;
        if (!is_array($settings))
        {
            return;
        }

        $userFieldsMap = UserFieldManager::getLinkedUserFieldsMap();

        foreach ($settings as $name => $isEnabled)
        {
            if (isset($userFieldsMap[$name]))
            {
                UserFieldManager::enableEntityInUserField(
                    $userFieldsMap[$name],
                    $entityTypeName,
                    $isEnabled === 'true'
                );
            }
        }
    }

    /**
     * @param int $entityTypeId
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    protected static function saveRelations(int $entityTypeId, array $fields): Result
    {
        $result = new Result();
        $relations = $fields['RELATIONS'] ?? null;
        if (!is_array($relations))
        {
            return $result;
        }
        $relationManager = Container::getInstance()->getRelationManager();
        $relationsCollection = $relationManager->getRelations($entityTypeId);
        if (array_key_exists('PARENT', $relations))
        {
            $availableForBindingEntityTypes = $relationManager->getAvailableForParentBindingEntityTypes($entityTypeId);
            $selectedParentTypes = static::prepareRelationsData((array)$relations['PARENT']);

            foreach ($availableForBindingEntityTypes as $availableTypeId => $description)
            {
                $typeResult = static::processRelation(
                    $relationsCollection,
                    new RelationIdentifier($availableTypeId, $entityTypeId),
                    $selectedParentTypes[$availableTypeId] ?? null
                );
                if (!$typeResult->isSuccess())
                {
                    $result->addErrors($typeResult->getErrors());
                }
            }
        }

        if (array_key_exists('CHILD', $relations))
        {
            $availableForBindingEntityTypes = $relationManager->getAvailableForChildBindingEntityTypes($entityTypeId);
            $selectedChildTypes = static::prepareRelationsData((array)$relations['CHILD']);
            foreach ($availableForBindingEntityTypes as $availableTypeId => $description)
            {
                $typeResult = static::processRelation(
                    $relationsCollection,
                    new RelationIdentifier($entityTypeId, $availableTypeId),
                    $selectedChildTypes[$availableTypeId] ?? null
                );
                if (!$typeResult->isSuccess())
                {
                    $result->addErrors($typeResult->getErrors());
                }
            }
        }

        return $result;
    }

    /**
     * @param array $relations
     * @return array
     */
    protected static function prepareRelationsData(array $relations): array
    {
        $result = [];

        foreach ($relations as $relationData)
        {
            if (!isset($relationData['ENTITY_TYPE_ID']))
            {
                continue;
            }
            $entityTypeId = (int)$relationData['ENTITY_TYPE_ID'];
            if ($entityTypeId > 0)
            {
                $result[$entityTypeId] = [
                    'entityTypeId' => $entityTypeId,
                    'isChildrenListEnabled' => $relationData['IS_CHILDREN_LIST_ENABLED'] === 'true',
                ];
            }
        }

        return $result;
    }

    /**
     * @param \Bitrix\Crm\Relation\Collection $relations
     * @param \Bitrix\Crm\RelationIdentifier $identifier
     * @param array|null $relationData
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    protected static function processRelation( Relation\Collection $relations, RelationIdentifier $identifier, ?array $relationData): Result
    {
        $relationManager = Container::getInstance()->getRelationManager();
        $relation = $relations->get($identifier);
        if ($relationData)
        {
            if ($relation)
            {
                if ($relation->isChildrenListEnabled() !== $relationData['isChildrenListEnabled'])
                {
                    $relation->setChildrenListEnabled($relationData['isChildrenListEnabled']);
                    return $relationManager->updateTypesBinding($relation);
                }
            }
            else
            {
                $settings = (new Relation\Settings())
                    ->setIsChildrenListEnabled($relationData['isChildrenListEnabled']);
                return $relationManager->bindTypes(
                    new Relation(
                        $identifier,
                        $settings,
                    )
                );
            }
        }
        elseif ($relation)
        {
            return $relationManager->unbindTypes($relation->getIdentifier());
        }

        return new Result();
    }

    /**
     * @param string $code
     * @param string|TypeTable $dataClass
     * @return int|null
     * @throws \Exception
     */
    protected static function getTypeIdByCode(string $code, TypeTable|string $dataClass): ?int
    {
        if (is_string($dataClass)){
            $dataClass = new $dataClass;
        }

        $res = $dataClass::query()
            ->setFilter(['CODE' => $code])
            ->setSelect(['ID'])
            ->exec()
            ->fetch();

        return !empty($res) ? (int)$res['ID'] : null;
    }

    /**
     * @param $title
     * @param $name
     * @param $code
     * @param $entityTypeId
     * @param $customSectionId
     * @return array
     */
    protected static function getFields($title, $name, $code, $entityTypeId, $customSectionId): array
    {
        return [
            "TITLE" => $title,
            "CODE"  => $code,
            "NAME" => $name,
            "ENTITY_TYPE_ID" => $entityTypeId,
            "CREATED_BY" => !empty($GLOBALS['USER']) ? ((int)CurrentUser::get()->getId()) : 1,
            "IS_CATEGORIES_ENABLED" => false,
            "IS_STAGES_ENABLED" => true,
            "IS_BEGIN_CLOSE_DATES_ENABLED" => false,
            "IS_CLIENT_ENABLED" => false,
            "IS_USE_IN_USERFIELD_ENABLED" => true,
            "IS_LINK_WITH_PRODUCTS_ENABLED" => false,
            "IS_MYCOMPANY_ENABLED" => false,
            "IS_DOCUMENTS_ENABLED" => false,
            "IS_SOURCE_ENABLED" => false,
            "IS_OBSERVERS_ENABLED" => true,
            "IS_RECYCLEBIN_ENABLED" => true,
            "IS_AUTOMATION_ENABLED" => true,
            "IS_BIZ_PROC_ENABLED" => true,
            "IS_SET_OPEN_PERMISSIONS" => true,
            "IS_PAYMENTS_ENABLED" => false,
            "LINKED_USER_FIELDS" => [
                "CALENDAR_EVENT|UF_CRM_CAL_EVENT" => 'true',
                "TASKS_TASK|UF_CRM_TASK" => 'true',
                "TASKS_TASK_TEMPLATE|UF_CRM_TASK" => 'true',
            ],
            "CUSTOM_SECTIONS" => [
                [
                    "ID"          => $customSectionId,
                    "TITLE"       => Constants::DYNAMIC_TYPE_CUSTOM_SECTION_TITLE,
                    "IS_SELECTED" => false,
                ]
            ],
            "CUSTOM_SECTION_ID" => $customSectionId,
            "RELATIONS" => [
                "PARENT" => false,
                "CHILD" => [
                    /*[
                        "ENTITY_TYPE_ID" => 4,
                        "IS_CHILDREN_LIST_ENABLED" => 'true',
                    ]*/
                ]
            ],
        ];
    }

    /**
     * TODO implement this method from bitrix core
     * @param int|null $entityTypeId
     * @param array $fields
     */
    protected static function saveConversionMap(?int $entityTypeId, array $fields): void
    {
    }
}