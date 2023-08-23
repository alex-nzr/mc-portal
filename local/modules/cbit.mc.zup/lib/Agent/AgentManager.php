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

namespace Cbit\Mc\Zup\Agent;

use CAgent;
use Cbit\Mc\Zup\Internals\Control\ServiceManager;

/**
 * Class AgentManager
 * @package Cbit\Mc\Zup\Agent
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
                'handler'   => "\CBit\Mc\Zup\Agent\Common::updateEducationTypes();",
                'period'    => "N",
                'interval'  => 3600,
                'dateCheck' => date("d.m.Y H:i:s", time() + 3660),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 3600),
            ],
            [
                'handler'   => "\CBit\Mc\Zup\Agent\Common::updateEmployeeEducation();",
                'period'    => "N",
                'interval'  => 3600,
                'dateCheck' => date("d.m.Y H:i:s", time() + 3660),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 3600),
            ],
            [
                'handler'   => "\CBit\Mc\Zup\Agent\Common::sendEmployeeEducation();",
                'period'    => "N",
                'interval'  => 300,
                'dateCheck' => date("d.m.Y H:i:s", time() + 360),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 300),
            ],
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}