<?php

use Cbit\Mc\Zup\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();
$MESS[$moduleId.'_MAIN_SETTINGS']            = "Настройки api";
$MESS[$moduleId.'_OPTION_API_ADDRESS']       = "Api url";
$MESS[$moduleId.'_OPTION_API_LOGIN']         = "Api login";
$MESS[$moduleId.'_OPTION_API_PASSWORD']      = "Api password";
$MESS[$moduleId.'_OPTION_API_CLIENT_ID']     = "Client id";
$MESS[$moduleId.'_OPTION_API_CLIENT_SECRET'] = "Client secret";
$MESS[$moduleId.'_OPTION_API_API_KEY']       = "Api key";

$MESS[$moduleId.'_ZUP_FIELDS_SETTINGS']      = "Настройки типовой интеграции с ЗУП";
$MESS[$moduleId.'_OPTION_FMNO_XML_ID']       = "Внешний код свойства FMNO";

$MESS[$moduleId.'_SYNC_SETTINGS']             = "Служебные настройки (не менять без крайней необходимости)";
$MESS[$moduleId.'_OPTION_LAST_GET_EDU_TYPES'] = "Дата последнего запроса типов образования";
$MESS[$moduleId.'_OPTION_LAST_GET_EMP_EDU']   = "Дата последнего запроса информации по образованию сотрудников";