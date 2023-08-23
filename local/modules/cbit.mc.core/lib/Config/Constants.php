<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Constants.php
 * 25.11.2022 12:34
 * ==================================================
 */

namespace Cbit\Mc\Core\Config;

/**
 * Class Constants
 * @package Cbit\Mc\Core\Config
 */
class Constants
{
    const OPTION_KEY_PD_STAFFING_ROLE       = 'option_key_pd_staffing_role';
    const OPTION_KEY_HR_TEAM_ROLE           = 'option_key_hr_team_role';
    const OPTION_KEY_EA_LEADERS_ROLE        = 'option_key_ea_leaders_role';
    const OPTION_KEY_VG_LEADERS_ROLE        = 'option_key_vg_leaders_role';
    const OPTION_KEY_RI_ANALYSTS_ROLE       = 'option_key_ri_analysts_role';
    const OPTION_KEY_RI_MANAGERS_ROLE       = 'option_key_ri_managers_role';
    const OPTION_KEY_EXPENSES_IT_ROLE       = 'option_key_expenses_it_role';
    const OPTION_KEY_EXPENSES_TRAVEL_ROLE   = 'option_key_expenses_travel_role';
    const OPTION_KEY_EXPENSES_FINANCE_ROLE  = 'option_key_expenses_finance_role';
    const OPTION_KEY_EXPENSES_PAYROLL_ROLE  = 'option_key_expenses_payroll_role';

    const OPTION_KEY_TENURE_COMPANY_UF_CODE  = 'option_key_tenure_company_uf_code';
    const OPTION_KEY_TENURE_POSITION_UF_CODE = 'option_key_tenure_position_uf_code';
    const OPTION_KEY_ABSENCE_UF_CODE         = 'option_key_absence_uf_code';
    const OPTION_KEY_ZUP_STATUS_UF_CODE      = 'option_key_zup_status_uf_code';
    const OPTION_KEY_FIO_EN_UF_CODE          = 'option_key_fio_en_uf_code';
    const OPTION_KEY_FMNO_UF_CODE            = 'option_key_fmno_uf_code';
    const OPTION_KEY_BASE_PER_DIEM_UF_CODE   = 'option_key_base_per_diem_uf_code';
    const OPTION_KEY_POSITION_EN_UF_CODE     = 'option_key_position_en_uf_code';
    const OPTION_KEY_TRACK_UF_CODE           = 'option_key_track_uf_code';
    const OPTION_KEY_CSP_UF_CODE             = 'option_key_csp_uf_code';
    const OPTION_KEY_RATING_UF_CODE          = 'option_key_rating_uf_code';

    const OPTION_TYPE_FILE_POSTFIX           = '_FILE';
    //const OPTION_KEY_SOME_FILE_OPTION      = 'option_key_some_file_option'.self::OPTION_TYPE_FILE_POSTFIX;

    const USER_AVAILABILITY_STATUS_FREE     = 'Free';
    const USER_AVAILABILITY_STATUS_LEARNING = 'Learning';
    const USER_AVAILABILITY_STATUS_STAFFED  = 'Staffed';
    const USER_AVAILABILITY_STATUS_BEACH    = 'Beach';
    const USER_AVAILABILITY_STATUS_LOA      = 'LOA';

    const ACTIVITIES_IBLOCK_CODE            = 'activitiesRegistry';
    const INDUSTRIES_IBLOCK_CODE            = 'industriesRegistry';
    const FUNCTIONS_IBLOCK_CODE             = 'functionsRegistry';
    const TEAM_COMP_IBLOCK_CODE             = 'teamCompositionsRegistry';
    const PROJECT_PHASES_IBLOCK_CODE        = 'projectPhasesRegistry';
    const PROJECT_STATES_IBLOCK_CODE        = 'projectStatesRegistry';
    const PER_DIEM_EDIT_REASONS_IBLOCK_CODE = 'perDiemEditReasonsRegistry';
    const EXPENSE_LOCATIONS_IBLOCK_CODE     = 'expenseLocationsRegistry';

    const USER_POSITION_EM        = 'EM';
    const USER_POSITION_ASC       = 'ASC';
    const USER_POSITION_SBA       = 'SBA';
    const USER_POSITION_BA        = 'BA';
    const USER_POSITION_BAI       = 'BAI';
    const USER_POSITION_PTI_1     = 'PTI (1)';
    const USER_POSITION_PTI_2     = 'PTI (2)';
    const USER_POSITION_FASC      = 'FASC';
    const USER_POSITION_PARTNER   = 'Partners';
    const USER_POSITION_PRINCIPAL = 'Principals';
    const USER_POSITION_JEM       = 'JEM';
    const USER_POSITION_JEX       = 'Junior Expert';
    const USER_POSITION_EXPERT    = 'Expert';
    const USER_POSITION_SENIOR    = 'Senior Expert';

    const USER_EMPLOYMENT_TYPE_CSP = 'CSP';
    const USER_EMPLOYMENT_TYPE_OSP = 'OSP';
}