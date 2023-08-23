<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Installer.php
 * 13.12.2022 15:04
 * ==================================================
 */


namespace Cbit\Mc\Timesheets\Internals\Installation;

use Bitrix\Main\Result;

/**
 * Class Installer
 * @package Cbit\Mc\Timesheets\Internals\Installation
 */
class Installer
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function installModule(): Result
    {
        $finalRes = new Result();
        DBTableInstaller::install();
        $ibResult = IBlockInstaller::install();
        if (!$ibResult->isSuccess())
        {
            $finalRes->addErrors($ibResult->getErrors());
        }
        return $finalRes;
    }

    /**
     * @throws \Exception
     */
    public static function uninstallModule(): void
    {
        DBTableInstaller::uninstall();
    }
}