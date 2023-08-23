<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EmploymentNeedTable.php
 * 05.12.2022 15:38
 * ==================================================
 */
namespace Cbit\Mc\Staffing\Internals\Model;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Entity\Dynamic;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;

/**
 * Class EmploymentNeedTable
 * @package Cbit\Mc\Staffing\Internals\Model
 */
class EmploymentNeedTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_staffing_employment_need";
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

            (new IntegerField('PROJECT_ID'))->configureRequired(),

            (new EnumField('USER_ROLE'))
                ->configureRequired()
                ->configureValues(Configuration::getInstance()->getStaffingUserRoles()),

            (new IntegerField('USER_EMPLOYMENT_PERCENT'))->configureRequired(),

            (new DateField('NEEDLE_DATE_FROM'))->configureRequired(),

            (new DateField('NEEDLE_DATE_TO'))->configureRequired(),

            (new BooleanField('ACTIVE'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('Y'),

            new ReferenceField(
                "PROJECT",
                !ServiceManager::isModuleInstallingNow() ? Dynamic::getInstance()->getDataClass() : '',
                ["=this.PROJECT_ID" => "ref.ID"]
            ),
        ];
    }
}