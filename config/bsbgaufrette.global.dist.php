<?php

/**
 * BsbGaufrette Configuration
 *
 * If you have a ./configs/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
return array(
    'bsb_gaufrette' => array(
        'filesystems' => array(
            'default' => array(
                'factory' => array(
                    'name' => 'local',
                    'options' => array(
                        'directory' => 'data/gaufrette_fs',
                        'create' => true
                    )
                ),
                'options' => array(
                    // 'filenamer' => 'default' /* Not yet implemented */
                )
            )
        ),
        'entity_map' => array(
            'SomeModule\Entity\SomeEntity' => array('preview' => 'default')
        )
    ),

    /**
     * Not yet implemented
     * @todo : automate this by looking at the map at bootstrap
     */
    // 'doctrine' => array(
    //    'eventmanager' => array(
    //        'orm_default' => array(
    //           'subscribers' => array('BsbGaufrette\Doctrine\FSSubscriber',)
    //        )
    //    ),
    //),
);