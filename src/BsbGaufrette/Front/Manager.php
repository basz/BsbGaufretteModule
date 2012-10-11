<?php

namespace BsbGaufrette\Front;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BsbGaufrette\Gaufrette\AdapterFactoryManager;
use BsbGaufrette\Doctrine\FSInterface;

class Manager implements ServiceLocatorAwareInterface, ServiceLocatorInterface {

    const SET_METHOD_PREFIX = 'persist';
    const GET_METHOD_PREFIX = 'retrieve';
    const HAS_METHOD_PREFIX = 'has';

    /**
     * Associative array of named filesystems and it's options
     *
     * @var array
     */
    protected $filesystems;
    
    /**
     * Associative array that keeps track of entity class name, properties and 
     * a name of a filesystem.
     * 
     * @var array
     */
    protected $entityMap;

    /**
     * Manager that creates and holds instances of concrete filesystem implementations.
     *
     * It extends AbstractPluginManager but has a modified createFromFactory
     * method, so options can be passed via the contructor (just
     * like AbstractPluginManager::createFromInvokable).
     *
     * @var AdapterFactoryManager
     */
    protected $factoryManager;

    /**
     * Set filesystems configuration
     *
     * @param array $filesystems
     */
    public function setFilesystems(array $filesystems = array()) {
        $this->filesystems = $filesystems;
    }

    /**
     * Get filesystems configuration
     *
     * @return array
     */
    public function getFilesystems() {
        return $this->filesystems;
    }

    /**
     * @param array $entityMap
     */
    public function setEntityMap($entityMap)
    {
        $this->entityMap = $entityMap;
    }

    /**
     * @return array
     */
    public function getEntityMap()
    {
        return $this->entityMap;
    }
    
