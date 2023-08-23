<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ExternalParticipantsTable.php
 * 30.01.2023 12:39
 * ==================================================
 */


namespace Cbit\Mc\Expense\Internals\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Core\Internals\Orm\Modifier;

/**
 * @class ExternalParticipantsTable
 * @package Cbit\Mc\Expense\Internals\Model
 */
class ExternalParticipantsTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_expense_external_participants";
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

            (new DateTimeField('DATE_CREATE'))
                ->configureRequired()
                ->configureDefaultValue(new DateTime()),

            (new StringField('NAME'))->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('LAST_NAME'))->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('SECOND_NAME'))
                ->configureDefaultValue('')
                ->configureNullable()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('COMPANY'))->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('POSITION'))->configureRequired()
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),
        ];
    }
}