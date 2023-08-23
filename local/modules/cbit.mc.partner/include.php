<?php

use Cbit\Mc\Partner\Config\Configuration;
use Cbit\Mc\Partner\Internals\Control\ServiceManager;
use Cbit\Mc\Partner\Internals\Debug\Logger;

try
{
    ServiceManager::getInstance()->includeModule();
}
catch (Throwable $e)
{
    Logger::writeToFile(
        $e->getMessage(),
        date("d.m.Y H:i:s") . ' - error on including module - ' . ServiceManager::getModuleId(),
        Configuration::getInstance()->getLogFilePath()
    );
}