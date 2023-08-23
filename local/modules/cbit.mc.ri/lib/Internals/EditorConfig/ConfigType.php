<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ConfigType.php
 * 24.02.2023 00:55
 * ==================================================
 */
namespace Cbit\Mc\RI\Internals\EditorConfig;

use Cbit\Mc\RI\Config\Constants;

/**
 * @class ConfigType
 * @package Cbit\Mc\RI\Internals\EditorConfig
 */
class ConfigType
{
    const COMMON = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;

    /**
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        return [
            static::COMMON,
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