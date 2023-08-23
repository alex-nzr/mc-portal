<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BeforeAdd.php
 * 20.01.2023 10:12
 * ==================================================
 */

namespace Cbit\Mc\Expense\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Helper\Im\Notify;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Expense\Config\Configuration;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Helper\Main\User;
use Cbit\Mc\Expense\Service\Container;
use Exception;

/**
 * @class BeforeAdd
 * @package Cbit\Mc\Expense\Service\Operation\Action
 */
class BeforeAdd extends Action
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        Container::getInstance()->getLocalization()->loadMessages();
    }

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
            $categoryId = $item->getCategoryId();

            $this->checkExpenseDate($typeId, $item);

            $typeOfRequest = Configuration::getInstance()->getTypeOfRequestByCategoryId((int)$item->getCategoryId());

            $item->setTitle( $typeOfRequest . ' #' . ($this->getLastIdFromDb() + 1));
            $item->setAssignedById(99999);
            $item->set('UF_CRM_'.$typeId.'_PARTICIPANTS_TOTAL', $this->getParticipantsCount($item));

            if ($categoryId > 0)
            {
                $categoryCode = Dynamic::getInstance()->getCategoryCodeById($categoryId);

                switch ($categoryCode)
                {
                    case Constants::DYNAMIC_CATEGORY_DEFAULT_CODE:
                        $this->checkMealTeamEvent($typeId, $item);
                        break;
                    case Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE:
                        $item->set(
                            'UF_CRM_'.$typeId.'_CHARGE_CODE',
                            Configuration::getInstance()->getDefaultTYBChargeCode()
                        );
                        break;
                }
            }

            $item->set('UF_CRM_'.$typeId.'_PSSS', $this->getPsssFromCC($item->get('UF_CRM_'.$typeId.'_CHARGE_CODE')));

            $originalRequestId = $this->findSameItem($item);
            if (!empty($originalRequestId))
            {
                $item->set('UF_CRM_'.$typeId.'_DUPLICATE_OF', $originalRequestId);
            }

            $item->set('UF_CRM_' . $typeId . '_REQUESTER_FMNO', User::getUserFMNOById($item->getCreatedBy()));
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return int
     * @throws \Exception
     */
    private function getParticipantsCount(Item $item): int
    {
        $typeId = Dynamic::getInstance()->getTypeId();

        $participantsTotal = 1;
        $participantsInternal = $item->get('UF_CRM_'.$typeId.'_PARTICIPANTS_INTERNAL');
        $participantsExternal = $item->get('UF_CRM_'.$typeId.'_PARTICIPANTS_EXTERNAL');
        if (is_array($participantsInternal))
        {
            $participantsTotal += count($participantsInternal);
        }

        if (is_array($participantsExternal))
        {
            $participantsTotal += count($participantsExternal);
        }

        return $participantsTotal;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return int|null
     * @throws \Exception
     */
    private function findSameItem(Item $item): ?int
    {
        $typeId = Dynamic::getInstance()->getTypeId();

        $items = Dynamic::getInstance()->select(
            ['ID'],
            [
                'UF_CRM_'.$typeId.'_EXPENSE_DATE' => $item->get('UF_CRM_'.$typeId.'_EXPENSE_DATE'),
                'UF_CRM_'.$typeId.'_CHARGE_CODE'  => $item->get('UF_CRM_'.$typeId.'_CHARGE_CODE'),
                'OPPORTUNITY'                     => $item->getOpportunity(),
                'CURRENCY_ID'                     => $item->getCurrencyId(),
                'CREATED_BY'                      => $item->getCreatedBy(),
            ]
        );

        return !empty($items) ? (current($items))->getId() : null;
    }

    /**
     * @param int $projectId
     * @return bool
     * @throws \Exception
     */
    private function getPsssFromCC(int $projectId): bool
    {
        $staffingTypeId       = Configuration::getInstance()->getStaffingTypeId();
        $staffingEntityTypeId = Configuration::getInstance()->getStaffingEntityTypeId();
        $staffingFactory      = $staffingEntityTypeId ? Container::getInstance()->getFactory($staffingEntityTypeId) : null;

        if ($staffingFactory !== null)
        {
            $item = $staffingFactory->getDataClass()::query()
                ->setFilter(['ID' => $projectId])
                ->setSelect(['UF_CRM_'.$staffingTypeId.'_PSSS'])
                ->fetchObject();

            if ($item !== null)
            {
                return (bool)$item->get('UF_CRM_'.$staffingTypeId.'_PSSS');
            }
        }
        return false;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getLastIdFromDb(): int
    {
        $lastItem = Dynamic::getInstance()->getItemFactory()->getDataClass()::query()
            ->setSelect(['ID'])
            ->setOrder(['ID' => 'DESC'])
            ->setLimit(1)
            ->fetch();

        if (is_array($lastItem))
        {
            return (int)$lastItem['ID'];
        }
        return 0;
    }

    /**
     * @param int $typeId
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function checkExpenseDate(int $typeId, Item $item): void
    {
        $expenseDate = $item->get('UF_CRM_'.$typeId.'_EXPENSE_DATE');
        if ($expenseDate instanceof Date)
        {
            $dateToCheck = new Date;
            if ((int)$expenseDate->format('Y') < (int)$dateToCheck->format('Y'))
            {
                throw new Exception(Loc::getMessage('ITEM_ADD_YEAR_ERROR'));
            }

            $creatorTenureCompany = User::getUserTenureCompanyArrayById($item->getCreatedBy());
            if (array_key_exists('Y', $creatorTenureCompany) && $creatorTenureCompany['Y'] < 1)
            {
                $monthsInCompany = array_key_exists('M', $creatorTenureCompany) ? $creatorTenureCompany['M'] : 0;
                $dateToCheck = (new Date)->add('-'.$monthsInCompany.' months');

                if ($expenseDate < $dateToCheck)
                {
                    throw new Exception(Loc::getMessage('ITEM_ADD_TENURE_ERROR'));
                }
            }
        }
    }

    /**
     * @param int $typeId
     * @param \Bitrix\Crm\Item $item
     * @return void
     * @throws \Exception
     */
    private function checkMealTeamEvent(int $typeId, Item $item): void
    {
        $categoryOfReceiptId = $item->get('UF_CRM_'.$typeId.'_CATEGORY_OF_RECEIPT');
        if ($categoryOfReceiptId > 0)
        {
            $categoryOfReceiptText = UserField::getUfListValueById($categoryOfReceiptId);
            if ($categoryOfReceiptText === Constants::MEAL_TEAM_EVENT_RECEIPT)
            {
                $userEmploymentType = User::getUserCspOspById($item->getCreatedBy());
                $start = new Date();
                $text  = '';
                switch ($userEmploymentType)
                {
                    case \Cbit\Mc\Core\Config\Constants::USER_EMPLOYMENT_TYPE_OSP:
                        $start->setDate((int)$start->format('Y'), (int)$start->format('m'), 1);
                        $text = Loc::getMessage('HAS_MEAL_TEAM_EVENT_IN_MONTH');
                        break;
                    case \Cbit\Mc\Core\Config\Constants::USER_EMPLOYMENT_TYPE_CSP:
                        $day = (int)date('w') - 1;//php starts week from sunday, that's why need to minus 1 day
                        $start = Date::createFromTimestamp(strtotime('-'.$day.' days'));
                        $text = Loc::getMessage('HAS_MEAL_TEAM_EVENT_IN_WEEK');
                        break;
                }

                $requests = Dynamic::getInstance()->select(['ID'], [
                    '>='.Item::FIELD_NAME_CREATED_TIME       => $start,
                    'UF_CRM_'.$typeId.'_CATEGORY_OF_RECEIPT' => $categoryOfReceiptId
                ]);

                if (!empty($requests))
                {
                    Notify::createTextNotify(
                        $item->getCreatedBy(),
                        'Note',
                        $text,
                    );
                }
            }
        }
    }
}