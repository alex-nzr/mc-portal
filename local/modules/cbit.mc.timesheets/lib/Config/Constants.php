<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Constants.php
 * 21.11.2022 12:34
 * ==================================================
 */

namespace Cbit\Mc\Timesheets\Config;

/**
 * Class Constants
 * @package Cbit\Mc\Timesheets\Config
 */
class Constants
{
    const PATH_TO_LOGFILE = '/local/logs/cbit.mc.timesheets-log.txt';

    const OPTION_KEY_API_URL            = 'cbit_mc_timesheets_api_url';
    const OPTION_KEY_API_LOGIN          = 'cbit_mc_timesheets_api_login';
    const OPTION_KEY_API_PASSWORD       = 'cbit_mc_timesheets_api_password';
    const OPTION_KEY_API_CLIENT_ID      = 'cbit_mc_timesheets_api_client_id';
    const OPTION_KEY_API_CLIENT_SECRET  = 'cbit_mc_timesheets_api_client_secret';
    const OPTION_KEY_API_API_KEY        = 'cbit_mc_timesheets_api_api_key';

    const OPTION_KEY_SYNC_LAST_GET_INDUSTRIES = 'cbit_mc_timesheets_sync_last_get_industries';
    const OPTION_KEY_SYNC_LAST_GET_ACTIVITIES = 'cbit_mc_timesheets_sync_last_get_activities';
    const OPTION_KEY_SYNC_LAST_GET_FUNCTIONS  = 'cbit_mc_timesheets_sync_last_get_functions';
    const OPTION_KEY_SYNC_LAST_GET_TEAM_COMP  = 'cbit_mc_timesheets_sync_last_get_team_comp';
}