<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Messages.php
 * 03.02.2023 11:23
 * ==================================================
 */

namespace Cbit\Mc\Subscription\Service\Localization;

use Bitrix\Crm\Service\Localization;
use Bitrix\Main\Localization\Loc;

/**
 * @class Messages
 * @package Cbit\Mc\Subscription\Service\Localization
 */
class Messages extends Localization
{
    /**
     * @return array
     */
    public function loadMessages (): array
    {
        //\Cbit\Mc\Subscription\Internals\Debug\Logger::print(parent::loadMessages());
        return array_merge(parent::loadMessages(), Loc::loadLanguageFile(__FILE__));
    }
}