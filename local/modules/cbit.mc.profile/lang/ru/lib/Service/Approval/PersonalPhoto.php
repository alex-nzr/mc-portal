<?php

use Cbit\Mc\Profile\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_PHOTO_SENT_TO_APPROVAL_TITLE']       = "Фотография отправлена на модерацию";
$MESS[$moduleId.'_PHOTO_SENT_TO_APPROVAL_DESC']        = "Фотография поменяется автоматически в случае успешного прохождения модерации";
$MESS[$moduleId.'_NEW_PHOTO_FOR_APPROVING_TITLE']      = "Новое фото ожидает модерации";
$MESS[$moduleId.'_NEW_PHOTO_FOR_APPROVING_LINK_TEXT']  = "Перейти к просмотру";
$MESS[$moduleId.'_PHOTO_APPROVING_ERROR']              = "При обновлении данных произошла ошибка";
$MESS[$moduleId.'_PHOTO_APPROVING_NO_FILE_ERROR']      = "Файл с новым фото профиля не найден.";
$MESS[$moduleId.'_PHOTO_APPROVED_SUCCESS_TITLE']       = "Фото одобрено";
$MESS[$moduleId.'_PHOTO_APPROVED_SUCCESS_DESC']        = "Новое фото профиля успешно прошло модерацию.";
$MESS[$moduleId.'_PHOTO_APPROVED_DECLINE_TITLE']       = "Фото не прошло модерацию";
$MESS[$moduleId.'_PHOTO_APPROVING_NO_FILE_IN_QUEUE']   = "Фото отсутствует в очереди на модерацию";