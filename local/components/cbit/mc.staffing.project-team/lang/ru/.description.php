<?php
use CBit\Mc\Staffing\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_USER_OF_PROJECT_COMPONENT_NAME'] = "Команда проекта";
$MESS[$moduleId.'_USER_OF_PROJECT_COMPONENT_DESC'] = "Выводит список пользователей, которые требуются или уже участвуют в данном проекте";
$MESS[$moduleId.'_USER_OF_PROJECT_COMPONENT_VENDOR_NAME']   = "Первый Бит";
$MESS[$moduleId.'_USER_OF_PROJECT_COMPONENT_CATEGORY_NAME'] = "Staffing components";