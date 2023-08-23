<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - UserPhotoTable.php
 * 15.11.2022 13:57
 * ==================================================
 */


namespace Cbit\Mc\Profile\Internals\Model\User;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\UserTable;

/**
 * Class UserPhotoTable
 * @package Cbit\Mc\Profile\Internals\Model\User
 */
class UserPhotoTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_profile_user_photo";
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

            (new IntegerField('USER_ID'))->configureRequired(),

            (new IntegerField('FILE_ID'))->configureRequired(),

            (new StringField('FILE_LINK'))->configureRequired(),

            new ReferenceField(
                "USER",
                UserTable::class,
                ["=this.USER_ID" => "ref.ID"]
            )
        ];
    }
}