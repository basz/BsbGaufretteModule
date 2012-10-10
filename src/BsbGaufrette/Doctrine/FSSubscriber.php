<?php

namespace BsbGaufrette\Doctrine\FSSubscriber;

use BsbGaufrette\Service\FSService;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class FSSubscriber implements EventSubscriber
{
    /**
     * @var type FSService
     */
    protected $fs;

    public function getSubscribedEvents() {
//        return array(Events::postLoad, Events::prePersist);
        return array();
    }
    /**
     * @param DoctrineGaufretteFSService $fs
     */
    public function setFsService(FSService $fs) {
        $this->fs = $fs;
    }
//    public function prePersist(LifecycleEventArgs $args) {
//        $entity = $args->getEntity();
//        $entityManager = $args->getEntityManager();
//       
//        if ($entity instanceof CompositionEntity) {
//            $entity->setFilesystem($this->fs);
//        }
//    }
//    public function postLoad(LifecycleEventArgs $args) {
//        $entity = $args->getEntity();
//        $entityManager = $args->getEntityManager();
//       
//        if ($entity instanceof GaufretteFSInterface) {
//            $entity->setFilesystem($this->fs);
//        }
//    }
    
//    public function preUpdate(PreUpdateEventArgs $eventArgs)
//    {
//        if ($eventArgs->getEntity() instanceof GaufretteFSInterface) {
//            if ($eventArgs->hasChangedField('preview')) {
//               // $this->validateCreditCard($eventArgs->getNewValue('creditCard'));
//               var_dump('some change');die();
//            }
//        }
//    }

//    public function postPersist(LifecycleEventArgs $args)
//    {
//        $entity = $args->getEntity();
//        $entityManager = $args->getEntityManager();
//        
//        if ($entity instanceof CompositionEntity) {
//            // $this->fs->saveResource($entity);
//            if (is_resource($entity->getPreviewChanged())) {
//                $fp = fopen('gaufrette://SandalTool_PreviewStorage/' . $entity->getId(), "w");
//                $resource = $entity->getPreview();
//                rewind ( $resource );
//                while(!feof($resource)) {
//                    fwrite($fp, fread($resource, 8096), 8096);
//                }
//                fclose($fp);
//            }
//        }
//    }
}