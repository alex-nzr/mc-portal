<?php

use Cbit\Mc\Core\Internals\Control\ServiceManager;

try
{
    ServiceManager::getInstance()->includeModuleDependencies();
}
catch (Throwable $e)
{
}