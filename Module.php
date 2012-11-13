<?php

//

/**
 * Bushbaby Gaufrette - A module provides easy configuration and access to Gaufrette Filesystem
 *
 * @link      http://github.com/basz/Gaufrette
 * @copyright Copyright (c) 2012 Bushbaby Multimedia
 * @license   see LICENSE document
 */

namespace BsbGaufrette;

class Module {
    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'aliases' => array(
                'bsbFS' => 'bsb_gaufrette_fs',
            ),
            'factories' => array(
                'bsb_gaufrette_fs' => 'BsbGaufrette\Service\ManagerFactory',
                /* not yet implemented */
                // 'bsb_gaufrette_fs_doctrine_subscriber_factory' => 'BsbGaufrette\Doctrine\Gaufrette\FSSubscriberFactory',
            ),
        );
    }

}
