<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

$MESS['/bitrix/modules/main/lang/ru/classes/general/user.php']["USER_STATUS_ONLINE"] = "Online";
$MESS['/bitrix/modules/main/lang/ru/classes/general/user.php']["USER_STATUS_OFFLINE"] = "Offline";

$MESS['/bitrix/modules/intranet/lang/ru/lib/userabsence.php']["USER_ABSENCE_STATUS_VACATION"] = "Vacation";

$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_WORK_POSITION"] = "Position";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_UF_DEPARTMENT"] = "Department";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_UF_DEPARTMENT"] = "Department";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_PERSONAL_MOBILE"] = "Mobile phone";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_WORK_PHONE"] = "Work phone";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_UF_PHONE_INNER"] = "Inner phone";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_EMAIL"] = "Email";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE"] = "Auto";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_TIME_ZONE"] = "Time zone";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE_DEF"] = "(default)";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE_YES"] = "Yes, use browser";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE_NO"] = "No, select from list";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_SECTION_CONTACT_TITLE"] = "Contact info";
$MESS['/bitrix/modules/intranet/lang/ru/lib/component/userprofile/form.php']["INTRANET_USER_PROFILE_FIELD_PERSONAL_PHONE"] = "Personal phone";

$curPage = (string)Application::getInstance()->getContext()->getRequest()->getRequestedPage();
$refPage = (string)$_SERVER['HTTP_REFERER'];
if (
    (
        str_contains($curPage, '/page/ri/ri/')
        || str_contains($curPage, '/page/ri/list/')
        || str_contains($refPage, '/page/ri/ri/')
        || str_contains($refPage, '/page/ri/list/')
    )
    &&
    (str_contains($curPage, '/details/') || str_contains($refPage, '/details/'))
){
    $MESS['/bitrix/modules/crm/lang/ru/lib/timeline/historydatamodel/presenter/modification.php']["CRM_TIMELINE_PRESENTER_MODIFICATION_STAGE_ID"] = "Стадия запроса изменена";
    $MESS['/bitrix/modules/crm/lang/ru/lib/timeline/historydatamodel/presenter/creation.php']["CRM_TIMELINE_PRESENTER_CREATION_TITLE"] = "Создан запрос";

    $MESS['/bitrix/components/bitrix/crm.timeline/templates/.default/lang/ru/template.php']["CRM_TIMELINE_MARK_ENTITY_FAILED_MARK"] = "Запрос отменён";
    $MESS['/bitrix/components/bitrix/crm.timeline/templates/.default/lang/ru/template.php']["CRM_TIMELINE_COMMENT"] = "Работа над запросом";
    $MESS['/bitrix/components/bitrix/crm.timeline/templates/.default/lang/ru/template.php']["CRM_TIMELINE_COMMENT_PLACEHOLDER"] = "Оставьте комментарий";
}


