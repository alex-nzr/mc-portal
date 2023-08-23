<?php

use CBit\Mc\Profile\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId."_COMPONENT_REQUIRED_PARAMS_EMPTY"]    = "Required component parameters were not passed";
$MESS[$moduleId."_COMPONENT_ERROR_PERMISSIONS"]        = "No photo moderation permissions";
$MESS[$moduleId."_COMPONENT_ERROR_PHOTO_NOT_IN_QUEUE"] = "Photo not found in moderation queue";
