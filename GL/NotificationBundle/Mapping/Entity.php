<?php

namespace GL\NotificationBundle\Mapping;

/**
 * @Annotation
 */
final class Entity extends AbstractColumnType
{
    /**
     * @var string
     */
    public $repository;

    /**
     * @var string
     */
    public $findMethod = "find";

    public $groupHandler = 'gl_notification.grouphandler.entity';

    /**
     * @var mixed
     */
    public $serializeProperties = array("id");

    public function serialize()
    {
        $entity = $this->getNotificationPropertyValue();
        $serializeData = array();
        foreach( $this->serializeProperties as $property ){
            $getMethod = 'get'.ucfirst($property);
            if ( !method_exists($entity, $getMethod) ){
                throw new \BadMethodCallException( sprintf('Method "%s" does not exist in %s', $getMethod, get_class($entity)) );
            }
            $serializeData[$property] = $entity->$getMethod();
        }
        return $serializeData;
    }
}