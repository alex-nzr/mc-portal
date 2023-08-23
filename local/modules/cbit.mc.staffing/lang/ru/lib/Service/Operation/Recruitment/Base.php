<?php

use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId."_OPERATION_PERM_ERROR"] = "Operation blocked by permissions";
$MESS[$moduleId."_OPERATION_REQUIRED_PARAM_ERROR"] = "Required param #PARAM# is empty";
$MESS[$moduleId."_OPERATION_DATE_ERROR"] = "End date can not be less then start date";
$MESS[$moduleId."_OPERATION_MAX_PERCENT_ERROR"] = "In selected period max user's employment is #MAX_PERCENT#%. You can't staff this user for another #ADD_PERCENT#%";
$MESS[$moduleId."_OPERATION_HAS_SAME_STAFFING_ERROR"] = "User is already stuffed in the selected project with the selected role in the specified period";