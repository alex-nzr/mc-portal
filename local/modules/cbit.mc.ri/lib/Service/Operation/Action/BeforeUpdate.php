<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BeforeUpdate.php
 * 15.12.2022 14:12
 * ==================================================
 */
namespace Cbit\Mc\RI\Service\Operation\Action;


use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Core\Helper\Main\DateTimeCalculator;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Container;
use Exception;

/**
 * Class BeforeUpdate
 * @package Cbit\Mc\RI\Service\Operation\Action
 */
class BeforeUpdate extends Action
{
    public static bool $sendNoteAboutAssignedChanged = false;
    public static bool $sendNoteAboutLaborCostFilled = false;
    public static bool $sendNoteAboutCancelling      = false;
    public static bool $sendNoteAboutCompleting      = false;

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     */
    public function process(Item $item): Result
    {
        $result = new Result();
        try
        {
            $typeId = Dynamic::getInstance()->getTypeId();
            if ($item->isChangedStageId())
            {
                if (!Container::getInstance()->getUserPermissions()->canUserChangeStage($item))
                {
                    throw new Exception(
                        "Stage changing blocked. 
                        If you want to cancel request, use 'Cancel button'.
                        If you want to complete request, you need to fill result description."
                    );
                }

                $stagePrefix = Dynamic::getInstance()->getStatusPrefix($item->getCategoryId());
                $stageCode   = substr($item->getStageId(), strlen($stagePrefix));

                switch ($stageCode)
                {
                    case Constants::DYNAMIC_STAGE_DEFAULT_ASSIGNED:
                        $item->set('UF_CRM_'.$typeId.'_MOVED_TO_ASSIGNED_STAGE', new DateTime());
                        break;
                    case Constants::DYNAMIC_STAGE_DEFAULT_FAIL:
                        static::$sendNoteAboutCancelling = true;
                        break;
                    case Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS:
                        static::$sendNoteAboutCompleting = true;
                        break;
                }

                $item->set('UF_CRM_'.$typeId.'_LAST_STATUS_CHANGE', new DateTime());
            }

            if ($item->isChanged("UF_CRM_".$typeId."_ASSIGNED_BY"))
            {
                $assignedById = $item->get("UF_CRM_".$typeId."_ASSIGNED_BY");
                $item->setAssignedById($assignedById);

                $item->setStageId(
                    Dynamic::getInstance()->getStatusPrefix($item->getCategoryId()) . Constants::DYNAMIC_STAGE_DEFAULT_ASSIGNED
                );

                $item->set(
                    "UF_CRM_". $typeId ."_PER_DIEM",
                    User::getUserPerDiem($assignedById)
                );

                static::$sendNoteAboutAssignedChanged = true;
            }

            if ($item->isChanged("UF_CRM_".$typeId."_DEADLINE"))
            {
                if (!DateTimeCalculator::getInstance()->isTimeSelected($item->get("UF_CRM_".$typeId."_DEADLINE")))
                {
                    throw new Exception('Time of deadline is not selected');
                }
            }

            if ($item->isChanged("UF_CRM_".$typeId."_LABOR_COSTS_PLAN"))
            {
                if ($item->get("UF_CRM_".$typeId."_MOVED_TO_ASSIGNED_STAGE") instanceof DateTime)
                {
                    static::$sendNoteAboutLaborCostFilled = true;
                }
                else
                {
                    throw new Exception('Request was not moved to assigned stage, but planned labor cost filled');
                }
            }

            //File already deleted when this action calling. So this code not works.
            /*if ($item->isChanged("UF_CRM_".$typeId."_ATTACHMENTS"))
            {
                $currentValue = (array)$item->remindActual("UF_CRM_".$typeId."_ATTACHMENTS");
                $newValue     = (array)$item->get("UF_CRM_".$typeId."_ATTACHMENTS");
                $item->set("UF_CRM_".$typeId."_ATTACHMENTS", array_unique(array_merge($currentValue, $newValue)));
            }*/
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }
}