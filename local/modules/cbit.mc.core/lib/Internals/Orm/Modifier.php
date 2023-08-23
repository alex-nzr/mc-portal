<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Modifier.php
 * 15.02.2023 12:33
 * ==================================================
 */
namespace Cbit\Mc\Core\Internals\Orm;

use CBXSanitizer;
use Throwable;

/**
 * @class Modifier
 * @package Cbit\Mc\Core\Internals\Orm
 */
class Modifier
{
    private static ?CBXSanitizer $sanitizer = null;

    /**
     * @param $value
     * @return string
     */
    public static function clearFetchedString($value): string
    {
        try
        {
            if (is_string($value))
            {
                return strip_tags(stripslashes(htmlspecialchars($value)));
            }
        }
        catch(Throwable $e)
        {
            //log error
        }
        return '';
    }

    /**
     * @param $value
     * @return string
     */
    public static function clearStringBeforeSave($value): string
    {
        try
        {
            if (is_string($value))
            {
                if (static::$sanitizer === null)
                {
                    static::$sanitizer = new CBXSanitizer;
                    static::$sanitizer->setLevel(CBXSanitizer::SECURE_LEVEL_HIGH);
                }

                return static::$sanitizer->sanitizeHtml($value);
            }
        }
        catch(Throwable $e)
        {
            //log error
        }

        return '';
    }
}