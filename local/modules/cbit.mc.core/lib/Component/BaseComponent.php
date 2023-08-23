<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BaseComponent.php
 * 02.12.2022 17:24
 * ==================================================
 */

namespace Cbit\Mc\Core\Component;

use CBitrixComponent;
use Exception;
use function ShowError;

/**
 * Class BaseComponent
 * @package Cbit\Mc\Core\Component
 */
abstract class BaseComponent extends CBitrixComponent
{
    public    string $moduleId;
    protected bool $excelMode;

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->excelMode = ($this->request->get('EXCEL_MODE') === 'Y');
    }

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        return array_merge($arParams, [
            "CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? "N",
            "CACHE_TIME" => $arParams["CACHE_TIME"] ?? 0,
        ]);
    }

    /**
     * @return void
     */
    final public function executeComponent()
    {
        try
        {
            if ($this->checkRequirements() && $this->startResultCache($this->arParams['CACHE_TIME']))
            {
                $this->arResult = $this->getResult();
                $this->includeComponentTemplate();
                $this->endResultCache();
            }
        }
        catch(Exception $e)
        {
            $this->AbortResultCache();
            $this->showMessage($e->getMessage(), true);
        }
    }

    /**
     * @param string $message
     * @param bool $isError
     */
    protected function showMessage(string $message, bool $isError = false): void
    {
        $isError ? ShowError($message) : ShowMessage($message);
    }

    /**
     * @return bool
     */
    abstract function checkRequirements(): bool;

    /**
     * @return array
     */
    abstract function getResult(): array;
}