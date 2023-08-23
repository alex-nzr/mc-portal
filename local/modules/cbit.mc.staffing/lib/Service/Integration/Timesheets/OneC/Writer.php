<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Writer.php
 * 16.12.2022 18:06
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Service\Integration\Timesheets\OneC;

use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;

/**
 * Class Writer
 * @package Cbit\Mc\Staffing\Service\Integration\Timesheets\OneC
 */
class Writer extends \Cbit\Mc\Timesheets\Service\Integration\OneC\Writer
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function sendNewStaffingData(): Result
    {
        $data = UserProjectTable::query()
            ->whereNot('SENT_TO_ONE_C', '=', 'Y')
            ->whereNot('DELETION_MARK', '=', 'Y')
            ->setSelect([
                'ID', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO', 'USER_PER_DIEM', 'PER_DIEM_REASON', 'PER_DIEM_COMMENT',
                'USER_FMNO' => 'USER.'.Fields::getFmnoUfCode(),
                'BASE_PER_DIEM' => 'USER.'.Fields::getBasePerDiemUfCode(),
                'PROJECT_UUID' => 'PROJECT.XML_ID',
            ])
            ->fetchAll();

        $oneCData = [];
        foreach ($data as $item)
        {
            if ((int)$item['BASE_PER_DIEM'] > 0 && (int)$item['USER_PER_DIEM'] > 0)
            {
                $perDiemRatio    = round((int)$item['USER_PER_DIEM'] / (int)$item['BASE_PER_DIEM'], 2) * 100;
                $discountPercent = $perDiemRatio - 100;
            }
            else
            {
                $discountPercent = 0;
            }

            $oneCData[$item['ID']] = [
                "Employee"      => [
                    'FMNO' => $item['USER_FMNO']
                ],
                "Project" => [
                    "UUID" => $item['PROJECT_UUID']
                ],
                "From" => ($item['STAFFING_DATE_FROM'] instanceof Date)
                    ? ($item['STAFFING_DATE_FROM'])->format("Y-m-d\TH:m:s") : '',
                "To"   => ($item['STAFFING_DATE_TO'] instanceof Date)
                    ? ($item['STAFFING_DATE_TO'])->format("Y-m-d\TH:m:s") : '',
                "PerDiem"               => (int)$item['USER_PER_DIEM'],
                "BasePerDiem"           => (int)$item['BASE_PER_DIEM'],
                "Discount"              => $discountPercent,
                "ReasonForTheDiscount"  => $item['PER_DIEM_REASON'],
                "Comment"               => $item['PER_DIEM_COMMENT'],
            ];
        }

        $finalRes = new Result();
        if (!empty($oneCData))
        {
            foreach ($oneCData as $id => $item)
            {
                $sendRes = $this->sendStaffingRecord((int)$id, $item);
                if ($sendRes->isSuccess())
                {
                    $updRes = UserProjectTable::update($id, [
                        'SENT_TO_ONE_C' => 'Y',
                    ]);
                    if (!$updRes->isSuccess())
                    {
                        $finalRes->addErrors($updRes->getErrors());
                    }
                }
                else
                {
                    $finalRes->addErrors($sendRes->getErrors());
                }
            }
        }
        return $finalRes;
    }

    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function updateStaffingData(): Result
    {
        $data = UserProjectTable::query()
            ->whereNot('UPDATED_IN_ONE_C', '=', 'Y')
            ->whereNot('DELETION_MARK', '=', 'Y')
            ->setSelect([
                'ID', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO'
            ])
            ->fetchAll();

        $oneCData = [];
        foreach ($data as $item)
        {
            $oneCData[$item['ID']] = [
                "From" => ($item['STAFFING_DATE_FROM'] instanceof Date)
                    ? ($item['STAFFING_DATE_FROM'])->format("Y-m-d\TH:m:s") : '',
                "To"   => ($item['STAFFING_DATE_TO'] instanceof Date)
                    ? ($item['STAFFING_DATE_TO'])->format("Y-m-d\TH:m:s") : '',
                "Comment" => '',
            ];
        }

        $finalRes = new Result();
        if (!empty($oneCData))
        {
            foreach ($oneCData as $id => $item)
            {
                $sendRes = $this->updateStaffingRecord((int)$id, $item);
                if ($sendRes->isSuccess())
                {
                    $updRes = UserProjectTable::update($id, [
                        'UPDATED_IN_ONE_C' => 'Y',
                    ]);
                    if (!$updRes->isSuccess())
                    {
                        $finalRes->addErrors($updRes->getErrors());
                    }
                }
                else
                {
                    $finalRes->addErrors($sendRes->getErrors());
                }
            }
        }
        return $finalRes;
    }

    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function deleteStaffingData(): Result
    {
        $data = UserProjectTable::query()
            ->where('DELETION_MARK', '=', 'Y')
            ->setSelect([ 'ID' ])
            ->fetchAll();

        $finalRes = new Result();
        foreach ($data as $item)
        {
            $sendRes = $this->deleteStaffingRecord((int)$item['ID']);
            if ($sendRes->isSuccess())
            {
                $delRes = UserProjectTable::delete($item['ID']);
                if (!$delRes->isSuccess())
                {
                    $finalRes->addErrors($delRes->getErrors());
                }
            }
            else
            {
                $finalRes->addErrors($sendRes->getErrors());
            }
        }
        return $finalRes;
    }
}