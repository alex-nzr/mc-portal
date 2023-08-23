<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BeforeDeletion.php
 * 20.01.2023 10:12
 * ==================================================
 */

namespace Cbit\Mc\Partner\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Result;

/**
 * @class BeforeDeletion
 * @package Cbit\Mc\Partner\Service\Operation\Action
 */
class BeforeDeletion extends Action
{
    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     */
    public function process(Item $item): Result
    {
        return new Result();
    }
}