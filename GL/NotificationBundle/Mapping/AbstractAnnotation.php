<?php

namespace GL\NotificationBundle\Mapping;

use GL\NotificationBundle\Notifications\NotificationInterface;

abstract class AbstractAnnotation implements AnnotationInterface
{
    /** @var NotificationInterface */
    protected $notification;
    /** @var string */
    protected $propertyName;

    public function setNotification(NotificationInterface $notification)
    {
        $this->notification = $notification;
    }
    public function getNotification()
    {
        return $this->notification;
    }
    public function setNotificationPropertyValue($value)
    {
        $property = $this->getPropertyName();
        $setMethod = 'set'.ucfirst($property);
        if ( !method_exists($this->notification, $setMethod) ){
            throw new \BadMethodCallException( sprintf('Method "%s" does not exist in %s', $setMethod, get_class($this->notification)) );
        }
        return $this->notification->$setMethod($value);
    }
    public function getNotificationPropertyValue($property = null)
    {
        if ( null == $property ){
            $property = $this->getPropertyName();
        }
        $getMethod = 'get'.ucfirst($property);
        if ( !method_exists($this->notification, $getMethod) ){
            throw new \BadMethodCallException( sprintf('Method "%s" does not exist in %s', $getMethod, get_class($this->notification)) );
        }
        return $this->notification->$getMethod();
    }

    public function setPropertyName($name)
    {
        $this->propertyName = $name;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }
}