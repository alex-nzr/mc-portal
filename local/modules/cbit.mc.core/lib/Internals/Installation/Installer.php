<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Installer.php
 * 25.02.2023 12:17
 * ==================================================
 */
namespace Cbit\Mc\Core\Internals\Installation;

use Bitrix\Main\Config\Option;
use Cbit\Mc\Core\Internals\Control\ServiceManager;

/**
 * @class Installer
 * @package Cbit\Mc\Core\Internals\Installation
 */
class Installer
{
    /**
     * @return void
     * @throws \Exception
     */
    public static function installModule(): void
    {
        DBTableInstaller::install();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function uninstallModule(): void
    {
        DBTableInstaller::uninstall();
        Option::delete(ServiceManager::getModuleId());
    }
}