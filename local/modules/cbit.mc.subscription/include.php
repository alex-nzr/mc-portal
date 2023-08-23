<?php

use Cbit\Mc\Subscription\Config\Configuration;
use Cbit\Mc\Subscription\Internals\Control\ServiceManager;
use Cbit\Mc\Subscription\Internals\Debug\Logger;

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