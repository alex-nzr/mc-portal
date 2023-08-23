<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - BusinessTripsConfig.php
 * 23.02.2023 20:04
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\EditorConfig\Scheme;

use Cbit\Mc\Expense\Service\EditorAdapter;

/**
 * @class BusinessTripsConfig
 * @package Cbit\Mc\Expense\Internals\EditorConfig\Scheme
 */
class BusinessTripsConfig extends CommonConfig
{
    /**
     * @return array
     */
    protected function getConfigScheme(): array
    {
        $commonScheme = parent::getConfigScheme();
        $commonScheme[] = [
            'name' =>  "daily_allowance",
            'title' => "Daily allowance",
            'type' => "section",
            'elements' => [
                [
                    'name' => "UF_CRM_". $this->typeId ."_TRIP_DATE",
                    'optionFlags' => '1'
                ],
                [
                    'name' => EditorAdapter::FIELD_OPPORTUNITY,
                    'optionFlags' => '1'
                ],
                [
                    'name' => "UF_CRM_". $this->typeId ."_DEPARTURE_DATE",
                    'optionFlags' => '1'
                ],
                [
                    'name' => "UF_CRM_". $this->typeId ."_ARRIVAL_DATE",
                    'optionFlags' => '1'
                ],
                [
                    'name' => "UF_CRM_". $this->typeId ."_CITY",
                    'optionFlags' => '1'
                ],
            ]
        ];

        return $commonScheme;
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        return array_merge(parent::getRequiredFields(), [
            'UF_CRM_' . $this->typeId . '_DEPARTURE_DATE',
            'UF_CRM_' . $this->typeId . '_ARRIVAL_DATE',
            'UF_CRM_' . $this->typeId . '_CITY',
            'UF_CRM_' . $this->typeId . '_CHARGE_CODE',
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReadonlyFields(): array
    {
        return parent::getReadonlyFields();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHiddenFields(): array
    {
        return parent::getHiddenFields();
    }
}