<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Main.php
 * 30.12.2022 20:22
 * ==================================================
 */

namespace Cbit\Mc\RI\Handler;

use Bitrix\Main\Context;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Container;

class Main
{
    /**
     * @return void
     * @throws \Exception
     */
    public static function changeItemStageOnFirstView(): void
    {
        $router = Container::getInstance()->getRouter();
        if ($router->isDetailPage())
        {
            $entityId     = Container::getInstance()->getRouter()->getEntityIdFromDetailUrl(
                Context::getCurrent()->getRequest()->getRequestedPage()
            );
            if ($entityId > 0)
            {
                $item = Dynamic::getInstance()->getById($entityId);
                if (!empty($item))
                {
                    $isOnFirstStage            = Dynamic::getInstance()->isItemInFirstStage($item);
                    $isItemOpenedByCoordinator = Dynamic::getInstance()->isItemOpenedByCoordinator($item);

                    if ($isOnFirstStage && $isItemOpenedByCoordinator)
                    {
                        Dynamic::getInstance()->moveItemToStage($item, Constants::DYNAMIC_STAGE_DEFAULT_REVIEW);
                    }
                }
            }
        }
    }
}