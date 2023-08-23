<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Handler.php
 * 15.12.2022 14:23
 * ==================================================
 */
namespace Cbit\Mc\RI\Service\Integration\Pull;


use Cbit\Mc\RI\Internals\Control\ServiceManager;

/**
 * Class Handler
 * @package Cbit\Mc\RI\Service\Integration\Pull
 */
class Handler
{
    /**
     * @return array
     */
    public static function bindDependentModule(): array
    {
        return [
            'MODULE_ID' => ServiceManager::getModuleId(),
            'USE' => ["PUBLIC_SECTION"]
        ];
    }
}