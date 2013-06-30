<?php

namespace GL\NotificationBundle\Lib;

use GL\NotificationBundle\Exception\MethodNotFoundException;
use GL\NotificationBundle\Exception\UnserializeBadClassException;
use GL\NotificationBundle\Mapping\ColumnTypeInterface;

class ProxyNotification
{
    /**
     * @var Notifications
     */
    private $parent;
    private $_notification;
    private $_data;
    private $_config;
    private $index;

    private $recipients;

    public function __construct(Notifications $parent)
    {
        $this->parent = $parent;
    }

    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }
    public function setNotification($notification)
    {
        $this->_notification = $notification;
        $this->setConfig($this->parent->loadAnnotationConfig($this->_notification));
    }
    public function getNotification()
    {
        return $this->_notification;
    }
    public function setData($data)
    {
        $this->_data = $data;
    }
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    public function prepareToStore()
    {
        $properties_data = $this->prepareToStoreProperties();
        $class_name = get_class($this->_notification);
        if ( $class_alias = $this->parent->getAliasByMappedClass($class_name) ){
            $class_name = $class_alias;
        }
        $this->_data = json_encode(array( $class_name => $properties_data ));
    }

    public function prepareToRestore()
    {
        $data = json_decode($this->_data, true);
        $keys = array_keys($data);
        $class_alias = array_shift($keys);
        $class = $this->parent->getMappedClassByAlias($class_alias);

        if ( !class_exists($class) ){
            throw new UnserializeBadClassException();
        }

        $this->setNotification( new $class() );
        return $data[$class_alias];
    }

    public function saveToFlashStore()
    {
        foreach( $this->recipients as $recipient ){
            $key = $this->parent->getStoreKeyForRecipient($recipient);
            $this->index[$recipient] = $this->parent->getFlashStorage()->add($key, $this->_data);
        }
    }

    public function restore()
    {
        $propertiesData = $this->prepareToRestore();
        $this->initializeProperties($propertiesData);
    }

    /*------------------------------------------------------------------------------------------- Private Methods --- */

    private function prepareToStoreProperties()
    {
        $data = array();
        foreach( $this->_config['properties'] as $name => $configs ){
            $getMethod = 'get'.ucfirst($name);
            if ( !method_exists($this->_notification, $getMethod) ){
                throw new MethodNotFoundException( printf('The method "%s" dos\'t exist in "%s"', $getMethod, get_class($this->_notification)) );
            }
            $value = $this->_notification->$getMethod();
            $shortName = $name;
            foreach( $configs as $config ){
                if ( $config instanceof ColumnTypeInterface ){
                    $value = $config->store($value);
                    $shortName = null == $config->alias ? $shortName : $config->alias;
                }
            }
            $data[$shortName] = $value;
        }
        return $data;
    }

    private function initializeProperties($data)
    {
        foreach( $this->_config['properties'] as $name => $configs ){
            $setMethod = 'set'.ucfirst($name);
            if ( !method_exists($this->_notification, $setMethod) ){
                throw new MethodNotFoundException( printf('The method "%s" dos\'t exist in "%s"', $setMethod, get_class($this->_notification)) );
            }

            $shortName = $name;
            foreach( $configs as $config ){
                if ( $config instanceof ColumnTypeInterface ){
                    $shortName = null == $config->alias ? $shortName : $config->alias;
                    $value = $data[$shortName];
                    $value = $config->restore($value, $this->parent->getContainer());
                }
            }

            $this->_notification->$setMethod($value);
        }
    }

}