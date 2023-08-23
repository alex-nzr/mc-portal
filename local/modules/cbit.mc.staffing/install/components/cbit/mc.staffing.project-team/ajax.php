<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */

namespace Cbit\Mc\Staffing\Component;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Cbit\Mc\Staffing\Service\Access\Permission;
use Cbit\Mc\Staffing\Service\Container;
use CBitrixComponent;
use Exception;

/**
 * Class ProjectTeamAjaxController
 * @package Cbit\Mc\Staffing\Component
 */
class ProjectTeamAjaxController extends Controller
{
    private ProjectTeam $component;
    private int $projectId;

    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     * @throws \Exception
     */
    protected function processBeforeAction(Action $action): bool
    {
        CBitrixComponent::includeComponentClass('cbit:mc.staffing.project-team');
        $this->component = new ProjectTeam();

        if (!Container::getInstance()->getUserPermissions()->hasPdStaffingPermissions())
        {
            throw new Exception('Operation blocked by permissions');
        }

        if (!$this->getRequest()->isPost() || !$this->getRequest()->getPost('signedParameters'))
        {
            throw new Exception('Component parameters not found');
        }

        $parameters = $this->getUnsignedParameters();

        if (isset($parameters['PROJECT_ID']))
        {
            $this->projectId = (int)$parameters['PROJECT_ID'];
        }
        else
        {
            throw new Exception('Required parameter projectId is missed');
        }
        return true;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function addNeedleEmployeeAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $postData['PROJECT_ID'] = $this->projectId;
        $result = $this->component->addNeedleEmployee($postData);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
        }
        return [
            'needle' => $this->component->getNeedleEmployees($this->projectId)
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function updateNeedleEmployeeAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $postData['PROJECT_ID'] = $this->projectId;
        $result = $this->component->updateNeedleEmployee($postData);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
        }
        return [
            'needle' => $this->component->getNeedleEmployees($this->projectId)
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function deleteNeedleEmployeeAction(int $id): array
    {
        $result = $this->component->deleteNeedleEmployee($id);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
        }
        return [
            'needle' => $this->component->getNeedleEmployees($this->projectId)
        ];
    }

    /**
     * @param int $recordId
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function deleteEmployeeFromProjectTeamAction(int $recordId, int $userId): array
    {
        $result = $this->component->deleteEmployeeFromProjectTeam($recordId, $userId, $this->projectId);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
        }
        return [
            'projectTeam' => $this->component->getProjectTeam($this->projectId),
            'needle'      => $this->component->getNeedleEmployees($this->projectId)
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function updateStaffingPeriodOfUserAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $postData['PROJECT_ID'] = $this->projectId;
        $result = $this->component->updateStaffingPeriodOfUser($postData);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
        }
        return [
            'projectTeam' => $this->component->getProjectTeam($this->projectId),
            'needle'      => $this->component->getNeedleEmployees($this->projectId)
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
