<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Sender.php
 * 17.02.2023 11:38
 * ==================================================
 */
namespace Cbit\Mc\RI\Helper\Notify;

use Bitrix\Crm\Item;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Helper\Im;
use Cbit\Mc\Core\Helper\Mail;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Helper\Main\User;
use Cbit\Mc\RI\Service\Container;
use Cbit\Mc\RI\Service\Router;

/**
 * @class Sender
 * @package Cbit\Mc\RI\Helper\Notify
 */
class Sender
{
    protected static array $instances = [];
    protected string $riBoxEmail = 'R&I@yakov.partners';
    protected Item $item;
    protected Router $router;
    protected ?string $itemTitle;
    protected string $localUrl;
    protected string $fullUrl;

    /**
     * @param \Bitrix\Crm\Item $item
     * @throws \Exception
     */
    private function __construct(Item $item)
    {
        Container::getInstance()->getLocalization()->loadMessages();
        $this->item      = $item;
        $this->router    = Container::getInstance()->getRouter();
        $this->itemTitle = $this->item->getTitle();
        $this->localUrl  = $this->router->getItemDetailUrlById($this->item->getId());
        $this->fullUrl   = $this->router->getItemFullPath($this->item->getId());
    }
    private function __clone(){}
    public function __wakeup(){}

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Cbit\Mc\RI\Helper\Notify\Sender|null
     * @throws \Exception
     */
    public static function getInstance(Item $item): ?Sender
    {
        if (empty(static::$instances[$item->getId()]))
        {
            static::$instances[$item->getId()] = new static($item);
        }
        return static::$instances[$item->getId()];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function sendNewItemAddedMessages(): void
    {
        //requester
        Im\Notify::createLinkNotify(
            $this->item->getCreatedBy(),
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_TITLE'),
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_TEXT_REQUESTER', [
                '#TITLE#' => $this->itemTitle
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            $this->item->getCreatedBy(),
            1,
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_TITLE'),
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_LINK_REQUESTER', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#' => $this->itemTitle
            ]),
        );

