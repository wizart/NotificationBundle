<?php

namespace GL\NotificationBundle\Notifications;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use GL\NotificationBundle\Mapping\ColumnTypeInterface;
use GL\NotificationBundle\Mapping\Recipient;

class NotificationHandler
{
    private $id;
    private $annotationReader;

    private $properties;

    /** @var PropertyHandler */
    private $recipient;

    private $columns;

    /** @var NotificationInterface */
    private $notification;

    public function __construct(NotificationInterface $notification, Reader $annotationReader)
    {
        $this->notification = $notification;
        $this->annotationReader = $annotationReader;
        $this->columns = new ArrayCollection();
        $this->buildNotificationHandler();
    }

    protected function buildNotificationHandler()
    {
        $notification = $this->notification;
        $reader = $this->annotationReader;
        $object = new \ReflectionObject($notification);
        $readAnnotation = function(\ReflectionProperty $property) use ($reader, $notification){
            $annotations = $reader->getPropertyAnnotations($property);
            if ( empty($annotations) ){
                return null;
            } else {
                $propertyHandler = new PropertyHandler();
                foreach($annotations as $annotation){
                    $annotation->setPropertyName($property->getName());
                    $annotation->setNotification($notification);
                    if ( $annotation instanceof ColumnTypeInterface ){
                        $propertyHandler->setColumnAnnotation($annotation);
                    } elseif ( $annotation instanceof Recipient ) {
                        $propertyHandler->setRecipientAnnotation($annotation);
                    } else {
                        $propertyHandler->addOtherAnnotation($annotation);
                    }
                }
                return $propertyHandler;
            }
        };
        $properties = array_map($readAnnotation, $object->getProperties());
        $this->setPropertiesHandlers($properties);
    }

    public function setPropertiesHandlers($properties)
    {
        $this->properties = array_filter($properties);
        foreach( $this->properties as $property ){
            if ( $property->hasColumnAnnotation() ){
                $this->columns->add($property);
            } elseif ( $property->hasRecipientAnnotation() ){
                if ( null != $this->recipient ){
                    throw new AnnotationException( sprintf('The "%s" Notification has two or more recipient annotation.', get_class($this->notification)) );
                }
                $this->recipient = $property;
            }
        }
        if ( null == $this->recipient ){
            throw new AnnotationException( sprintf('The "%s" Notification has any recipient annotation.', get_class($this->notification)) );
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return $this->id;
    }

    public function getRecipient()
    {
        return $this->recipient->getRecipientAnnotation()->getNotificationPropertyValue();
    }
    public function setRecipient($recipient)
    {
        return $this->recipient->getRecipientAnnotation()->setNotificationPropertyValue($recipient);
    }


    public function serializeNotification()
    {
        $data = array();
        foreach( $this->columns as $column ){
            $annotation = $column->getColumnAnnotation();
            $data[$annotation->getShortName()] = $annotation->serialize();
        }
        $data = array(get_class($this->notification) => $data);
        return json_encode($data);
    }
    public function unserializeNotificationFromArray(array $data)
    {
        $data = $data[get_class($this->notification)];
        foreach( $this->columns as $column ){
            $annotation = $column->getColumnAnnotation();
            if ( isset($data[$annotation->getShortName()]) ){
                $annotation->unserialize( $data[$annotation->getShortName()] );
            }
        }
    }
}