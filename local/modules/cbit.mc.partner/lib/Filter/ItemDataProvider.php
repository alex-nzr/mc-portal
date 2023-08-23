<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ItemDataProvider.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Partner\Filter;

use Bitrix\Crm\Filter\ItemDataProvider as CrmItemDataProvider;
use Cbit\Mc\Partner\Entity\Dynamic;
use Cbit\Mc\Partner\Internals\EditorConfig;

/**
 * Class ItemDataProvider
 * @package Cbit\Mc\Partner\Filter
 */
class ItemDataProvider extends CrmItemDataProvider
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getGridColumns(): array
    {
        $typeId       = Dynamic::getInstance()->getTypeId();
        $entityTypeId = Dynamic::getInstance()->getEntityTypeId();
        $editorConfig = EditorConfig\Factory::getInstance($typeId, $entityTypeId)->createConfig(
            EditorConfig\ConfigType::COMMON
        );

        return array_filter(parent::getGridColumns(), function($item) use($editorConfig){
            return !in_array($item['id'] , $editorConfig->getHiddenFields());
        });
    }

    /**
     * @return array|\Bitrix\Main\Filter\Field[]
     * @throws \Exception
     */
    public function prepareFields(): array
    {
        $typeId       = Dynamic::getInstance()->getTypeId();
        $entityTypeId = Dynamic::getInstance()->getEntityTypeId();
        $editorConfig = EditorConfig\Factory::getInstance($typeId, $entityTypeId)->createConfig(
            EditorConfig\ConfigType::COMMON
        );
        return array_filter(parent::prepareFields(), function($field) use($editorConfig){
            return !in_array($field->getId(), $editorConfig->getHiddenFields());
        });
    }
}