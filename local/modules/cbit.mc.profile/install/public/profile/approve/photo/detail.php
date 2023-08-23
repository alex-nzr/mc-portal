<?php
/** @var \CMain $APPLICATION */
/** @var \CUser $USER */

use Bitrix\Main\Context;

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$request = Context::getCurrent()->getRequest();
$newFileId = $request->getQuery('NEW_ID');
$oldFileId = $request->getQuery('OLD_ID');
$userId    = $request->getQuery('USER_ID');
$backUrl   = $request->getQuery('BACK_URL');
?>
<?php
$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        'POPUP_COMPONENT_NAME' => 'cbit:mc.profile.photo-approver.detail',
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        'POPUP_COMPONENT_PARAMS' => [
            "NEW_FILE_ID" => $newFileId,
            "OLD_FILE_ID" => $oldFileId,
            "USER_ID"     => $userId
        ],
        'PAGE_MODE' => false,
        'PAGE_MODE_OFF_BACK_URL' => $backUrl ?? '/'
    ]
);
?>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
