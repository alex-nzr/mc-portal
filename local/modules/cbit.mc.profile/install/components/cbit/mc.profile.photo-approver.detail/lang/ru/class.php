<?php

use CBit\Mc\Profile\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId."_COMPONENT_REQUIRED_PARAMS_EMPTY"]    = "Не переданы обязательные параметры компонента";
$MESS[$moduleId."_COMPONENT_ERROR_PERMISSIONS"]        = "Отсутствуют права на модерацию фото";
$MESS[$moduleId."_COMPONENT_ERROR_PHOTO_NOT_IN_QUEUE"] = "Фото отсутствует в очереди на модерацию";
