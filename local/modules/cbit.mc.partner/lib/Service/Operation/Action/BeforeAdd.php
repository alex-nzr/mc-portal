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

namespace Cbit\Mc\Partner\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Helper\Im\Notify;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Partner\Config\Configuration;
use Cbit\Mc\Partner\Config\Constants;
use Cbit\Mc\Partner\Entity\Dynamic;
use Cbit\Mc\Partner\Helper\Main\User;
use Cbit\Mc\Partner\Service\Container;
use Exception;

/**
 * @class BeforeAdd
 * @package Cbit\Mc\Partner\Service\Operation\Action
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
        return new Result();
    }
}