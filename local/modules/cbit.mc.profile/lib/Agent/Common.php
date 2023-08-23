<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Common.php
 * 25.10.2022 17:30
 * ==================================================
 */


namespace Cbit\Mc\Profile\Agent;


use Cbit\Mc\Profile\Config\Constants;

/**
 * Class Common
 * @package CBit\Mc\Profile\Agent
 */
class Common
{
    protected static string $logFile = Constants::PATH_TO_LOGFILE;

    /**
     * Example function for agent
     */
    public static function someFunc(): string
    {
        return __METHOD__.'();';
    }
}