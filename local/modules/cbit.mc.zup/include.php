<?php

use Bitrix\Main\Loader;
use Cbit\Mc\Zup\Config\Constants;
use Cbit\Mc\Zup\Internals\Control\ServiceManager;
use Cbit\Mc\Zup\Internals\Debug\Logger;

try
{
    $arControllers = [
        '\\Cbit\\Mc\\Zup\\Controller\\Base'  => 'lib/Controller/Base.php',
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