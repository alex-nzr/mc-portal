<?php

use Cbit\Mc\Timesheets\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_MODULE_ID_NOT_DETECTED']  = "Module $moduleId not found";
$MESS[$moduleId.'_URL_ERROR']               = "Api url is empty. Check options of $moduleId";
$MESS[$moduleId.'_AUTH_ERROR']              = "Api login or password is empty. Check options of $moduleId";
$MESS[$moduleId.'_CLIENT_ID_ERROR']         = "Client id is empty. Check options of $moduleId";
$MESS[$moduleId.'_CLIENT_SECRET_ERROR']     = "Client secret is empty. Check options of $moduleId";
$MESS[$moduleId.'_API_KEY_ERROR']           = "Api key is empty. Check options of $moduleId";