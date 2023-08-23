<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EducationTypesTable.php
 * 21.11.2022 22:16
 * ==================================================
 */


namespace Cbit\Mc\Zup\Internals\Model\Education;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Core\Internals\Orm\Modifier;

/**
 * Class EducationTypesTable
 * @package Cbit\Mc\Zup\Internals\Model\Education
 */
class EducationTypesTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_zup_education_types";
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

            (new DatetimeField('DATE_CREATE'))
                ->configureRequired()
                ->configureDefaultValue(new DateTime),

            (new DatetimeField('DATE_MODIFY'))
                ->configureRequired()
                ->configureDefaultValue(new DateTime),

            (new StringField('UUID'))
                ->configureRequired()
                ->configureUnique(),

            (new StringField('DESCRIPTION_RU'))
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField("DESCRIPTION_EN"))
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),
        ];
    }
}