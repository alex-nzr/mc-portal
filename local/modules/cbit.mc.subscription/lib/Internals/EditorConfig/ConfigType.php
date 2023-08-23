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
namespace Cbit\Mc\Subscription\Internals\EditorConfig;

use Cbit\Mc\Subscription\Config\Constants;

/**
 * @class ConfigType
 * @package Cbit\Mc\Subscription\Internals\EditorConfig
 */
class ConfigType
{
    const COMMON  = 'COMMON';

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