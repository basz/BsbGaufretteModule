<?php

namespace BsbGaufrette\Service\Gaufrette;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Gaufrette\Adapter\SafeLocal;

class SafeLocalAdapterFactory extends LocalAdapterFactory
{
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SafeLocal($this->adapterOptions['directory'], $this->adapterOptions['create']);
    }
    
}