    protected function getFactoryManager() {
        if ($this->factoryManager == null) {
            $this->factoryManager = new AdapterFactoryManager();

            $this->factoryManager->setServiceLocator($this->getServiceLocator());
        }
        
        return $this->factoryManager;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    /**
     * Retrieve a registered filesystem
     *
     * @param  string  $name
     * @throws Exception\ServiceNotFoundException
     * @return object|array
     */
    public function get($name) {
        return $this->retrieveFilesystem($name);
    }

    /**
     * Check for a registered filesystem
     *
     * @param  string|array  $name
     * @return bool
     */
    public function has($name) {
        return $this->getFactoryManager()->has($name);
    }

    /**
     * Magic method design to handle getter and setter
     *
     * @param type $method
     * @param null $arguments
     * @return null
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @todo implement __isset && __unset
     */
    public function __call($method, $arguments) {
        // detect method and property name
        if (!preg_match(sprintf('/^(%s|%s|%s)(.+)$/', self::SET_METHOD_PREFIX, self::GET_METHOD_PREFIX, self::HAS_METHOD_PREFIX), $method, $match)) {
            throw new \BadMethodCallException(sprintf('Method call to "%s" must be in the form of with actionXxxxYyyyy(), received "%s"', __CLASS__, $method));
        }

        $method = $match[1] . 'Item';
        $propName = lcfirst($match[2]);

        array_unshift($arguments, $propName);

        // call method
        return call_user_func_array(array($this, $method), $arguments);
    }

    /**
     * Stores (or deleted) content in the filesystem
     *
     * @param $propName
     * @param \BsbGaufrette\Doctrine\FSInterface $entity
     * @param mixed $content
     * @return null
     * @throws \InvalidArgumentException empty identifier on entity
     * @todo add support for stream_resource
     */
    protected function persistItem($propName, FSInterface $entity, $content=null) {
        if(!$entity instanceof FSInterface) {
            throw new \InvalidArgumentException(sprintf(
                '%s: must implement "%s"', get_class($entity),
                'BsbGaufrette\Doctrine\FSInterface'
            ));
        }

        if ( $entity->getId() == null ) {
            throw new \InvalidArgumentException(sprintf(
                'Can\'t %s data without %s::getId() returning some sort of identifier.', self::SET_METHOD_PREFIX, get_class($entity)
            ));
        }

        $fsName = $this->lookupNameInEntityMap(get_class($entity), $propName);
        $fs = $this->retrieveFilesystem($fsName);

        $filename = $this->getFilename($propName, $entity->getId());

        // content is null we delete
        if ($content === null && $fs->exists($filename)) {
            $fs->delete($filename);
        }

        // content is file on disc to be read
        if (is_string($content)) {
            if (is_file($content)) {
                if (!is_readable($content)) {
                    // @todo
                } else {
                    $fs->write($filename, file_get_contents($content));
                }
            } else {
                $fs->write($filename, $content);
            }
        }

    }

    protected function hasItem($propName, FSInterface $entity) {
        if(!$entity instanceof FSInterface) {
            throw new \InvalidArgumentException(sprintf(
                '%s: must implement "%s"', get_class($entity),
                'BsbGaufrette\Doctrine\FSInterface'
            ));
        }

        if ( $entity->getId() == null ) {
            return false;
        }

        $fsName = $this->lookupNameInEntityMap(get_class($entity), $propName);
        $fs = $this->retrieveFilesystem($fsName);

        $filename = $this->getFilename($propName, $entity->getId());

        return !!$fs->exists($filename);
    }
    /**
     * @param $propName
     * @param \BsbGaufrette\Doctrine\FSInterface $entity
     * @return null
     * @throws \InvalidArgumentException empty identifier on entity
     */
    protected function retrieveItem($propName, FSInterface $entity) {
        if(!$entity instanceof FSInterface) {
            throw new \InvalidArgumentException(sprintf(
                '%s: must implement "%s"', get_class($entity),
                'BsbGaufrette\Doctrine\FSInterface'
            ));
        }

        if ( $entity->getId() == null ) {
            throw new \InvalidArgumentException(sprintf(
                'Can\'t %s data without %s::getId() returning some sort of identifier.', self::GET_METHOD_PREFIX, get_class($entity)
            ));
        }

        $fsName = $this->lookupNameInEntityMap(get_class($entity), $propName);
        $fs = $this->retrieveFilesystem($fsName);

        $filename = $this->getFilename($propName, $entity->getId());

        if (!$fs->exists($filename)) {
            return null;
        }

        return $this->fs->get($filename);
    }

    /**
     * Get a filename to be used by the adapter
     *
     * @todo implements various strategies with plugin loader
     * @param $propName
     * @param $id
     */
    protected function getFilename($propName, $id) {
        $filter = new \Zend\Filter\Word\CamelCaseToDash();

        return sprintf("%s-%s", $filter->filter($propName), str_pad($id, 8, '0', STR_PAD_LEFT));
    }

    protected function retrieveFilesystem($name) {
        if (!isset($this->filesystems[$name])) {
            throw new \Exception(sprintf('No filesystem configured for "%s".', $name));
        }
        
        if (!$this->getFactoryManager()->has($name)) {
            $filesystem = $this->filesystems[$name];
            $adapterName = $filesystem['factory']['name'];
            $factory = $this->getFactoryManager()->getFactoryAlias($adapterName);
            $this->getFactoryManager()->setFactory($name, $factory);

            $options = isset($filesystem['factory']['options'])? $filesystem['factory']['options'] : null;
            return $this->getFactoryManager()->get($name, $options);
        }

        return $this->getFactoryManager()->get($name);
    }
    
    /**
     * Find the name of the FS by looking up the fq classname as key in an array
     * 
     * When the value is an array property names are compared.
     * 
     * @param type $entityClass
     * @param type $propName
     * @return string Name of the FS
     */
    protected function lookupNameInEntityMap($entityClass, $propName) {
        if (!isset($this->entityMap[$entityClass])) {
            return;
        }
        
        // fqcn only?
        $map = $this->entityMap[$entityClass];
        if (is_string($map)) {
            return $map;
        }

        // fqcn and property name
        if (is_array($map)) {
            if (isset($map[$propName]) && is_string($map[$propName]) ) {
                return $map[$propName];
            }
            
            if (isset($map['*']) && is_string($map['*']) ) {
                return $map['*'];
            }
        }

            throw new \RuntimeException(sprintf(
                'No association could be found in map between a filesystem and the property "%s" on "%s".',
                $entityClass,
                $propName));
    }
}