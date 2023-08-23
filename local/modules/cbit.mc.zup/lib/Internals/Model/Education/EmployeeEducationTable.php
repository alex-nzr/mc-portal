<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EmployeeEducationTable.php
 * 21.11.2022 22:43
 * ==================================================
 */


namespace Cbit\Mc\Zup\Internals\Model\Education;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Internals\Orm\Modifier;

/**
 * Class EmployeeEducationTable
 * @package Cbit\Mc\Zup\Internals\Model\Education
 */
class EmployeeEducationTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_zup_employee_education";
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

            new StringField('UUID'),

            (new DatetimeField('DATE_CREATE'))
                ->configureRequired()
                ->configureDefaultValue(new DateTime),

            (new DatetimeField('DATE_MODIFY'))
                ->configureRequired()
                ->configureDefaultValue(new DateTime),

            new IntegerField("USER_ID"),

            new ReferenceField(
                "USER",
                UserTable::class,
                ["=this.USER_ID" => "ref.ID"]
            ),

            (new StringField("EDUCATION_TYPE_UUID"))
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            new ReferenceField(
                "EDUCATION_TYPE",
                EducationTypesTable::class,
                ["=this.EDUCATION_TYPE_UUID" => "ref.UUID"]
            ),

            (new StringField('INSTITUTION_RU'))
                ->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField("INSTITUTION_EN"))
                ->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('SPECIALTY_RU'))
                ->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField("SPECIALTY_EN"))
                ->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('QUALIFICATION_RU'))
                ->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField("QUALIFICATION_EN"))
                ->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new DatetimeField('DATE_BEGIN_STUDYING'))
                ->configureRequired(),

            (new DatetimeField('DATE_END_STUDYING'))
                ->configureRequired(),

            (new BooleanField('OUTSIDE_RUSSIA'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('N'),

            (new BooleanField('CONFIRMED_BY_HR'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('N'),

            (new BooleanField('SENT_TO_ONE_C'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('N'),
        ];
    }
}