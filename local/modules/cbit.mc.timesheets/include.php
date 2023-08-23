<?php

use Bitrix\Main\Loader;
use Cbit\Mc\Timesheets\Config\Constants;
use Cbit\Mc\Timesheets\Internals\Control\ServiceManager;
use Cbit\Mc\Timesheets\Internals\Debug\Logger;

try
{
    $arControllers = [
        '\\Cbit\\Mc\\Timesheets\\Controller\\Base'  => 'lib/Controller/Base.php',
    ];
    Loader::registerAutoLoadClasses(GetModuleID(__FILE__), $arControllers);
    ServiceManager::getInstance()->includeModuleDependencies();
}
catch (Throwable $e)
{
    Logger::writeToFile(
        $e->getMessage(),
        date("d.m.Y H:i:s") . ' - error on module including',
        Constants::PATH_TO_LOGFILE
    );
}