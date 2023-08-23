<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Delete.php
 * 07.12.2022 12:03
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
 * Class Delete
 * @package Cbit\Mc\Staffing\Service\Operation\Recruitment
 */
class Delete extends Base
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

            $markAsDeletedResult = UserProjectTable::update($this->recordId, [
                'DELETION_MARK' => 'Y',
                'USER_ID'       => $this->userId
            ]);

            if (!$markAsDeletedResult->isSuccess())
            {
                $result->addErrors($markAsDeletedResult->getErrors());
            }
            else
            {
                $updNeedResult = Employment::updateNeed($item['RELATED_NEED_ID'], [
                    'ACTIVE' => 'Y'
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