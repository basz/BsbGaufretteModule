<?php

namespace BsbGaufrette\Doctrine\Service\FSSubscriberFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BsbGaufrette\Doctrine\FSSubscriber;

class FSSubscriberFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new FSSubscriber();
        
        $service->setFsService($serviceLocator->get('bsbFS'));

        return $service;
    }

}
