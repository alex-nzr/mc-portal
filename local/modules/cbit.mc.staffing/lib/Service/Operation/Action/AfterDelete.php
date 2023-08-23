<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - AfterDelete.php
 * 28.02.2023 16:14
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\Staffing\Internals\Model\EmploymentNeedTable;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;
use Cbit\Mc\Staffing\Service\Operation\Recruitment;
use Throwable;

/**
 * @class AfterDelete
 * @package Cbit\Mc\Staffing\Service\Operation\Action
 */
class AfterDelete extends Action
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
            $bindings = UserProjectTable::query()
                ->setSelect(['ID'])
                ->where('PROJECT_ID', $item->getId())
                ->fetchAll();
            foreach ($bindings as $binding)
            {
                UserProjectTable::delete($binding['ID']);
            }

            $needs = EmploymentNeedTable::query()
                ->setSelect(['ID'])
                ->where('PROJECT_ID', $item->getId())
                ->fetchAll();
            foreach ($needs as $need)
            {
                EmploymentNeedTable::delete($need['ID']);
            }
        }
        catch (Throwable $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }
}