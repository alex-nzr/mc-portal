<?php
use CBit\Mc\Staffing\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_BINDER_COMPONENT_NAME'] = "Привязка сотрудников к проектам";
$MESS[$moduleId.'_BINDER_COMPONENT_DESC'] = "Выводит два списка с возможностью перетаскивания из одного в другой";
$MESS[$moduleId.'_BINDER_COMPONENT_VENDOR_NAME']    = "Первый Бит";
$MESS[$moduleId.'_BINDER_COMPONENT_CATEGORY_NAME']  = "Компоненты модуля Staffing";