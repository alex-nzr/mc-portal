<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - PhotoQueueTable.php
 * 08.11.2022 15:35
 * ==================================================
 */

namespace Cbit\Mc\Profile\Internals\Model\Approval;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\UserTable;

/**
 * Class PhotoQueueTable
 * @package Cbit\Mc\Profile\Internals\Model\Approval
 */
class PhotoQueueTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_profile_photo_approval_queue";
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new IntegerField('OLD_FILE_ID'))
                ->configureRequired()
                ->configureDefaultValue(0),

            (new IntegerField('NEW_FILE_ID'))->configureRequired(),

            (new IntegerField("USER_ID"))->configureRequired(),

            new ReferenceField(
                "USER",
                UserTable::class,
                ["=this.USER_ID" => "ref.ID"]
            )
        ];
    }
}