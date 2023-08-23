<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Messages.php
 * 21.12.2022 14:23
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Localization;

use Bitrix\Crm\Service\Localization;
use Bitrix\Main\Localization\Loc;

class Messages extends Localization
{
    /**
     * @return array
     */
    public function loadMessages (): array
    {
        //\Cbit\Mc\RI\Internals\Debug\Logger::print(parent::loadMessages());
        return array_merge(parent::loadMessages(), Loc::loadLanguageFile(__FILE__));
    }
}