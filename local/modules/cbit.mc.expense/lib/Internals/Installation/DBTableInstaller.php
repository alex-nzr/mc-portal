<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - DBTableInstaller.php
 * 30.11.2022 18:32
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\Installation;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;
use Cbit\Mc\Expense\Internals\Model\ExternalParticipantsTable;

/**
 * Class DBTableInstaller
 * @package Cbit\Mc\Expense\Internals\Installation
 */
class DBTableInstaller
{
    private static array $dataClasses = [
        ExternalParticipantsTable::class,
    ];

    /**
     * @throws \Exception
     */
    public static function install(): void
    {
        static::createDataTables(static::$dataClasses);
    }

    /**
     * @throws \Exception
     */
    public static function uninstall(): void
    {
        static::deleteDataTables(static::$dataClasses);
    }

    /**
     * @param array $dataClasses
     * @throws \Exception
     */
    private static function createDataTables(array $dataClasses): void
    {
        $connection = Application::getConnection();

        foreach ($dataClasses as $dataClass)
        {
            $dataTableName = Base::getInstance($dataClass)->getDBTableName();
            if(!$connection->isTableExists($dataTableName))
            {
                Base::getInstance($dataClass)->createDbTable();
            }
        }
    }

    /**
     * @param array $dataClasses
     * @throws \Exception
     */
    private static function deleteDataTables(array $dataClasses): void
    {
        $connection = Application::getConnection();

        foreach ($dataClasses as $dataClass)
        {
            $dataTableName = Base::getInstance($dataClass)->getDBTableName();
            if($connection->isTableExists($dataTableName))
            {
                $connection->dropTable($dataTableName);
            }
        }
    }
}