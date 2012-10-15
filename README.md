BsbGaufrette
===
BsbGaufrette is a Zendframework 2 module that provides easy configuration and access to the [Gaufrette Filesystem][1].

## Main Features

1. Exposing Gaufrette adapters via the ServiceManager via simple configuration. This feature is mostly done and not subject.

2. Ability to easily work with entity object that have associated files... This feature is still considered proof of concept. The *ultimate* goal is to get this to work with Doctrine entities.

*The Gaufrette api itself isn't stable yet and as such subject to change.*

## Installation

### with Composer

modify your ./composer.json so it includes

    "require": {
        "bushbaby/gaufrette" : "dev-master"
    }

then execute in your project root, which will install the module as well as the Gaufrette library.

    composer.phar update


## Activation

Finally modify the "modules' key in ./config/application.config.php so it contains "BsbGaufrette" to activate BsbGaufrette within your project.

## Configuration

Copy a provided configuration template to the autload directory to get started quickly.

    cp ./vendor/bushbaby/gaufrette/config/bsbgaufrette.global.dist.php \
       ./config/autoload/bsbgaufrette.global.php

## Usage
### the Gaufrette Manager

At this point you are able to get to the Gaufrette Manager via the service manager.

    $sm = $this->getServiceManager();
    $gm = $sm->get('bsbGaufretteManager'); // aliased as 'bsbFS'

This manager contains a plugin loader that knows how to instantiate all (currently only local and safelocal) of the adapters provided by the Gaufrette library via factories. Do that via the get($name) method.

    $fs = $gm->get('my_local_save_place');

When the name hasn't been configured an exception is thrown. Use the has($name) method when you need test for its existance.

    if ($gm->has('my_local_save_place')) {
        $fs = $gm->get('my_local_save_place');
    }

Any object returned is guaranteed to extend \Gaufrette\Adapter\Base and as such you can now do:

    $fs->write('my-book.txt', 'once upon a time');

### Configuring Gaufrette file systems

To be able to retrieve Gaufrette adapters via the Gaufrette manager you need to define them under a 'key' in the module configuration. Configuration pretty much explains itself.

    <?php
    array(
        'bsb_gaufrette' => array(
            'filesystems' => array(
                'my_local_save_place' => array(
                    'factory' => array(
                        'name' => 'local',
                            'options' => array(
                                'directory' => 'data/gaufrette_fs',
                                'create' => true
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

'my_local_save_place' is now a local file system in the data/gaufrette_fs directory which wil be created if it does not exist.

### Usage with Entity objects
* _As stated before before this work is incomplete and may change. But we want to be able to save, update and retrieve files that belong to entities transparently, by only working with the entity itself. The current implementation does not do that but are a few abstracted methods to make this possible._

Files by themselves are usually part of some sort of entity. Perhaps a picture for a contact or a zip file that belongs to a resume.


Suppose we have an entity that we want to store a preview image with.

    $manager = $sm->get('bsbFS');
    $manager->persistPreviewImage($entity, 'path/to/image.png');

The first part of the called method will determain the action; in this case 'persist'.

It will construct the filename and path to be used within the filesystem by looking at the second part of the called method (PreviewImage) the id of the entity and the chosen naming strategy.

It will figure out which filesystem it should use by looking at the entity_map defined in configuration.

This means that only condition placed on entities is that they must implement \BsbGaufrette\Doctrine\FSInterface so it is garanteed a getId() method is available. Without an known id the file cannot be associated with an entity.

    <?php
    return array(
        'bsb_gaufrette' => array(
            'entity_map' => array(
                'SomeModule\Entity\SomeEntity' => array('*' => 'my_local_save_place')
            )
        ),

Four _actions_ are implemented.

1. manager->hasXxxxx($entity) - does a file Xxxxx exists for this entity.
2. manager->peristXxxxx($entity, 'content') - save content to Xxxxx for this entity.
3. manager->retrieveXxxxx($entity) - retrieves a File object for Xxxxx this entity.
4. manager->filenameXxxxx($entity) - returns the constructed filename.

example

    if ($manager->hasPreviewImage($entity)) {
        $file = $manager->retrievePreviewImage($entity);
    }

    $manager->persistPreviewImage($entity, 'path/to/image.png');
    // $file instanceof Gaufrette\File

### file naming _(not yet implemented)_
Naming of the files is derived from the naming strategy chosen - but the method name and the id are both main ingredients.
With the filesystem configuration an options is available that specifies the naming strategy

### Limitations
 - Only the Local and SaveLocal adapters are implemented
 - can only persist/retrieve when an entity has an id
 -

## Planned
### Content
- support stream_resources
- persist a Gaufrette\File

### Naming strategies
- LeftPaddedPropLast : preview-image-0000000XX (default)
- DirectorySplit : /preview/image/0000000XX
- LeftPaddedPropLast : 0000000XX-preview-image
- Hashedup
- AddExt : preview-image-0000000XX.png

### Doctrine Events
- Hook into the enitymanager->flush method for storing and removing of files to the filesystems.
- Researching persist/remove on Doctrine::flush via Subscribers

### Future
 - Doctrine Annotation Support
 - Transparent en/decrypting, useful when saving files remote...
 - View helper and action to construct a url to an asset, dunno


  [1]: https://github.com/knpLabs/Gaufrette "Gaufrette"