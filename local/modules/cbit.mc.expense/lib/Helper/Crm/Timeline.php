<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Timeline.php
 * 15.02.2023 22:18
 * ==================================================
 */
namespace Cbit\Mc\Expense\Helper\Crm;

use Bitrix\Crm\Timeline\CommentEntry;
use Bitrix\Crm\Timeline\Pusher;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Service\Container;

/**
 * @class Timeline
 * @package Cbit\Mc\Expense\Helper\Crm
 */
class Timeline
{
    /**
     * @param int $entityId
     * @param string $text
     * @param bool $isFixed
     * @return int
     * @throws \Exception
     */
    public static function createComment(int $entityId, string $text, bool $isFixed = false): int
    {
        $entityTypeId = Dynamic::getInstance()->getEntityTypeId();
        $commentParams = [
            'TEXT' => $text,
            'BINDINGS' => [
                [
                    'ENTITY_TYPE_ID' => $entityTypeId,
                    'ENTITY_ID'      => $entityId,
                    'IS_FIXED'       => $isFixed
                ]
            ]
        ];
        $commentId    = (int)CommentEntry::create($commentParams);
        static::sendPullEvent($commentId, $commentParams['BINDINGS']);
        return $commentId;
    }

    /**
     * @param int $timelineEntryId
     * @param array $bindings
     * @return void
     * @throws \Exception
     */
    private static function sendPullEvent(int $timelineEntryId, array $bindings): void
    {
        $historyDataModel = null;

        $timelineEntry = Container::getInstance()->getTimelineEntryFacade()->getById($timelineEntryId);
        if (is_array($timelineEntry))
        {
            $historyDataModel = Container::getInstance()->getTimelineHistoryDataModelMaker()->prepareHistoryDataModel(
                $timelineEntry,
                ['ENABLE_USER_INFO' => true]
            );
        }

        foreach ($bindings as $binding)
        {
            Container::getInstance()->getTimelinePusher()->sendPullEvent(
                $binding['ENTITY_TYPE_ID'],
                $binding['ENTITY_ID'],
                Pusher::ADD_ACTIVITY_PULL_COMMAND,
                $historyDataModel
            );
        }
    }
}