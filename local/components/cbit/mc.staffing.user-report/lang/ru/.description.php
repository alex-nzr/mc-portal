<?php
use CBit\Mc\Staffing\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_USER_REPORT_COMPONENT_NAME']          = "Текущие проекты пользователя";
$MESS[$moduleId.'_USER_REPORT_COMPONENT_DESC']          = "Выводит список проектов, в которых пользователь участвует в текущий момент";
$MESS[$moduleId.'_USER_REPORT_COMPONENT_VENDOR_NAME']   = "Первый Бит";
$MESS[$moduleId.'_USER_REPORT_COMPONENT_CATEGORY_NAME'] = "Staffing components";