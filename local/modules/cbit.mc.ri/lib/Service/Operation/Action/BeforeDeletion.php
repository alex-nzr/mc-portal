<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BeforeDeletion.php
 * 15.12.2022 15:02
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * @class BeforeDeletion
 * @package Cbit\Mc\RI\Service\Operation\Action
 */
class BeforeDeletion extends Action
{
    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     */
    public function process(Item $item): Result
    {
        return (new Result())->addError(new Error(
            'Deletion is prohibited. Please, use cancelling button or change stage of request'
        ));
    }
}