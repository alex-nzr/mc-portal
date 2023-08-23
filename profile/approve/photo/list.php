<?php
/** @var \CMain $APPLICATION */
/** @var \CUser $USER */
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

?>
<?php
$APPLICATION->IncludeComponent('cbit:mc.profile.photo-approver.list', '', []);
?>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
