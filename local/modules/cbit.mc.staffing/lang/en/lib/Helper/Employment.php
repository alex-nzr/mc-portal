<?php

use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_RELEVANT_ROLE_NOT_FOUND_IN_PROJECT'] = 'There is no need for employees with the #ROLE# role on this project';
$MESS[$moduleId.'_PERCENT_IS_MORE_THAN_NEED']          = 'On this project, participation percentages are available for an employee with the role #ROLE#: #PERCENTS#';
$MESS[$moduleId.'_START_DATE_IS_INCORRECT']            = 'No need was found with the selected start date. Change the start date or make changes to needs beforehand';
$MESS[$moduleId.'_END_DATE_IS_CHANGED']                = 'An employee has been added. Please note: the end date in need was automatically changed from #OLD_END_DATE# to #NEW_END_DATE#.';