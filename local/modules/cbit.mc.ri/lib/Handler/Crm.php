<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Crm.php
 * 17.01.2023 18:49
 * ==================================================
 */


namespace Cbit\Mc\RI\Handler;

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\ORM\EntityError;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Helper\Notify\Sender;
use Cbit\Mc\RI\Service\Container;

/**
 * Class Crm
 * @package Cbit\Mc\RI\Handler
 */
class Crm
{
    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\EventResult|null
     * @throws \Exception
     */
    public static function changeDetailCardTabs(Event $event): ?EventResult
    {
        if (Container::getInstance()->getRouter()->isDetailPage())
        {
            $tabs = $event->getParameter('tabs');

            foreach ($tabs as $key => $tab)
            {
                if ($tab['id'] !== 'main')
                {
                    unset($tabs[$key]);
                }
            }

            return new EventResult(EventResult::SUCCESS, [
                'tabs' => $tabs,
            ]);
        }

        return null;
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     * @throws \Exception
     */
    public static function onBeforeCrmTimelineCommentAdd(Event $event): \Bitrix\Main\Entity\EventResult
    {
        $requestParams  = Context::getCurrent()->getRequest()->getPostList()->toArray();
        $fields         = $event->getParameter('fields');
        $entityTypeId   = (int)$requestParams['OWNER_TYPE_ID'];
        $itemId         = (int)$requestParams['OWNER_ID'];
        $result         = new \Bitrix\Main\Entity\EventResult();

        if (!is_array($fields))
        {
            return $result;
        }

        if (($entityTypeId === Dynamic::getInstance()->getEntityTypeId()) && ($itemId > 0))
        {
            $item = Dynamic::getInstance()->getById($itemId);
            if ($item !== null)
            {
                if (!Container::getInstance()->getUserPermissions()->checkUpdatePermissions($entityTypeId, $itemId))
                {
                    $result->addError(new EntityError("No permissions to add comment."));
                }
            }
        }

        return $result;
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     * @throws \Exception
     */
    public static function onBeforeCrmTimelineCommentUpdate(Event $event): \Bitrix\Main\Entity\EventResult
    {
        $requestParams  = Context::getCurrent()->getRequest()->getPostList()->toArray();
        $entityTypeId   = (int)$requestParams['OWNER_TYPE_ID'];
        $result         = new \Bitrix\Main\Entity\EventResult();

        if ($entityTypeId === Dynamic::getInstance()->getEntityTypeId())
        {
            $result->addError(new EntityError("Updating of comments in prohibited"));
        }
        return $result;
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     */
    public static function onBeforeCrmTimelineCommentDelete(Event $event): \Bitrix\Main\Entity\EventResult
    {
        $result = new \Bitrix\Main\Entity\EventResult();

        //отмена удаления не работает, так как связь из TimelineBindingTable удаляется раньше записи из TimelineTable
        //удаление связи происходит прямым sql запросом и нет возможности встроиться на событии
        //поэтому комментарий остаётся в таблице, но привязки к элементу теряются

        //$result->addError(new EntityError("Deleting of comments in prohibited"));

        return $result;
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     * @throws \Exception
     */
    public static function onAfterCrmTimelineCommentAdd(Event $event): \Bitrix\Main\Entity\EventResult
    {
        $requestParams  = Context::getCurrent()->getRequest()->getPostList()->toArray();
        $fields         = $event->getParameter('fields');
        $entityTypeId   = (int)$requestParams['OWNER_TYPE_ID'];
        $itemId         = (int)$requestParams['OWNER_ID'];
        $result         = new \Bitrix\Main\Entity\EventResult();

        if (!is_array($fields))
        {
            return $result;
        }

        if (($entityTypeId === Dynamic::getInstance()->getEntityTypeId()) && ($itemId > 0))
        {
            $item = Dynamic::getInstance()->getById($itemId);
            if ($item !== null)
            {
                $createdBy  = $item->getCreatedBy();
                $assignedBy = $item->getAssignedById();
                $text       = (string)$fields['COMMENT'];

                if ((int)$fields['AUTHOR_ID'] === $createdBy)
                {
                    Sender::getInstance($item)->sendNewTimelineCommentMessages($assignedBy, $text, true);
                }
                elseif ((int)$fields['AUTHOR_ID'] === $assignedBy)
                {
                    Sender::getInstance($item)->sendNewTimelineCommentMessages($createdBy, $text);
                }
                else
                {
                    Sender::getInstance($item)->sendNewTimelineCommentMessages($assignedBy, $text);
                    Sender::getInstance($item)->sendNewTimelineCommentMessages($createdBy, $text);
                }
            }
        }

        return $result;
    }
}