<?php

namespace GL\NotificationBundle\Notifications;


use Doctrine\Common\Annotations\Reader;
use GL\NotificationBundle\Exception\NotImplementNotificationInterfaceException;
use GL\NotificationBundle\Exception\UnserializeBadClassException;

class NotificationsHandler
{
    private $annotationReader;
    private $serializeList;
    private $handlers;
    private $recipient;

    public function __construct($list = array(), $recipient, Reader $annotationReader)
    {
        $this->serializeList = $list;
        $this->recipient = $recipient;
        $this->annotationReader = $annotationReader;
        foreach( $this->serializeList as $index => $serializeNotification){
            $this->handlers[$index] = $this->unserialize($serializeNotification);
        }
        $this->postUnserialize();
        var_dump($this->handlers[0]); die();
    }

    private function getEmptyNotification($data)
    {
        $keys = array_keys($data);
        $class = array_shift($keys);
        if ( !class_exists($class) ){
            throw new UnserializeBadClassException( sprintf('In serialize data find class "%s" but it can not find in project.', $class) );
        }

        try{ $object = new $class(); }catch (\Exception $e){
            throw new UnserializeBadClassException( sprintf('Can\'t create empty class "%s".', $class) );
        }

        if ( !$object instanceof NotificationInterface ){
            throw new NotImplementNotificationInterfaceException( sprintf('Class "%s" do not implement NotificationInterface.', $class) );
        }
        return $object;
    }

    private function unserialize($serialize_data)
    {
        $data = json_decode($serialize_data, true);
        if ( null == $data ){
            return null;
        }

        $notification = $this->getEmptyNotification($data);
        $handler = new NotificationHandler($notification, $this->annotationReader);
        $handler->setRecipient($this->recipient);
        $handler->unserializeNotificationFromArray($data);
        return $handler;
    }

    private function postUnserialize()
    {
        $groups = array();
        foreach( $this->handlers as $notificationHandler ){

        }
    }

    public function getNotifications()
    {
        return array();
    }
}