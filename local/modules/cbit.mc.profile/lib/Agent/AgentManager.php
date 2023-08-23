<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AgentManager.php
 * 25.10.2022 17:29
 * ==================================================
 */

namespace Cbit\Mc\Profile\Agent;

use CAgent;
use Cbit\Mc\Profile\Internals\Control\ServiceManager;

/**
 * Class AgentManager
 * @package Cbit\Mc\Profile\Agent
 */
class AgentManager
{
    protected static ?AgentManager $instance = null;
    protected string $moduleId = '';
    protected array $agents = [];

    private function __construct(){
        $this->agents   = $this->getAgentsData();
        $this->moduleId = ServiceManager::getModuleId();
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
                $agent['module'],
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
        CAgent::RemoveModuleAgents($this->moduleId);
        return true;
    }

    protected function getAgentsData(): array
    {
        return [
            /*[
                'handler'   => "\CBit\Mc\Profile\Agent\Common::someFunc();",
                'module'    => $this->moduleId,
                'period'    => "N",
                'interval'  => 300,
                'dateCheck' => date("d.m.Y H:i:s", time() + 360),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 300),
            ],*/
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}