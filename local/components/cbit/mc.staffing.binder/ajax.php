<?php
namespace Cbit\Mc\Staffing\Component;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Staffing\Internals\Debug\Logger;
use Cbit\Mc\Staffing\Service\Operation\Recruitment;
use Cbit\Mc\Staffing\Helper\Employment;
use CBitrixComponent;
use Exception;
use Throwable;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Class BinderComponentAjaxController
 * @package Cbit\Mc\Staffing\Component
 */
class BinderComponentAjaxController extends Controller
{
    private Binder $binder;

    protected function processBeforeAction(Action $action): bool
    {
        CBitrixComponent::includeComponentClass('cbit:mc.staffing.binder');
        $this->binder = new Binder();
        return true;
    }

    /**
     * @param int $limitCount
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFilteredUsersAction(int $limitCount): array
    {
        $filter = $this->binder->getUserFilterValues();
        $limit  = $limitCount * $this->binder->pageLimit;
        $users  = $this->binder->getUsersData($limit, $filter);
        return [
            'users'   => $users,
            'count'   => count($users),
            'total'   => $this->binder->getTotalUsers()
        ];
    }

    /**
     * @param int $limitCount
     * @return array
     * @throws \Exception
     */
    public function getFilteredProjectsAction(int $limitCount): array
    {
        $filter = $this->binder->getProjectFilterValues();
        $limit  = $limitCount * $this->binder->pageLimit;
        $projects  = $this->binder->getProjectsData($limit, $filter);
        return [
            'projects' => $projects,
            'count'    => count($projects),
            'total'    => $this->binder->getTotalProjects()
        ];
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getUserInfoAction(int $userId): array
    {
        $userData = UserTable::query()
            ->setSelect([
                'ID',
                'PERSONAL_PHOTO',
                'WORK_POSITION',
                'NAME',
                'LAST_NAME',
                Fields::getFioEnUfCode(),
                Fields::getBasePerDiemUfCode(),
            ])
            ->where('ID', '=', $userId)
            ->fetch();

        if (!empty($userData["PERSONAL_PHOTO"]))
        {
            $userData['PHOTO_SRC'] = User::getResizedAvatarByFileId((int)$userData["PERSONAL_PHOTO"]);
        }

        return [
            'perDiem'    => !empty($userData[Fields::getBasePerDiemUfCode()]) ? $userData[Fields::getBasePerDiemUfCode()] : 'no data',
            'photo'      => $userData['PHOTO_SRC'],
            'position'   => $userData["WORK_POSITION"],
            'name'       => User::getProfileViewLink($userId, (array)$userData),
            'employment' => Employment::getUserCurrentEmployment($userId),
        ];
    }

    /**
     * @return array
     */
    public function bindUserToProjectAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        try
        {
            $customPerDiemData = $this->prepareCustomPerDiemDataFromPost($postData);

            $operation = (new Recruitment\Bind())->configureParams($postData, $customPerDiemData);
            $result    = $operation->launch();

            if (!$result->isSuccess())
            {
                $this->addErrors($result->getErrors());
            }

            return [];
        }
        catch(Throwable $e)
        {
            $this->addError(new Error($e->getMessage()));
        }

        return [];
    }

    /**
     * @param array $postData
     * @return array
     * @throws \Exception
     */
    public function prepareCustomPerDiemDataFromPost(array $postData): array
    {
        $customPerDiemData = [];

        $arPdo    = $postData['PER_DIEM_EDIT_PDO'];
        $arFrom   = $postData['PER_DIEM_EDIT_DATE_FROM'];
        $arTo     = $postData['PER_DIEM_EDIT_DATE_TO'];
        $arReason = $postData['PER_DIEM_EDIT_REASON'];

        if (is_array($arPdo) && is_array($arFrom) && is_array($arTo) && is_array($arReason))
        {
            if ( (count($arPdo) !== count($arFrom))
                 || (count($arPdo) !== count($arTo))
                 || (count($arPdo) !== count($arReason))
            ){
                throw new Exception("count of all 'PER_DIEM_EDIT_...' arrays must be the same to match the values");
            }

            for($i = 0; $i < count($arPdo); $i++)
            {
                $customPerDiemData[] = [
                    $postData['PER_DIEM_EDIT_PDO'][$i],
                    $postData['PER_DIEM_EDIT_DATE_FROM'][$i],
                    $postData['PER_DIEM_EDIT_DATE_TO'][$i],
                    $postData['PER_DIEM_EDIT_REASON'][$i],
                ];
            }
        }

        return $customPerDiemData;
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
}
