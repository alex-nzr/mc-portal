<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Container.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Subscription\Service;

use Bitrix\Main\DI\ServiceLocator;
use Cbit\Mc\Subscription\Entity;
use Cbit\Mc\Subscription\Internals\Control\ServiceManager;
use Cbit\Mc\Subscription\Service\Access\UserPermissions;
use Cbit\Mc\Subscription\Service\Localization\Messages;

/**
 * Class Container
 * @package Cbit\Mc\Subscription\Service
 */
class Container extends \Bitrix\Crm\Service\Container
{
    /**
     * @return \Cbit\Mc\Subscription\Service\Container
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public static function getInstance(): Container
    {
        $container = ServiceLocator::getInstance()->get('crm.service.container');
        if (!($container instanceof Container)){
            $container = new static();
        }
        return $container;
    }

    /**
     * @return \Cbit\Mc\Subscription\Service\Router
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public function getRouter(): Router
    {
        $router = ServiceLocator::getInstance()->get('crm.service.router');
        if (!($router instanceof Router)){
            $router = new Router();
        }
        return $router;
    }

    /**
     * @throws \Exception
     */
    public function getFactory(int $entityTypeId): ?\Bitrix\Crm\Service\Factory
    {
        if ($entityTypeId === Entity\Dynamic::getInstance()->getEntityTypeId())
        {
            $identifier = ServiceManager::getModuleId() . '.dynamicFactory';//Some unique identifier for service
            if(!ServiceLocator::getInstance()->has($identifier))
            {
                $type = $this->getTypeByEntityTypeId($entityTypeId);
                if($type)
                {
                    $factory = new Factory($type);
                    ServiceLocator::getInstance()->addInstance(
                        $identifier,
                        $factory
                    );
                    return $factory;
                }
                return null;
            }
            return ServiceLocator::getInstance()->get($identifier);
        }

        return parent::getFactory($entityTypeId);
    }

    /**
     * @param int|null $userId
     * @return \Cbit\Mc\Subscription\Service\Access\UserPermissions
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Exception
     */
    public function getUserPermissions(?int $userId = null): UserPermissions
    {
        if($userId === null)
        {
            $userId = $this->getContext()->getUserId();
        }

        $identifier = static::getIdentifierByClassName(UserPermissions::class, [$userId]);

        if(!ServiceLocator::getInstance()->has($identifier))
        {
            $userPermissions = new UserPermissions($userId);
            ServiceLocator::getInstance()->addInstance($identifier, $userPermissions);
        }

        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @return \Cbit\Mc\Subscription\Service\Localization\Messages
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public function getLocalization(): Messages
    {
        $loc = ServiceLocator::getInstance()->get('crm.service.localization');
        if (!($loc instanceof Messages)){
            $loc = new Messages();
        }
        return $loc;
    }

    /**
     * @return \Cbit\Mc\Subscription\Service\Context
     * @throws \Exception
     */
    public function getContext(): Context
    {
        $identifier = static::getIdentifierByClassName(Context::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new Context);
        }
        return ServiceLocator::getInstance()->get($identifier);
    }
}