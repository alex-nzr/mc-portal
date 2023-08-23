<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AgentManager.php
 * 21.11.2022 12:29
 * ==================================================
 */

namespace Cbit\Mc\Timesheets\Agent;

use CAgent;
use Cbit\Mc\Timesheets\Internals\Control\ServiceManager;

/**
 * Class AgentManager
 * @package Cbit\Mc\Timesheets\Agent
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
                'handler'   => "\Cbit\Mc\Timesheets\Agent\Common::updateActivitiesRegistry();",
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y H:i:s", time() + 86460),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 86400),
            ],
            [
                'handler'   => "\Cbit\Mc\Timesheets\Agent\Common::updateIndustriesRegistry();",
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y H:i:s", time() + 86460),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 86400),
            ],
            [
                'handler'   => "\Cbit\Mc\Timesheets\Agent\Common::updateFunctionsRegistry();",
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y H:i:s", time() + 86460),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 86400),
            ],
            [
                'handler'   => "\Cbit\Mc\Timesheets\Agent\Common::updateTeamCompositionsRegistry();",
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y H:i:s", time() + 86460),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 86400),
            ],
            [
                'handler'   => "\Cbit\Mc\Timesheets\Agent\Common::updateEnumerationsData();",
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y H:i:s", time() + 86460),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 86400),
            ],
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}