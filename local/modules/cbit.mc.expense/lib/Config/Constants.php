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

namespace Cbit\Mc\Expense\Config;

/**
 * Class Constants
 * @package Cbit\Mc\Expense\Config
 */
class Constants
{
    const STAFFING_MODULE_ID = 'cbit.mc.staffing';

    const DYNAMIC_TYPE_CODE                 = 'DYNAMIC_ENTITY_EXPENSE';
    const DYNAMIC_TYPE_TITLE                = 'Expenses';
    const DYNAMIC_TYPE_CUSTOM_SECTION_CODE  = 'expenses';
    const DYNAMIC_TYPE_CUSTOM_SECTION_TITLE = 'Expenses';

    const CUSTOM_PAGE_LIST    = 'list';
    //const CUSTOM_PAGE_EXAMPLE = 'somePage';

    const OPTION_TYPE_FILE_POSTFIX       = '_FILE';
    const OPTION_KEY_DYNAMIC_TYPE_ID     = 'cbit_mc_expense_type_id';
    const OPTION_KEY_DEFAULT_TYB_CC      = 'cbit_mc_expense_default_tyb_cc';
    const OPTION_KEY_STAFFING_TYPE_ID    = 'cbit_mc_staffing_dynamic_type_id';
    //const OPTION_KEY_SOME_TEXT_OPTION  = 'cbit_mc_expense_some_text_option';
    //const OPTION_KEY_SOME_FILE_OPTION  = 'cbit_mc_expense_some_file_option'.self::OPTION_TYPE_FILE_POSTFIX;
    //const OPTION_KEY_SOME_COLOR_OPTION = 'cbit_mc_expense_some_color_option';

    const OPTION_TYB_CC_DEFAULT_VALUE = 'HR105';

    const DYNAMIC_CATEGORY_DEFAULT_TITLE     = 'Receipt';
    const DYNAMIC_CATEGORY_DEFAULT_CODE      = 'RECEIPT';
    const DYNAMIC_STAGE_DEFAULT_NEW          = 'NEW';
    const DYNAMIC_STAGE_DEFAULT_SUBMITTED    = 'RECEIPT_SUBMITTED';
    const DYNAMIC_STAGE_DEFAULT_UNDER_REVIEW = 'RECEIPT_UNDER_REVIEW';
    const DYNAMIC_STAGE_DEFAULT_REJECTED     = 'RECEIPT_REJECTED';
    const DYNAMIC_STAGE_DEFAULT_APPROVED     = 'RECEIPT_APPROVED';
    const DYNAMIC_STAGE_DEFAULT_SUCCESS      = 'SUCCESS';
    const DYNAMIC_STAGE_DEFAULT_FAIL         = 'FAIL';


    const DYNAMIC_CATEGORY_DAILY_ALLOWANCE_TITLE     = 'Business Trips';
    const DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE      = 'DAILY_ALLOWANCE';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_NEW          = 'NEW';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_SUBMITTED    = 'DA_SUBMITTED';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_UNDER_REVIEW = 'DA_UNDER_REVIEW';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_REJECTED     = 'DA_REJECTED';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_APPROVED     = 'DA_APPROVED';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_SUCCESS      = 'SUCCESS';
    const DYNAMIC_STAGE_DAILY_ALLOWANCE_FAIL         = 'FAIL';


    const DYNAMIC_CATEGORY_TR_YOUR_BUDGET_TITLE     = 'Treat yourself budget';
    const DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE      = 'TREAT_YOURSELF_BUDGET';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_NEW          = 'NEW';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_SUBMITTED    = 'TYB_SUBMITTED';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_UNDER_REVIEW = 'TYB_UNDER_REVIEW';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_REJECTED     = 'TYB_REJECTED';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_APPROVED     = 'TYB_APPROVED';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_SUCCESS      = 'SUCCESS';
    const DYNAMIC_STAGE_TR_YOUR_BUDGET_FAIL         = 'FAIL';

    const TYPE_OF_REQUEST_EXPENSE = 'Expense';
    const TYPE_OF_REQUEST_TRIP    = 'Trip';
    const TYPE_OF_REQUEST_TYB     = 'Treat yourself budget';

    const REQUEST_ACTION_KEY_CODE     = 'ACTION_KEY';

    const SHOW_REJECT_REASON_POPUP_ACTION = 'showRejectReasonPopup';
    const SPLIT_REQUEST_AMOUNT_ACTION     = 'splitRequestAmount';

    const MEAL_TEAM_EVENT_RECEIPT    = 'Meal: Team Event';
    const IT_EQUIPMENT_RECEIPT       = 'IT - Equipment';
    const IT_SOFT_RECEIPT            = 'IT - Soft';
    const IT_LICENSES_RECEIPT        = 'IT - Licenses';
    const IT_HARDWARE_RECEIPT        = 'IT - Hardware';
    const IT_CELLULAR_RECEIPT        = 'IT - Cellular';
    const TRAVEL_LAUNDRY_GYM_RECEIPT = 'Travel: Laundry / Gym';
    const TRAVEL_WEEKEND_RECEIPT     = 'Travel: Weekend travel';
    const TRAVEL_SO_TICKET_RECEIPT   = 'Travel: SO ticket';
    const TRAVEL_TOLL_ROAD_RECEIPT   = 'Travel: Toll road';
    const TRAVEL_TAXI_RECEIPT        = 'Travel: Taxi';
    const TRAVEL_PARKING_RECEIPT     = 'Travel: Parking';
}