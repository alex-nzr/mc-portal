<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Employment.php
 * 19.01.2023 18:48
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Integration\Staffing;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;

/**
 * @class Employment
 * @package Cbit\Mc\RI\Service\Integration\Staffing
 */
class Employment
{
    /**
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public static function getCurrentUserProjectsIds(int $userId): array
    {
        $projectIds = [];

        if (Loader::includeModule(Constants::STAFFING_MODULE_ID))
        {
            $data = UserProjectTable::query()
                ->setSelect(['ID', 'PROJECT_ID'])
                ->setFilter([
                    '=USER_ID'              => $userId,
                    '!=DELETION_MARK'       => 'Y',
                    '<=STAFFING_DATE_FROM'  => new Date(),
                    '>=STAFFING_DATE_TO'    => new Date(),
                ])
                ->fetchAll();

            foreach ($data as $item)
            {
                $projectIds[] = $item['PROJECT_ID'];
            }
        }

        return $projectIds;
    }
}