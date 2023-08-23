<?php

use Cbit\Mc\Staffing\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();
$MESS[$moduleId.'_SYNC_SETTINGS']               = "Служебные настройки (не менять без крайней необходимости)";
$MESS[$moduleId.'_OPTION_LAST_GET_PROJECTS']    = "Дата последнего запроса проектов";