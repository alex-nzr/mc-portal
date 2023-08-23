<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Main.php
 * 01.02.2023 16:25
 * ==================================================
 */


namespace Cbit\Mc\Core\Handler;

use Cbit\Mc\Core\Internals\Control\ServiceManager;

/**
 * @class Main
 * @package Cbit\Mc\Core\Handler
 */
class Main
{
    /**
     * @return void
     * @throws \Exception
     */
    public static function includeCoreDependencies(): void
    {
        ServiceManager::getInstance()->includeModuleDependencies();
    }
}