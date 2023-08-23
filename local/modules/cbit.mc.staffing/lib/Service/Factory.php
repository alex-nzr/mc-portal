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
namespace Cbit\Mc\Staffing\Service;

use Bitrix\Crm\Item;
use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Crm\Service\Context;
use Bitrix\Crm\Service\Factory\Dynamic;
use Bitrix\Crm\Service\Operation;
use Cbit\Mc\Staffing\Service\Operation\Action\AfterDelete;

/**
 * Class Factory
 * @package Cbit\Mc\Staffing\Service
 */
class Factory extends Dynamic
{
    public function __construct(Type $type)
    {
        parent::__construct($type);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Add
     */
    public function getAddOperation(Item $item, Context $context = null): Operation\Add
    {
        return parent::getAddOperation($item, $context);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Update
     */
    public function getUpdateOperation(Item $item, Context $context = null): Operation\Update
    {
        return parent::getUpdateOperation($item, $context);
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
            ->addAction(Operation::ACTION_AFTER_SAVE, new AfterDelete());
    }
}