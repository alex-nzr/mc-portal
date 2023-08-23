<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Base.php
 * 17.01.2023 12:17
 * ==================================================
 */
namespace Cbit\Mc\Partner\Controller;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Cbit\Mc\Partner\Service\Container;

/**
 * Class Base
 * @package Cbit\Mc\Partner\Controller
 */
class Base extends Controller
{
    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     * @throws \Exception
     */
    protected function processBeforeAction(Action $action): bool
    {
        Container::getInstance()->getLocalization()->loadMessages();
        return parent::processBeforeAction($action);
    }

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