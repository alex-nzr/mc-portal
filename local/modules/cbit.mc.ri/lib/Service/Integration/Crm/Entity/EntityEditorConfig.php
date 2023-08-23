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

namespace Cbit\Mc\RI\Service\Integration\Crm\Entity;

use Bitrix\Crm\Attribute\FieldAttributeManager;
use Bitrix\Crm\Entity\EntityEditorConfigScope;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Internals\Contract\IEditorConfig;
use Cbit\Mc\Core\Service\EntityEditor\FieldManager;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Internals\EditorConfig;
use CCrmOwnerType;
use Exception;

/**
 * Class EntityEditorConfig
 * @package Cbit\Mc\RI\Service\Integration\Crm\Entity
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
            return CCrmOwnerType::ResolveName($this->entityTypeID) .'_details_C'. $categoryId;
        }
        return parent::getConfigId();
    }

    /**
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     */
    public static function setTypeCardConfig(int $entityTypeId): Result
    {
        $result = new Result();
        try
        {
            $typeId = Configuration::getInstance()->getTypeIdFromOption();
            if ($typeId <= 0)
            {
                throw new Exception('Error in '.__METHOD__.': typeId must be greater than 0');
            }

            $userId     = !empty($GLOBALS['USER']) ? CurrentUser::get()->getId() : 1;
            $scope      = EntityEditorConfigScope::COMMON;
            $categories = ItemCategoryTable::query()
                ->setSelect(['ID', 'CODE', 'IS_DEFAULT'])
                ->where('ENTITY_TYPE_ID', $entityTypeId)
                ->fetchCollection();

            //clean attributes before save new configuration
            FieldAttributeManager::deleteByOwnerType($entityTypeId);

            foreach ($categories as $category)
            {
                $categoryId = $category->getId();
                $code = (string)$category->get('CODE');
                if (empty($code) && ($category->get('IS_DEFAULT') === true))
                {
                    $code = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
                }

                $editorConfig = EditorConfig\Factory::getInstance($typeId, $entityTypeId)->createConfig($code);

                if ($editorConfig instanceof IEditorConfig)
                {
                    $cardConfiguration = $editorConfig->getEditorConfigTemplate();
                    if (!empty($cardConfiguration))
                    {
                        $extras = [
                            'CATEGORY_ID' => $categoryId,
                        ];

                        $crmConfig = new static($entityTypeId, $userId, $scope, $extras);
                        $data      = $crmConfig->normalize($cardConfiguration);
                        $data      = $crmConfig->sanitize($data);
                        $crmConfig->set($data);

                        //todo доработать под сохранение обязательных полей для определённых стадий
                        foreach ($editorConfig->getRequiredFields() as $requiredFieldName)
                        {
                            FieldManager::getInstance($entityTypeId)->saveFieldAsRequired($requiredFieldName, $categoryId);
                        }
                    }
                    else
                    {
                        $result->addError(new Error("Can not find card configuration for category '$code'"));
                    }
                }
                else
                {
                    $result->addError(new Error("Can not create editorConfig object"));
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