<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Crm.php
 * 17.01.2023 19:39
 * ==================================================
 */


namespace Cbit\Mc\Subscription\Handler;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Cbit\Mc\Subscription\Service\Container;

/**
 * @class Crm
 * @package Cbit\Mc\Subscription\Handler
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
}