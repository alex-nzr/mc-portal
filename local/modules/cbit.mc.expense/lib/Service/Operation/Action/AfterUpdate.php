<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AfterUpdate.php
 * 20.01.2023 10:12
 * ==================================================
 */
namespace Cbit\Mc\Expense\Service\Operation\Action;


use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Result;

/**
 * @class AfterUpdate
 * @package Cbit\Mc\Expense\Service\Operation\Action
 */
class AfterUpdate extends Action
{
    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function process(Item $item): Result
    {
        return new Result();
    }
}