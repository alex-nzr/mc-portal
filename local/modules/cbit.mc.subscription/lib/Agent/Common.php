<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Common.php
 * 17.01.2023 12:30
 * ==================================================
 */
namespace Cbit\Mc\Subscription\Agent;


use Cbit\Mc\Subscription\Config\Configuration;
use Cbit\Mc\Subscription\Internals\Debug\Logger;
use Throwable;

/**
 * Class Common
 * @package Cbit\Mc\Subscription\Agent
 */
class Common
{
    /**
     * @return string
     */
    public static function someFunc(): string
    {
        try
        {
            //Agent logic
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @param \Throwable $e
     * @param string $method
     */
    private static function logError(Throwable $e, string $method)
    {
        $code = !empty($e->getCode()) ? $e->getCode() : 0;
        Logger::writeToFile(
            "Code: $code. Description: " . $e->getMessage(),
            date("d.m.Y H:i:s") . ' ' . $method,
            Configuration::getInstance()->getLogFilePath()
        );
    }
}