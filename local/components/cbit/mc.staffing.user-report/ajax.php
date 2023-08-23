<?php
namespace Cbit\Mc\Staffing\Component;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use CBitrixComponent;
use Exception;

/**
 * Class UserReportAjaxController
 * @package Cbit\Mc\Staffing\Component
 */
class UserReportAjaxController extends Controller
{
    protected int $userId;
    private UserReport $report;

    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     * @throws \Exception
     */
    protected function processBeforeAction(Action $action): bool
    {
        CBitrixComponent::includeComponentClass('cbit:mc.staffing.user-report');
        $this->report = new UserReport();

        if (!$this->getRequest()->isPost() || !$this->getRequest()->getPost('signedParameters'))
        {
            return false;
        }

        $parameters = $this->getUnsignedParameters();

        if (isset($parameters['USER_ID']))
        {
            $this->userId = (int)$parameters['ID'];
        }
        else
        {
            throw new Exception('Required parameter userId is missed');
        }
        return true;
    }

    /**
     * @param int|null $lastId
     * @return array
     * @throws \Exception
     */
    public function getMoreProjectsAction(?int $lastId = null): array
    {
        if (empty($lastId))
        {
            $lastId = 0;
        }
        $countPerPage = $this->report->getPageLimit();
        $projects = $this->report->getUserProjectsData($this->userId, $lastId);
        return [
            'projects'=> $projects,
            'lastId'  => !empty($projects) ? (int)end($projects)['ID'] : $lastId,
            'isFinal' => (count($projects) < $countPerPage) ? 'Y' : 'N'
        ];
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
