<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Toolbar.php
 * 15.12.2022 20:39
 * ==================================================
 */
namespace Cbit\Mc\RI\Helper\UI;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\Icon;
use Bitrix\UI\Toolbar\ButtonLocation;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Access\Permission;
use Cbit\Mc\RI\Service\Container;

/**
 * Class Toolbar
 * @package Cbit\Mc\RI\Helper\UI
 */
class Toolbar
{
    const CANCEL_REQUEST_BTN_ID = 'ri-cancel-request-btn';

    private static bool $cancelBtnAdded = false;

    /**
     * @throws \Exception
     */
    public static function addCancelButton(): void
    {
        if (static::$cancelBtnAdded)
        {
            return;
        }

        $entityId = (int)Container::getInstance()->getRouter()->getEntityIdFromDetailUrl(
            Context::getCurrent()->getRequest()->getRequestedPage()
        );
        $item = ($entityId > 0) ? Dynamic::getInstance()->getById($entityId) : null;
        if (!empty($item) && Container::getInstance()->getUserPermissions()->canUserCancelRequest($item))
        {
            $cancelBtn = new Button([
                'dataset' => [
                    'id' => self::CANCEL_REQUEST_BTN_ID,
                ],
                "color" => Color::SECONDARY,
                "icon"  => Icon::STOP,
                "text"  => Loc::getMessage('CRM_RI_ACTION_CANCEL')
            ]);
            $cancelBtn->setId(self::CANCEL_REQUEST_BTN_ID);
            \Bitrix\UI\Toolbar\Facade\Toolbar::addButton($cancelBtn, ButtonLocation::AFTER_TITLE);
            static::$cancelBtnAdded = true;
        }
    }
}