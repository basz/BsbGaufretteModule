<?php

namespace BsbGaufrette\Service\Gaufrette;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Gaufrette\Adapter\Local;

class LocalAdapterFactory implements FactoryInterface
{
    /**
     *
     * @var array $options
     */
    protected $adapterOptions;
    
    public function __construct(array $options = array()) {
        $this->adapterOptions = $options;
        
        if (!isset($this->adapterOptions['directory']) || !is_string($this->adapterOptions['directory'])) {
            $this->adapterOptions['directory'] = 'data/gaufrette_fs';
        }
        
        if (!isset($this->adapterOptions['create']) || !is_bool($this->adapterOptions['create'])) {
            $this->adapterOptions['create'] = null;
        }
    }
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Local($this->adapterOptions['directory'], $this->adapterOptions['create']);
    }
    
}
