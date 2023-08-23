<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ConfigType.php
 * 19.02.2023 18:55
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\EditorConfig;

use Cbit\Mc\Expense\Config\Constants;

/**
 * @class ConfigType
 * @package Cbit\Mc\Expense\Internals\EditorConfig
 */
class ConfigType
{
    const RECEIPT        = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
    const BUSINESS_TRIPS = Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE;
    const TR_YOUR_BUDGET = Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE;

    /**
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        return [
            static::RECEIPT,
            static::BUSINESS_TRIPS,
            static::TR_YOUR_BUDGET,
        ];
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isTypeSupported(string $type): bool
    {
        return in_array($type, static::getSupportedTypes());
    }
}