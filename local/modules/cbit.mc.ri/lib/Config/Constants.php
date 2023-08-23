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

namespace Cbit\Mc\RI\Config;

/**
 * Class Constants
 * @package Cbit\Mc\RI\Config
 */
class Constants
{
    const DYNAMIC_TYPE_CODE                 = 'DYNAMIC_ENTITY_RI';
    const DYNAMIC_TYPE_TITLE                = 'R&I';
    const DYNAMIC_TYPE_CUSTOM_SECTION_CODE  = 'ri';
    const DYNAMIC_TYPE_CUSTOM_SECTION_TITLE = 'R&I';

    const CUSTOM_PAGE_LIST        = 'list';
    const CUSTOM_PAGE_TEAM        = 'team';
    const CUSTOM_PAGE_OUTSOURCING = 'outsourcing';

    const OPTION_TYPE_FILE_POSTFIX       = '_FILE';
    const OPTION_KEY_DYNAMIC_TYPE_ID     = 'cbit_mc_ri_type_id';
    const OPTION_KEY_COORDINATOR_ID      = 'cbit_mc_ri_coordinator_id';
    const OPTION_KEY_TEAM_DESCRIPTION    = 'cbit_mc_ri_team_desc';
    const OPTION_KEY_TEAM_WORK_TIME      = 'cbit_mc_ri_team_work_time';
    const OPTION_KEY_DEFAULT_ASSIGNED_ID = 'cbit_mc_ri_default_assigned_id';
    const OPTION_KEY_SOME_FILE_OPTION    = 'cbit_mc_ri_some_file_option'.self::OPTION_TYPE_FILE_POSTFIX;

    const DYNAMIC_CATEGORY_DEFAULT_TITLE    = 'Default';
    const DYNAMIC_CATEGORY_DEFAULT_CODE     = 'DEFAULT';
    const DYNAMIC_STAGE_DEFAULT_NEW         = 'NEW';
    const DYNAMIC_STAGE_DEFAULT_REVIEW      = 'UNDER_REVIEW';
    const DYNAMIC_STAGE_DEFAULT_ASSIGNED    = 'ASSIGNED_TO';
    const DYNAMIC_STAGE_DEFAULT_POSTPONED   = 'POSTPONED';
    const DYNAMIC_STAGE_DEFAULT_SUCCESS     = 'SUCCESS';
    const DYNAMIC_STAGE_DEFAULT_FAIL        = 'FAIL';

    const OPTION_KEY_STAFFING_TYPE_ID  = 'cbit_mc_staffing_dynamic_type_id';
    const STAFFING_MODULE_ID           = 'cbit.mc.staffing';

    const SHOW_SCORING_POPUP_ACTION = 'showScoringPopup';
}