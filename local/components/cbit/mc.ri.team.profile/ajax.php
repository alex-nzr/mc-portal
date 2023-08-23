<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */

namespace Cbit\Mc\RI\Component;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Service\Access\Permission;
use Cbit\Mc\RI\Service\Container;
use CBitrixComponent;
use Exception;
use Throwable;

class RITeamProfileAjaxController extends Controller
{
    /**
     * @var \Cbit\Mc\RI\Component\TeamProfile
     */
    private TeamProfile $component;

    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     * @throws \Exception
     */
    protected function processBeforeAction(Action $action): bool
    {
        CBitrixComponent::includeComponentClass('cbit:mc.ri.team.profile');
        $this->component = new TeamProfile();

        return true;
    }

    /**
     * @param int $id
     * @return array
     */
    public function updateRICoordinatorAction(int $id): array
    {
        try
        {
            if (!Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForRi())
            {
                throw new Exception('Operation blocked by permissions');
            }

            if (0 >= $id)
            {
                throw new Exception('Coordinator id must be greater than 0');
            }
            Option::set(ServiceManager::getModuleId(), Constants::OPTION_KEY_COORDINATOR_ID, $id);

            return [
                'coordinator' => $this->component->getCurrentCoordinatorData()
            ];
        }
        catch(Throwable $e)
        {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * @param string $text
     * @return array
     */
    public function updateRITeamDescriptionAction(string $text): array
    {
        try
        {
            if (!Container::getInstance()->getUserPermissions()->hasUserRiManagerPermissions())
            {
                throw new Exception('Operation blocked by permissions');
            }

            if (empty($text))
            {
                throw new Exception('Team description can not be empty');
            }
            Option::set(ServiceManager::getModuleId(), Constants::OPTION_KEY_TEAM_DESCRIPTION, $text);

            return [];
        }
        catch(Throwable $e)
        {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @return array
     */
    public function updateRITeamWorkTimeAction(string $from, string $to): array
    {
        try
        {
            if (!Container::getInstance()->getUserPermissions()->hasUserRiManagerPermissions())
            {
                throw new Exception('Operation blocked by permissions');
            }

            if (empty($from) || empty($to))
            {
                throw new Exception('Team work time can not be empty');
            }
            Option::set(
                ServiceManager::getModuleId(),
                Constants::OPTION_KEY_TEAM_WORK_TIME,
                "$from - $to"
            );

            return [];
        }
        catch(Throwable $e)
        {
            $this->addError(new Error($e->getMessage()));
            return [];
        }
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
