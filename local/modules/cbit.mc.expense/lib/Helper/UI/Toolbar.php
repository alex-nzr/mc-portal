<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Toolbar.php
 * 24.01.2022 23:09
 * ==================================================
 */
namespace Cbit\Mc\Expense\Helper\UI;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\Icon;
use Bitrix\UI\Toolbar\ButtonLocation;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Service\Container;

/**
 * @class Toolbar
 * @package Cbit\Mc\Expense\Helper\UI
 */
class Toolbar
{
    const SPLIT_AMOUNT_BTN_ID = 'expense-split-amount-btn';

    private static bool $splitBtnAdded = false;

    /**
     * @throws \Exception
     */
    public static function addSplitAmountButton(): void
    {
        if (static::$splitBtnAdded)
        {
            return;
        }

        $entityId = (int)Container::getInstance()->getRouter()->getEntityIdFromDetailUrl(
            Context::getCurrent()->getRequest()->getRequestedPage()
        );
        $item = ($entityId > 0) ? Dynamic::getInstance()->getById($entityId) : null;
        if (!empty($item) && Container::getInstance()->getUserPermissions()->canUserSplitAmount($item))
        {
            $splitBtn = new Button([
                'dataset' => [
                    'id' => self::SPLIT_AMOUNT_BTN_ID,
                ],
                "color" => Color::SECONDARY,
                "icon"  => Icon::EDIT,
                "text"  => 'Split'
            ]);
            $splitBtn->setId(self::SPLIT_AMOUNT_BTN_ID);
            \Bitrix\UI\Toolbar\Facade\Toolbar::addButton($splitBtn, ButtonLocation::AFTER_TITLE);
            static::$splitBtnAdded = true;
        }
    }
}