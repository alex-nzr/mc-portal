<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AssignedRI.php
 * 21.12.2022 11:07
 * ==================================================
 */


namespace Cbit\Mc\Core\Internals\UserField\Type;


use Bitrix\Intranet\UserField\Types\EmployeeType;
use CUserTypeManager;

/**
 * Class AssignedRI
 * @package Cbit\Mc\Core\Internals\UserField\Type
 */
class AssignedRI extends EmployeeType
{
    public const USER_TYPE_ID = 'cbit.ri-user';
    public const RENDER_COMPONENT = 'cbit:mc.core.field.ri-user';

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => '(cbit)Привязка к пользователю RI',
            'BASE_TYPE'   => CUserTypeManager::BASE_TYPE_ENUM,
        ];
    }
}