<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Factory.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Subscription\Service;

use Bitrix\Crm\Item;
use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Crm\Service\Context;
use Bitrix\Crm\Service\Factory\Dynamic;
use Bitrix\Crm\Service\Operation;
use Cbit\Mc\Subscription\Item\Expense;
use Cbit\Mc\Subscription\Service\Operation\Action\AfterUpdate;
use Cbit\Mc\Subscription\Service\Operation\Action\BeforeAdd;
use Cbit\Mc\Subscription\Service\Operation\Action\BeforeUpdate;
use Cbit\Mc\Subscription\Service\Operation\Action\BeforeDeletion;

/**
 * Class Factory
 * @package Cbit\Mc\Subscription\Service
 */
class Factory extends Dynamic
{
    /**
     * @param \Bitrix\Crm\Model\Dynamic\Type $type
     */
    public function __construct(Type $type)
    {
        parent::__construct($type);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Add
     * @throws \Exception
     */
    public function getAddOperation(Item $item, Context $context = null): Operation\Add
    {
        return parent::getAddOperation($item, $context)
            ->addAction( Operation::ACTION_BEFORE_SAVE , new BeforeAdd() );
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Update
     * @throws \Exception
     */
    public function getUpdateOperation(Item $item, Context $context = null): Operation\Update
    {
        $operation = parent::getUpdateOperation($item, $context);
        return $operation
            ->addAction( Operation::ACTION_BEFORE_SAVE , new BeforeUpdate() )
            ->addAction( Operation::ACTION_AFTER_SAVE , new AfterUpdate() );
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Delete
     * @throws \Exception
     */
    public function getDeleteOperation(Item $item, Context $context = null): Operation\Delete
    {
        return parent::getDeleteOperation($item, $context)
            ->addAction(Operation::ACTION_BEFORE_SAVE, new BeforeDeletion());
    }

    /**
     * @param array $data
     * @return \Bitrix\Crm\Item
     * @throws \Exception
     */
    public function createItem(array $data = []): Item
    {
        return parent::createItem($data);
    }

    /**
     * @return \Bitrix\Crm\Service\EditorAdapter
     * @throws \Exception
     */
    public function getEditorAdapter(): \Bitrix\Crm\Service\EditorAdapter
    {
        if (!$this->editorAdapter)
        {
            $this->editorAdapter = new EditorAdapter($this->getFieldsCollection(), $this->getDependantFieldsMap());

            $this->editorAdapter
                ->setTypeId($this->getType()->getId())
                ->setEntityTypeId($this->getEntityTypeId())
                ->setCrmContext(Container::getInstance()->getContext());

            if ($this->isClientEnabled())
            {
                $this->editorAdapter->addClientField($this->getFieldCaption(EditorAdapter::FIELD_CLIENT));
            }
            if ($this->isLinkWithProductsEnabled())
            {
                $this->editorAdapter->addOpportunityField(
                    $this->getFieldCaption(EditorAdapter::FIELD_OPPORTUNITY),
                    $this->isPaymentsEnabled()
                );
                $this->editorAdapter->addProductRowSummaryField(
                    $this->getFieldCaption(EditorAdapter::FIELD_PRODUCT_ROW_SUMMARY)
                );
            }
        }

        return $this->editorAdapter;
    }
}