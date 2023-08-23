<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Employee.php
 * 25.11.2022 23:07
 * ==================================================
 */


namespace Cbit\Mc\Core\Internals\UserField\Type;


use Bitrix\Intranet\UserField\Types\EmployeeType;
use CUserTypeManager;

/**
 * Class Employee
 * @package Cbit\Mc\Core\Internals\UserField\Type
 */
class Employee extends EmployeeType
{
    public const USER_TYPE_ID = 'cbit.employee';
    public const RENDER_COMPONENT = 'cbit:mc.core.field.employee';

    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => '(cbit)Привязка к сотруднику',
            'BASE_TYPE'   => CUserTypeManager::BASE_TYPE_ENUM,
        ];
    }
}