<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AfterUpdate.php
 * 15.12.2022 14:12
 * ==================================================
 */
namespace Cbit\Mc\RI\Service\Operation\Action;


use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Helper\Main\DateTimeCalculator;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Helper\Main\User;
use Cbit\Mc\RI\Helper\Notify\Sender;
use Cbit\Mc\RI\Service\Container;
use Cbit\Mc\RI\Service\Integration\Pull;
use Exception;

/**
 * Class AfterUpdate
 * @package Cbit\Mc\RI\Service\Operation\Action
 */
class AfterUpdate extends Action
{
    /**
     * @var \Bitrix\Main\Result
     */
    private Result $result;

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     */
    public function process(Item $item): Result
    {
        $this->result = new Result();
        try
        {
            Container::getInstance()->getLocalization()->loadMessages();

            $this->sendScoringActionEvent($item);

            if (BeforeUpdate::$sendNoteAboutAssignedChanged === true)
            {
                Sender::getInstance($item)->sendAssignedAddedToItemMessages();
            }

            if (BeforeUpdate::$sendNoteAboutLaborCostFilled === true)
            {
                $this->sendNoteAboutLaborCostFilled($item);
            }

            if (BeforeUpdate::$sendNoteAboutCancelling === true)
            {
                $this->sendNoteAboutCancelling($item);
            }

            if (BeforeUpdate::$sendNoteAboutCompleting === true)
            {
                $this->sendNoteAboutCompleting($item);
            }
        }
        catch(Exception $e)
        {
            $this->result->addError(new Error($e->getMessage()));
        }

        return $this->result;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function sendScoringActionEvent(Item $item): void
    {
        if (Dynamic::getInstance()->isItemInSuccessStage($item))
        {
            if (Container::getInstance()->getContext()->getUserId() === (int)$item->getCreatedBy())
            {
                if (Pull\Configuration::isAvailable())
                {
                    Pull\Event::addToStack(Constants::SHOW_SCORING_POPUP_ACTION, [
                        'itemId' => $item->getId()
                    ]);
                }
                else
                {
                    $this->result->addError(new Error(Pull\Configuration::getError()));
                }
            }
        }
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function sendNoteAboutLaborCostFilled(Item $item): void
    {
        $typeId          = Dynamic::getInstance()->getTypeId();
        $planHours       = $item->get("UF_CRM_".$typeId."_LABOR_COSTS_PLAN");
        $movedToAssigned = $item->get("UF_CRM_".$typeId."_MOVED_TO_ASSIGNED_STAGE");
        $completeDate    = DateTimeCalculator::getInstance()->add($movedToAssigned, (int)$planHours*3600);

        Sender::getInstance($item)->sendLaborCostFilledMessages($completeDate);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function sendNoteAboutCancelling(Item $item): void
    {
        $typeId     = Dynamic::getInstance()->getTypeId();
        $userId     = Container::getInstance()->getContext()->getUserId();
        $createdBy  = (int)$item->getCreatedBy();
        $assignedBy = (int)$item->getAssignedById();
        $cancelledByRequester = ($userId === $createdBy);
        $userName   = User::getUserNameById( $cancelledByRequester ? $createdBy : $assignedBy );
        $userType   = $cancelledByRequester
                    ? Loc::getMessage('ITEM_CANCELLED_EVENT_CREATED_BY')
                    : Loc::getMessage('ITEM_CANCELLED_EVENT_ASSIGNED');

        Sender::getInstance($item)->sendItemCancelledMessages(
            $cancelledByRequester ? $assignedBy : $createdBy,
            $userType,
            $userName,
            UserField::getUfListValueById((int)$item->get('UF_CRM_'.$typeId.'_CANCEL_REASON')),
            $item->get('UF_CRM_'.$typeId.'_CANCEL_COMMENT'),
            $cancelledByRequester
        );
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function sendNoteAboutCompleting(Item $item): void
    {
        $typeId = Dynamic::getInstance()->getTypeId();
        Sender::getInstance($item)->sendItemCompletedMessages(
            (int)$item->getCreatedBy(),
            $item->get('UF_CRM_'.$typeId.'_RESULT_DESCRIPTION') ?? '',
            $item->get('UF_CRM_'.$typeId.'_RESULT_ATTACHMENTS') ?? []
        );
    }
}