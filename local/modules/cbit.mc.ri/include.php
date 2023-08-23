<?php

use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Internals\Debug\Logger;

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