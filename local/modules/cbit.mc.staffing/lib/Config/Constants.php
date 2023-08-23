<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Constants.php
 * 17.01.2023 12:34
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Config;

/**
 * Class Constants
 * @package Cbit\Mc\Staffing\Config
 */
class Constants
{
    const PATH_TO_LOGFILE = '/local/logs/cbit.mc.staffing-log.txt';

    const STAFFING_EMPLOYMENT_TYPE_STAFF = 'Staffing';
    const STAFFING_EMPLOYMENT_TYPE_BEACH = 'Beach';

    const DYNAMIC_TYPE_CUSTOM_SECTION_CODE = 'staffing';
    const DYNAMIC_TYPE_CODE = 'DYNAMIC_ENTITY_STAFFING';

    const CUSTOM_PAGE_LIST    = 'list';
    const CUSTOM_PAGE_BINDING = 'binding';

    const OPTION_KEY_DYNAMIC_TYPE_ID        = 'cbit_mc_staffing_dynamic_type_id';
    const OPTION_KEY_SYNC_LAST_GET_PROJECTS = 'cbit_mc_staffing_sync_last_get_projects';
}