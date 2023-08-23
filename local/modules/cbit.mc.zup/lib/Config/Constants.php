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

namespace Cbit\Mc\Zup\Config;

/**
 * Class Constants
 * @package Cbit\Mc\Zup\Config
 */
class Constants
{
    const PATH_TO_LOGFILE = '/local/logs/cbit.mc.zup-log.txt';

    const OPTION_KEY_API_URL            = 'cbit_mc_zup_api_url';
    const OPTION_KEY_API_LOGIN          = 'cbit_mc_zup_api_login';
    const OPTION_KEY_API_PASSWORD       = 'cbit_mc_zup_api_password';
    const OPTION_KEY_API_CLIENT_ID      = 'cbit_mc_zup_api_client_id';
    const OPTION_KEY_API_CLIENT_SECRET  = 'cbit_mc_zup_api_client_secret';
    const OPTION_KEY_API_API_KEY        = 'cbit_mc_zup_api_api_key';

    const OPTION_KEY_SYNC_LAST_GET_EDU_TYPES = 'cbit_mc_zup_sync_last_get_edu_types';
    const OPTION_KEY_SYNC_LAST_GET_EMP_EDU   = 'cbit_mc_zup_sync_last_get_emp_edu';

    const OPTION_KEY_SYNC_FMNO_XML_ID = 'cbit_mc_zup_sync_fmno_xml_id';
}