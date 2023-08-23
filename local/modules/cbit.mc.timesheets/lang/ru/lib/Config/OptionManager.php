<?php

use Cbit\Mc\Timesheets\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();
$MESS[$moduleId.'_MAIN_SETTINGS']            = "Настройки api";
$MESS[$moduleId.'_OPTION_API_ADDRESS']       = "Api url";
$MESS[$moduleId.'_OPTION_API_LOGIN']         = "Api login";
$MESS[$moduleId.'_OPTION_API_PASSWORD']      = "Api password";
$MESS[$moduleId.'_OPTION_API_CLIENT_ID']     = "Client id";
$MESS[$moduleId.'_OPTION_API_CLIENT_SECRET'] = "Client secret";
$MESS[$moduleId.'_OPTION_API_API_KEY']       = "Api key";

$MESS[$moduleId.'_SYNC_SETTINGS']               = "Служебные настройки (не менять без крайней необходимости)";
$MESS[$moduleId.'_OPTION_LAST_GET_ACTIVITIES']  = "Дата последнего запроса activities";
$MESS[$moduleId.'_OPTION_LAST_GET_INDUSTRIES']  = "Дата последнего запроса industries";
$MESS[$moduleId.'_OPTION_LAST_GET_FUNCTIONS']   = "Дата последнего запроса functions";
$MESS[$moduleId.'_OPTION_LAST_GET_TEAM_COMP']   = "Дата последнего запроса team compositions";