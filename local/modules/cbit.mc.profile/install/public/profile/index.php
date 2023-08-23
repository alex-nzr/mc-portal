<?php
/** @var \CMain $APPLICATION */
/** @var \CUser $USER */
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->IncludeComponent('bitrix:intranet.user.profile', '');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
