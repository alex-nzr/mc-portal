<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Main.php
 * 26.01.2023 21:09
 * ==================================================
 */


namespace Cbit\Mc\Expense\Handler;

use Bitrix\Main\Context;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Service\Container;

/**
 * @class Main
 * @package Cbit\Mc\Expense\Handler
 */
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
                    $isItemOnSubmittedStage = Dynamic::getInstance()->isItemInSubmittedStage($item);
                    $isItemOpenedByAssigned = Dynamic::getInstance()->isItemOpenedByAssigned($item);
                    if ($isItemOnSubmittedStage && $isItemOpenedByAssigned)
                    {
                        Dynamic::getInstance()->moveItemToReviewStage($item);
                    }
                }
            }
        }
    }
}