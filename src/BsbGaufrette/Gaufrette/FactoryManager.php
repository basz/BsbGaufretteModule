<?php

namespace BsbGaufrette\Gaufrette;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;

class FactoryManager extends AbstractPluginManager {

    /**
     * Default set of factories classes
     *
     * @var array
     */
    protected $factoryAliases = array(
        'local' => 'BsbGaufrette\Service\Gaufrette\LocalAdapterFactory',
        'safelocal' => 'BsbGaufrette\Service\Gaufrette\SafeLocalAdapterFactory',
    );

    /**
     * Do not share by default
     *
     * @var array
     */
    protected $shareByDefault = true;

    /**
     * Whether or not to auto-add a class as an invokable class if it exists
     *
     * @var bool
     */
    protected $autoAddInvokableClass = true;

    public function getFactoryAlias($alias) {
        return isset($this->factoryAliases[$alias]) ? $this->factoryAliases[$alias] : $alias;
    }

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance of StorageInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin) {
        if ($plugin instanceof \Gaufrette\Adapter\Base) {
            // we're okay
            return;
        }

        throw new \RuntimeException(sprintf(
                        'Plugin of type %s is invalid; must implement \Gaufrette\Adapter\Base', (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }

    /**
     * Attempt to create an instance via a factory
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return mixed
     * @throws Exception\ServiceNotCreatedException If factory is not callable
     */
    protected function createFromFactory($canonicalName, $requestedName) {

        $factory = $this->factories[$canonicalName];
        if (is_string($factory) && class_exists($factory, true)) {
            if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
                $factory = new $factory();
            } else {
                $factory = new $factory($this->creationOptions);
            }

            $this->factories[$canonicalName] = $factory;
        }
        if ($factory instanceof FactoryInterface) {
            $instance = $this->createServiceViaCallback(array($factory, 'createService'), $canonicalName, $requestedName);
        } elseif (is_callable($factory)) {
            $instance = $this->createServiceViaCallback($factory, $canonicalName, $requestedName);
        } else {
            throw new Exception\ServiceNotCreatedException(sprintf(
                            'While attempting to create %s%s an invalid factory was registered for this instance type.', $canonicalName, ($requestedName ? '(alias: ' . $requestedName . ')' : '')
            ));
        }
        return $instance;
    }

}

