<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Factory.php
 * 19.02.2023 18:49
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\EditorConfig;

use Cbit\Mc\Expense\Internals\EditorConfig\Scheme\BusinessTripsConfig;
use Cbit\Mc\Expense\Internals\EditorConfig\Scheme\ReceiptConfig;
use Cbit\Mc\Expense\Internals\EditorConfig\Scheme\TreatYourselfBudgetConfig;
use Exception;
use Cbit\Mc\Core\Internals\Contract\IEditorConfig;

/**
 * @class Factory
 * @package Cbit\Mc\Expense\Internals\EditorConfig
 */
class Factory
{
    protected static ?Factory $instance = null;
    protected int    $entityTypeId;
    protected int    $typeId;

    /**
     * @param int $typeId
     * @param int $entityTypeId
     */
    protected function __construct(int $typeId, int $entityTypeId)
    {
        $this->typeId       = $typeId;
        $this->entityTypeId = $entityTypeId;
    }

    /**
     * @param int $typeId
     * @param int $entityTypeId
     * @return \Cbit\Mc\Expense\Internals\EditorConfig\Factory|null
     */
    public static function getInstance(int $typeId, int $entityTypeId): ?Factory
    {
        if (static::$instance === null)
        {
            static::$instance = new static($typeId, $entityTypeId);
        }
        return static::$instance;
    }
    private function __clone(){}
    public function __wakeup(){}

    /**
     * @param string $configType
     * @return \Cbit\Mc\Core\Internals\Contract\IEditorConfig|null
     * @throws \Exception
     */
    public function createConfig(string $configType): ?IEditorConfig
    {
        $config = null;

        if (ConfigType::isTypeSupported($configType))
        {
            switch ($configType)
            {
                case ConfigType::RECEIPT:
                    $config = new ReceiptConfig($this->typeId, $this->entityTypeId);
                    break;
                case ConfigType::BUSINESS_TRIPS:
                    $config = new BusinessTripsConfig($this->typeId, $this->entityTypeId);
                    break;
                case ConfigType::TR_YOUR_BUDGET:
                    $config = new TreatYourselfBudgetConfig($this->typeId, $this->entityTypeId);
                    break;
            }

            return $config;
        }
        else
        {
            throw new Exception("Can not resolve config by type '$configType'");
        }
    }
}
