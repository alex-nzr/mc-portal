<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ${FILE_NAME}
 * 12.12.2022 12:44
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Service\Operation\Recruitment;


use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Cbit\Mc\Staffing\Helper\Employment;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;
use Cbit\Mc\Staffing\Service\Access\Permission;
use Exception;
use Throwable;

/**
 * Class Update
 * @package Cbit\Mc\Staffing\Service\Operation\Recruitment
 */
class Update extends Base
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function launch(): Result
    {
        $result = new Result();

        try
        {
            $item = UserProjectTable::query()
                ->setSelect(['*'])
                ->where('ID', '=', $this->recordId)
                ->fetch();

            if (empty($item))
            {
                throw new Exception("Relation №'$this->recordId' of user '$this->userId' to project '$this->projectId' not found");
            }

            $updResult = UserProjectTable::update($this->recordId, [
                'USER_ID'            => $this->userId,
                'USER_ROLE'          => $this->userRole,
                'USER_PER_DIEM'      => $this->perDiemValue,
                'STAFFING_DATE_FROM' => $this->from,
                'STAFFING_DATE_TO'   => $this->to,
                'UPDATED_IN_ONE_C'   => 'N'
            ]);
            if (!$updResult->isSuccess())
            {
                $result->addErrors($updResult->getErrors());
            }
            else
            {
                $updNeedResult = Employment::updateNeed($item['RELATED_NEED_ID'], [
                    'USER_ROLE'        => $this->userRole,
                    'NEEDLE_DATE_FROM' => $this->from,
                    'NEEDLE_DATE_TO'   => $this->to
                ]);
                if (!$updNeedResult->isSuccess())
                {
                    $result->addErrors($updNeedResult->getErrors());
                }
            }
        }
        catch(Throwable $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }
}