        //coordinator
        Im\Notify::createLinkNotify(
            Configuration::getInstance()->getCurrentCoordinatorId(),
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_TITLE'),
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_TEXT_COORDINATOR', [
                '#TITLE#' => $this->itemTitle
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            Configuration::getInstance()->getCurrentCoordinatorId(),
            1,
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_TITLE'),
            Loc::getMessage('NEW_ITEM_CREATED_EVENT_LINK_COORDINATOR', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#' => $this->itemTitle
            ]),
            $this->riBoxEmail
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function sendUnassignedItemMessages(): void
    {
        $createdAt = $this->item->getCreatedTime() instanceof Date ? $this->item->getCreatedTime()->format('d.m.Y H:i:s') : '';

        //coordinator
        Im\Notify::createLinkNotify(
            Configuration::getInstance()->getCurrentCoordinatorId(),
            Loc::getMessage('NEW_ITEM_UNASSIGNED_EVENT_TITLE', [
                '#TITLE#' => $this->itemTitle
            ]),
            Loc::getMessage('NEW_ITEM_UNASSIGNED_EVENT_TEXT', [
                '#TITLE#' => $this->itemTitle,
                '#DATE#'  => $createdAt
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            Configuration::getInstance()->getCurrentCoordinatorId(),
            1,
            Loc::getMessage('NEW_ITEM_UNASSIGNED_EVENT_TITLE'),
            Loc::getMessage('NEW_ITEM_UNASSIGNED_EVENT_LINK', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#' => $this->itemTitle,
                '#DATE#'  => $createdAt
            ]),
            $this->riBoxEmail
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function sendAssignedAddedToItemMessages(): void
    {
        $assignedName = User::getUserNameById((int)$this->item->getAssignedById());

        //requester
        Im\Notify::createLinkNotify(
            $this->item->getCreatedBy(),
            Loc::getMessage('ITEM_ASSIGNED_ADDED_EVENT_TITLE', [
                '#TITLE#' => $this->itemTitle
            ]),
            Loc::getMessage('ITEM_ASSIGNED_ADDED_EVENT_TEXT', [
                '#TITLE#' => $this->itemTitle,
                '#NAME#'  => $assignedName
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            $this->item->getCreatedBy(),
            1,
            Loc::getMessage('ITEM_ASSIGNED_ADDED_EVENT_TITLE'),
            Loc::getMessage('ITEM_ASSIGNED_ADDED_EVENT_LINK', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#' => $this->itemTitle,
                '#NAME#'  => $assignedName
            ])
        );
    }

    /**
     * @param \Bitrix\Main\Type\Date $planDate
     * @return void
     * @throws \Exception
     */
    public function sendLaborCostFilledMessages(Date $planDate): void
    {
        //requester
        Im\Notify::createLinkNotify(
            $this->item->getCreatedBy(),
            Loc::getMessage('ITEM_LABOR_COST_FILLED_EVENT_TITLE', [
                '#TITLE#' => $this->itemTitle
            ]),
            Loc::getMessage('ITEM_LABOR_COST_FILLED_EVENT_TEXT', [
                '#TITLE#' => $this->itemTitle,
                '#DATE#'  => $planDate->toString()
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            $this->item->getCreatedBy(),
            1,
            Loc::getMessage('ITEM_LABOR_COST_FILLED_EVENT_TITLE'),
            Loc::getMessage('ITEM_LABOR_COST_FILLED_EVENT_LINK', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#' => $this->itemTitle,
                '#DATE#'  => $planDate->toString()
            ])
        );
    }

    /**
     * @param int $toUser
     * @param string $text
     * @param bool $sendToRiBox
     * @return void
     * @throws \Exception
     */
    public function sendNewTimelineCommentMessages(int $toUser, string $text, bool $sendToRiBox = false): void
    {
        Im\Notify::createLinkNotify(
            $toUser,
            Loc::getMessage('ITEM_COMMENT_ADDED_EVENT_TITLE', [
                '#TITLE#' => $this->itemTitle
            ]),
            Loc::getMessage('ITEM_COMMENT_ADDED_EVENT_TEXT', [
                '#TITLE#' => $this->itemTitle,
                '#TEXT#'  => $text
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            $toUser,
            1,
            Loc::getMessage('ITEM_COMMENT_ADDED_EVENT_TITLE'),
            Loc::getMessage('ITEM_COMMENT_ADDED_EVENT_LINK', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#' => $this->itemTitle,
                '#TEXT#'  => $text
            ]),
            $sendToRiBox ? $this->riBoxEmail : ''
        );
    }

    /**
     * @param int $toUser
     * @param string $userType
     * @param string $userName
     * @param string $reason
     * @param string $comment
     * @param bool $cancelledByRequester
     * @return void
     * @throws \Exception
     */
    public function sendItemCancelledMessages(
        int $toUser, string $userType,
        string $userName, string $reason,
        string $comment, bool $cancelledByRequester = false
    ): void
    {
        Im\Notify::createLinkNotify(
            $toUser,
            Loc::getMessage('ITEM_CANCELLED_EVENT_TITLE', [
                '#TITLE#' => $this->itemTitle
            ]),
            Loc::getMessage('ITEM_CANCELLED_EVENT_TEXT', [
                '#TYPE#'    => $userType,
                '#NAME#'    => $userName,
                '#TITLE#'   => $this->itemTitle,
                '#REASON#'  => $reason,
                '#COMMENT#' => $comment,
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            $toUser,
            1,
            Loc::getMessage('ITEM_CANCELLED_EVENT_TITLE'),
            Loc::getMessage('ITEM_CANCELLED_EVENT_LINK', [
                '#HREF#'  => $this->fullUrl,
                '#TYPE#'    => $userType,
                '#NAME#'    => $userName,
                '#TITLE#'   => $this->itemTitle,
                '#REASON#'  => $reason,
                '#COMMENT#' => $comment,
            ]),
            $cancelledByRequester ? $this->riBoxEmail : ''
        );
    }

    /**
     * @param int $toUser
     * @param string $result
     * @param array $fileIds
     * @return void
     * @throws \Exception
     */
    public function sendItemCompletedMessages(int $toUser, string $result, array $fileIds = []): void
    {
        Im\Notify::createLinkNotify(
            $toUser,
            Loc::getMessage('ITEM_COMPLETED_EVENT_TITLE', [
                '#TITLE#' => $this->itemTitle
            ]),
            Loc::getMessage('ITEM_COMPLETED_EVENT_TEXT', [
                '#TITLE#'   => $this->itemTitle,
                '#RESULT#'  => $result,
            ]),
            $this->localUrl
        );

        Mail\Notify::sendImmediate(
            $toUser,
            1,
            Loc::getMessage('ITEM_COMPLETED_EVENT_TITLE'),
            Loc::getMessage('ITEM_COMPLETED_EVENT_LINK', [
                '#HREF#'  => $this->fullUrl,
                '#TITLE#'   => $this->itemTitle,
                '#RESULT#'  => $result,
            ]),
            '',
            $fileIds
        );
    }
}