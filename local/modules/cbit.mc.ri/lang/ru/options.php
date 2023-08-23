<?php

use Cbit\Mc\RI\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();
$MESS[$moduleId.'_MODULE_NOT_LOADED'] = "Не удалось подключить модуль $moduleId";
$MESS[$moduleId."_ACCESS_DENIED"]     = "Доступ к модулю $moduleId запрещён";