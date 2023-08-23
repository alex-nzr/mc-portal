<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AfterAdd.php
 * 17.02.2023 11:12
 * ==================================================
 */
namespace Cbit\Mc\RI\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\RI\Helper\Notify\Sender;
use Exception;

/**
 * @class AfterAdd
 * @package Cbit\Mc\RI\Service\Operation\Action
 */
class AfterAdd extends Action
{
    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function process(Item $item): Result
    {
        $result = new Result();
        try
        {
            Sender::getInstance($item)->sendNewItemAddedMessages();
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }
}