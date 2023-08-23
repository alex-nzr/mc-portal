<?php

use Cbit\Mc\Profile\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_PHOTO_SENT_TO_APPROVAL_TITLE']       = "The photo has been sent for moderation";
$MESS[$moduleId.'_PHOTO_SENT_TO_APPROVAL_DESC']        = "The photo will change automatically in case of successful moderation";
$MESS[$moduleId.'_NEW_PHOTO_FOR_APPROVING_TITLE']      = "New photo awaiting moderation";
$MESS[$moduleId.'_NEW_PHOTO_FOR_APPROVING_LINK_TEXT']  = "Go to viewing";
$MESS[$moduleId.'_PHOTO_APPROVING_ERROR']              = "An error occurred while updating the data";
$MESS[$moduleId.'_PHOTO_APPROVING_NO_FILE_ERROR']      = "The file with the new profile photo was not found.";
$MESS[$moduleId.'_PHOTO_APPROVED_SUCCESS_TITLE']       = "Photo approved";
$MESS[$moduleId.'_PHOTO_APPROVED_SUCCESS_DESC']        = "The new profile photo has successfully passed moderation.";
$MESS[$moduleId.'_PHOTO_APPROVED_DECLINE_TITLE']       = "The photo has not passed moderation";
$MESS[$moduleId.'_PHOTO_APPROVING_NO_FILE_IN_QUEUE']   = "The photo is missing from the moderation queue";