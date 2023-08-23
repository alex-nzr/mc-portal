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

namespace Cbit\Mc\Subscription\Config;

/**
 * Class Constants
 * @package Cbit\Mc\Subscription\Config
 */
class Constants
{
    const DYNAMIC_TYPE_CODE                 = 'DYNAMIC_ENTITY_SUBSCRIPTION';
    const DYNAMIC_TYPE_TITLE                = 'R&I Subscriptions';
    const DYNAMIC_TYPE_CUSTOM_SECTION_CODE  = 'ri';
    const DYNAMIC_TYPE_CUSTOM_SECTION_TITLE = 'R&I';

    const CUSTOM_PAGE_LIST    = 'list';

    const OPTION_TYPE_FILE_POSTFIX       = '_FILE';
    const OPTION_KEY_DYNAMIC_TYPE_ID     = 'cbit_mc_subscription_type_id';
    const OPTION_KEY_SOME_FILE_OPTION    = 'cbit_mc_subscription_some_file_option'.self::OPTION_TYPE_FILE_POSTFIX;

    const RI_MODULE_ID = 'cbit.mc.ri';
}