<?php

use Bitrix\Main\Engine\CurrentUser;

$userId = (int)CurrentUser::get()->getId();

$aMenuLinks = [
    [
        "Profile",
        SITE_DIR."profile/index.php",
        [],
        [],
        ""
    ],
    [
        "Calendar",
        SITE_DIR."company/personal/user/".$userId."/calendar/",
        [],
        [],
        ""
    ],
];