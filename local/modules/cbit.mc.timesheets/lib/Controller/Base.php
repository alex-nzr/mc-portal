<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Base.php
 * 21.11.2022 12:17
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Controller;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;

/**
 * Class Base
 * @package Cbit\Mc\Timesheets\Controller
 */
class Base extends Controller
{
    /**
     * @return array
     */
    protected function getDefaultPreFilters(): array
    {
        return [
            new HttpMethod([HttpMethod::METHOD_POST]),
            new Csrf(),
            new Authentication()
        ];
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [];
    }
}