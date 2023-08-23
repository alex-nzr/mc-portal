<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BeforeUpdate.php
 * 20.01.2023 10:12
 * ==================================================
 */
namespace Cbit\Mc\Expense\Service\Operation\Action;


use Bitrix\Crm\Attribute\FieldAttributeType;
use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Helper\Currency\Converter;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\Integration\Pull;
use Exception;

/**
 * @class BeforeUpdate
 * @package Cbit\Mc\Expense\Service\Operation\Action
 */
class BeforeUpdate extends Action
{
    private Result $result;

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function process(Item $item): Result
    {
        $this->result = new Result();
        try
        {
            $typeId = Dynamic::getInstance()->getTypeId();

            if ($item->isChangedStageId())
            {
                if (!Container::getInstance()->getUserPermissions()->canUserChangeStage($item))
                {
                    throw new Exception("Stage changing blocked by permissions");
                }

                if (Dynamic::getInstance()->isItemInApprovedStage($item))
                {
                    $item->set('UF_CRM_'.$typeId.'_APPROVAL_DATE', new DateTime());
                }
                elseif (Dynamic::getInstance()->isItemInRejectStage($item, false))
                {
                    if ((bool)$item->get('UF_CRM_'.$typeId.'_REJECT_COMMENT_ADDED') !== true)
                    {
                        //item can be moved on reject stage only by submit comment form
                        $stageIdBeforeUpdate = $item->remindActual(Item::FIELD_NAME_STAGE_ID);
                        $item->setStageId($stageIdBeforeUpdate);
                        $this->sendRejectCommentEvent($item);
                        throw new Exception("Specify the reason for the reject");
                    }

                    //remove the flag so that script will start again when stage changed to REJECTED
                    $item->set('UF_CRM_'.$typeId.'_REJECT_COMMENT_ADDED', false);
                }
                elseif (Dynamic::getInstance()->isItemInSubmittedStage($item))
                {
                    $this->processInitialAmount($typeId, $item);
                    if ($item->getCurrencyId() !== 'RUB')
                    {
                        $expenseDate = $item->get('UF_CRM_'.$typeId.'_EXPENSE_DATE');
                        $opportunityRUB = Converter::convertToRUB(
                            $item->getCurrencyId(),
                            $item->getOpportunity(),
                            $expenseDate instanceof Date ? $expenseDate : new Date()
                        );
                        $item->setCurrencyId('RUB');
                        $item->setOpportunity($opportunityRUB);
                    }
                }
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
    private function sendRejectCommentEvent(Item $item): void
    {
        if (Pull\Configuration::isAvailable())
        {
            Pull\Event::addToStack(Constants::SHOW_REJECT_REASON_POPUP_ACTION, [
                'itemId' => $item->getId()
            ]);
        }
        else
        {
            $this->result->addError(new Error(Pull\Configuration::getError()));
        }
    }

    /**
     * @param int $typeId
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function processInitialAmount(int $typeId, Item $item): void
    {
        if (Dynamic::getInstance()->isItemInRejectStage($item, true)
            || Dynamic::getInstance()->isItemInFirstStage($item, true)
        ){
            $item->set('UF_CRM_'.$typeId.'_INITIAL_AMOUNT', $item->getOpportunity().'|'.$item->getCurrencyId());
            $item->set('UF_CRM_'.$typeId.'_AMOUNT_REJECTED', '');
            $item->set('UF_CRM_'.$typeId.'_REASON', '');
        }
    }
}