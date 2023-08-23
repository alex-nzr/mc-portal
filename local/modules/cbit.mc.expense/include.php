<?php

use Cbit\Mc\Expense\Config\Configuration;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Internals\Debug\Logger;

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