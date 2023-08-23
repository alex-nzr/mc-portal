<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Handler.php
 * 20.01.2023 14:23
 * ==================================================
 */
namespace Cbit\Mc\Expense\Service\Integration\Pull;


use Cbit\Mc\Expense\Internals\Control\ServiceManager;

/**
 * @class Handler
 * @package Cbit\Mc\Expense\Service\Integration\Pull
 */
class Handler
{
    /**
     * @return array
     */
    public static function bindDependentModule(): array
    {
        return [
            'MODULE_ID' => ServiceManager::getModuleId(),
            'USE' => ["PUBLIC_SECTION"]
        ];
    }
}