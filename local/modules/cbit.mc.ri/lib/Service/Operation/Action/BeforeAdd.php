<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BeforeAdd.php
 * 26.12.2022 15:35
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Helper\Main\DateTimeCalculator;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Helper;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Exception;

/**
 * @class BeforeAdd
 * @package Cbit\Mc\RI\Service\Operation\Action
 */
class BeforeAdd extends Action
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
            $typeId = Dynamic::getInstance()->getTypeId();

            if (!DateTimeCalculator::getInstance()->isTimeSelected($item->get("UF_CRM_".$typeId."_DEADLINE")))
            {
                throw new Exception('Time of deadline is not selected');
            }

            $defaultAssignedId = (int)Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_DEFAULT_ASSIGNED_ID, 99999);

            $item->setAssignedById($defaultAssignedId);
            $item->set('UF_CRM_'.$typeId."_ASSIGNED_BY", $defaultAssignedId);

            $item->setObservers([$item->getCreatedBy()]);
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }
}