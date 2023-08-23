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
namespace Cbit\Mc\Staffing\Handler;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Service\Container;
use function bitrix_sessid_get;

/**
 * Class Crm
 * @package Cbit\Mc\Staffing\Handler
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
        if (Container::getInstance()->getRouter()->isInDynamicTypeSection())
        {
            $tabs = $event->getParameter('tabs');

            foreach ($tabs as $key => $tab)
            {
                if ($tab['id'] !== 'main')
                {
                    unset($tabs[$key]);
                }
            }

            $tabs[] = [
                'id'   => 'project_team',
                'name' => 'Project team',
                'loader' => [
                    'serviceUrl' => '/local/components/cbit/mc.staffing.project-team/lazyload.ajax.php?&site='.SITE_ID.'&'. bitrix_sessid_get(),
                    'componentData' => [
                        'template'   => '.default',
                        'PROJECT_ID' => Container::getInstance()->getRouter()->getEntityIdFromDetailUrl(
                            Container::getInstance()->getRouter()->getCurPage()
                        )
                    ]
                ],
                'enabled' => true
            ];

            return new EventResult(EventResult::SUCCESS, [
                'tabs' => $tabs,
            ]);
        }

        return null;
    }
}