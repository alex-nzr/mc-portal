<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - UserProjectTable.php
 * 30.11.2022 18:39
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Internals\Model;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Internals\Orm\Modifier;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Entity\Dynamic;
use Cbit\Mc\Staffing\Helper\Employment;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;

/**
 * Class UserProjectTable
 * @package Cbit\Mc\Staffing\Internals\Model
 */
class UserProjectTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return "cbit_mc_staffing_user_project";
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

            (new IntegerField('PROJECT_ID'))->configureRequired(),

            (new EnumField('USER_ROLE'))
                ->configureRequired()
                ->configureValues(Configuration::getInstance()->getStaffingUserRoles()),

            (new IntegerField('USER_EMPLOYMENT_PERCENT'))->configureRequired(),

            (new EnumField('USER_EMPLOYMENT_TYPE'))
                ->configureRequired()
                ->configureValues(Configuration::getInstance()->getStaffingEmploymentTypes()),

            (new IntegerField('USER_PER_DIEM'))->configureRequired(),

            (new StringField('PER_DIEM_REASON'))
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new StringField('PER_DIEM_COMMENT'))
                ->addFetchDataModifier([Modifier::class, 'clearFetchedString'])
                ->addSaveDataModifier([Modifier::class, 'clearStringBeforeSave']),

            (new DateField('STAFFING_DATE_FROM'))->configureRequired(),

            (new DateField('STAFFING_DATE_TO'))->configureRequired(),

            (new IntegerField('RELATED_NEED_ID'))->configureRequired(),

            new ReferenceField(
                "USER",
                UserTable::class,
                ["=this.USER_ID" => "ref.ID"]
            ),

            new ReferenceField(
                "PROJECT",
                !ServiceManager::isModuleInstallingNow() ? Dynamic::getInstance()->getDataClass() : '',
                ["=this.PROJECT_ID" => "ref.ID"]
            ),

            new ReferenceField(
                "NEED",
                EmploymentNeedTable::class,
                ["=this.RELATED_NEED_ID" => "ref.ID"]
            ),

            (new BooleanField('SENT_TO_ONE_C'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('N'),

            (new BooleanField('UPDATED_IN_ONE_C'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('Y'),

            (new BooleanField('DELETION_MARK'))
                ->configureRequired()
                ->configureValues('N', 'Y')
                ->configureDefaultValue('N'),
        ];
    }

    /**
     * @param \Bitrix\Main\ORM\Event $event
     */
    public static function onAfterAdd(Event $event)
    {
        $data = $event->getParameter("fields");
        Employment::updateUserAvailabilityStatus((int)$data['USER_ID']);
    }

    /**
     * @param \Bitrix\Main\ORM\Event $event
     */
    public static function onAfterDelete(Event $event)
    {
        $data = $event->getParameter("fields");
        Employment::updateUserAvailabilityStatus((int)$data['USER_ID']);
    }

    /**
     * @param \Bitrix\Main\ORM\Event $event
     */
    public static function onAfterUpdate(Event $event)
    {
        $data = $event->getParameter("fields");
        Employment::updateUserAvailabilityStatus((int)$data['USER_ID']);
    }
}