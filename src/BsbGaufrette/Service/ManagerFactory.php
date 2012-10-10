<?php

namespace BsbGaufrette\Service;

use BsbGaufrette\Front\Manager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('Config');
        $config         = isset($config['bsb_gaufrette']) ? $config['bsb_gaufrette'] : array();
        
        $service = new Manager();

        $service->setServiceLocator($serviceLocator);

        if (isset($config['filesystems'])) {
            $service->setFilesystems($config['filesystems']);
        }
        if (isset($config['entity_map'])) {
            $service->setEntityMap($config['entity_map']);
        }

        return $service;
    }
}
