<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - EditorAdapter.php
 * 19.02.2023 01:16
 * ==================================================
 */
namespace Cbit\Mc\Expense\Service;

use Bitrix\Crm\EO_Status_Collection;
use Bitrix\Crm\Field;
use Bitrix\Crm\Item;
use Cbit\Mc\Core\Internals\Contract\IEditorConfig;
use CCrmFieldInfoAttr;
use Cbit\Mc\Core\Service\EntityEditor\FieldManager;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Internals\Control\EventManager;
use Cbit\Mc\Expense\Internals\EditorConfig;

/**
 * @class EditorAdapter
 * @package Cbit\Mc\Expense\Service
 */
class EditorAdapter extends \Cbit\Mc\Core\Service\EditorAdapter
{
    public const FIELD_OPPORTUNITY = 'OPPORTUNITY_WITH_CURRENCY';
    public const FIELD_CLIENT = 'CLIENT';
    public const FIELD_CLIENT_DATA_NAME = 'CLIENT_DATA';
    public const FIELD_PRODUCT_ROW_SUMMARY = 'PRODUCT_ROW_SUMMARY';

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\EO_Status_Collection $stages
     * @param array $componentParameters
     * @return \Cbit\Mc\Expense\Service\EditorAdapter
     * @throws \Exception
     */
    public function processByItem(Item $item, EO_Status_Collection $stages, array $componentParameters = []): EditorAdapter
    {
        $this->crmContext->setItem($item);
        $categoryCode = Dynamic::getInstance()->getCategoryCodeById($item->getCategoryId());
        $editorConfig = EditorConfig\Factory::getInstance($this->typeId, $this->entityTypeId)->createConfig($categoryCode);
        $this->markReadonlyFields($editorConfig);
        $this->markHiddenFields($editorConfig);
        $this->processAdditionalFields($editorConfig);
        EventManager::sendEntityDetailsContextReadyEvent();

        return parent::processByItem($item, $stages, $componentParameters);
    }
}