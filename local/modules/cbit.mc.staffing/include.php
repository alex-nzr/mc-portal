<?php

use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Internals\Debug\Logger;

try
{
    ServiceManager::getInstance()->includeModule();
}
catch (Throwable $e)
{
    Logger::writeToFile(
        $e->getMessage(),
        date("d.m.Y H:i:s") . ' - error on module including',
        Constants::PATH_TO_LOGFILE
    );
}