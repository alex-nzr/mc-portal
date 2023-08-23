<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AgentManager.php
 * 17.01.2023 12:29
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Agent;

use CAgent;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;

/**
 * Class AgentManager
 * @package Cbit\Mc\Staffing\Agent
 */
class AgentManager
{
    protected static ?AgentManager $instance = null;
    protected array $agents = [];

    private function __construct(){
        $this->agents   = $this->getAgentsData();
    }

    public static function getInstance(): AgentManager
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function addAgents(): bool
    {
        foreach ($this->agents as $agent)
        {
            CAgent::AddAgent(
                $agent['handler'],
                ServiceManager::getModuleId(),
                $agent['period'],
                $agent['interval'],
                $agent['dateCheck'],
                $agent['active'],
                $agent['nextExec']
            );
        }
        return true;
    }

    public function removeAgents(): bool
    {
        CAgent::RemoveModuleAgents(ServiceManager::getModuleId());
        return true;
    }

    protected function getAgentsData(): array
    {
        return [
            [
                'handler'   => "\Cbit\Mc\Staffing\Agent\Common::updateProjectsData();",
                'period'    => "N",
                'interval'  => 1800,
                'dateCheck' => date("d.m.Y H:i:s", time() + 1860),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 1800),
            ],
            [
                'handler'   => "\Cbit\Mc\Staffing\Agent\Common::sendStaffingRecordsFromQueue();",
                'period'    => "N",
                'interval'  => 1800,
                'dateCheck' => date("d.m.Y H:i:s", time() + 1860),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 1800),
            ],
            [
                'handler'   => "\Cbit\Mc\Staffing\Agent\Common::updateStaffingRecordsFromQueue();",
                'period'    => "N",
                'interval'  => 1800,
                'dateCheck' => date("d.m.Y H:i:s", time() + 1860),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 1800),
            ],
            [
                'handler'   => "\Cbit\Mc\Staffing\Agent\Common::deleteStaffingRecordsFromQueue();",
                'period'    => "N",
                'interval'  => 1800,
                'dateCheck' => date("d.m.Y H:i:s", time() + 1860),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 1800),
            ],
            [
                'handler'   => "\CBit\Mc\Staffing\Agent\Common::updateAllUsersAvailabilityStatus();",
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y 02:00:00", time() + 86460),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y 02:00:00", time() + 86400),
            ],
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}