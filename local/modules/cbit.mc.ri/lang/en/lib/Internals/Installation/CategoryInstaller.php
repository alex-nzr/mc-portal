<?php

use Cbit\Mc\RI\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_DEFAULT_STAGE_NEW_TITLE']       = 'Unassigned';
$MESS[$moduleId.'_DEFAULT_STAGE_REVIEW_TITLE']    = 'Under review';
$MESS[$moduleId.'_DEFAULT_STAGE_ASSIGNED_TITLE']  = 'Assigned to...';
$MESS[$moduleId.'_DEFAULT_STAGE_POSTPONED_TITLE'] = 'Postponed';
$MESS[$moduleId.'_DEFAULT_STAGE_SUCCESS_TITLE']   = 'Completed';
$MESS[$moduleId.'_DEFAULT_STAGE_FAIL_TITLE']      = 'Cancelled';