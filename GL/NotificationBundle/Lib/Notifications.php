<?php

namespace GL\NotificationBundle\Lib;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use GL\NotificationBundle\Notifications\NotificationInterface;
use GL\NotificationBundle\Storage\NotificationStorageInterface;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Notifications
{
    /** @var \Doctrine\Common\Collections\ArrayCollection */
    private $_elements;
    /** @var ContainerInterface */
    private $container;
    /** @var Reader */
    private $annotationReader;

    /** @var NotificationStorageInterface */
    private $flashStorageAdapter;
    /** @var NotificationStorageInterface */
    private $permanentStorageAdapter;

    private $mappedConfig;

    public function __construct(ContainerInterface $container)
    {
        $this->_elements = new ArrayCollection();
        $this->container = $container;
        $this->annotationReader = $this->container->get('annotation_reader');
        $this->instanceFromConfig($this->container->getParameter('gl_notification_config'));
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function instanceFromConfig($config)
    {
        if ( !$this->container->has($config['flashStorage']) ){
            throw new ServiceNotFoundException($config['flashStorage']);
        }
        $this->flashStorageAdapter = $this->container->get($config['flashStorage']);

        if ( null == $config['permanentStorage'] ){
            $this->permanentStorageAdapter = null;
        } else {
            if ( !$this->container->has($config['permanentStorage']) ){
                throw new ServiceNotFoundException($config['permanentStorage']);
            }
            $this->permanentStorageAdapter = $this->container->get($config['permanentStorage']);
        }
        $this->mappedConfig = $config['mapped'];
    }

    /**
     * @param array|string $recipient
     * @param NotificationInterface $notification
     * @return mixed
     */
    public function add($recipient, NotificationInterface $notification)
    {
        $recipients = is_array($recipient) ? $recipient : array($recipient);

        $proxy = new ProxyNotification($this);
        $proxy->setRecipients($recipients);
        $proxy->setNotification($notification);
        $proxy->prepareToStore();
        $proxy->saveToFlashStore();

        $index = array();
        foreach( $recipients as $recipient ){
            if ( !$this->_elements->containsKey($recipient) ){
                $this->_elements->set($recipient, new ArrayCollection());
            }
            $index[$recipient] = $this->_elements->get($recipient)->add($proxy);
        }

        return is_array($recipient) ? $index : array_shift($index);
    }

    /**
     * @param string $recipient
     * @return ArrayCollection
     */
    public function get($recipient)
    {
        if ( !$this->_elements->containsKey($recipient) ){
            $this->restore($recipient);
        }
        $notifications = array_map(function($proxy){
                return $proxy->getNotification();
        }, $this->_elements->get($recipient)->toArray());

        return new ArrayCollection($notifications);
    }

    /**
     * @param string $recipient
     * @return int
     */
    public function getCount($recipient)
    {
        return $this->flashStorageAdapter->count( $this->getStoreKeyForRecipient($recipient) );
    }


    /* ------------------------------------------------------------------------------------------ Mapping methods --- */
    public function getMappedClassByAlias($alias)
    {
        return isset($this->mappedConfig['aliases'][$alias]) ? $this->mappedConfig['aliases'][$alias] : null;
    }
    public function getAliasByMappedClass($class)
    {
        return array_search($class, $this->mappedConfig['aliases']);
    }
    public function loadAnnotationConfig($notification)
    {
        $object = new \ReflectionObject($notification);
        $config = array(
            'class' => $this->annotationReader->getClassAnnotations($object),
            'properties' => array()
        );

        foreach( $object->getProperties() as $property ){
            $config['properties'][$property->getName()] = $this->annotationReader->getPropertyAnnotations($property);
        }
        return $config;
    }

    /* -------------------------------------------------------------------------------- Store and Restore methods --- */
    /**
     * @return NotificationStorageInterface
     */
    public function getFlashStorage()
    {
        return $this->flashStorageAdapter;
    }

    /**
     * @return NotificationStorageInterface
     */
    public function getPermanentStorage()
    {
        return $this->permanentStorageAdapter;
    }


    public function store(){
        $this->prepareToStore();
    }

    public function restore($recipient){
        $list = $this->flashStorageAdapter->get($this->getStoreKeyForRecipient($recipient));

        if ( !$this->_elements->containsKey($recipient) ){
            $this->_elements->set($recipient, new ArrayCollection());
        }
        $index = array();
        foreach( $list as $data ){
            $proxy = new ProxyNotification($this);
            $proxy->setRecipients(array($recipient));
            $proxy->setData($data);
            $proxy->restore();
            $index[$recipient][] = $this->_elements->get($recipient)->add($proxy);
        }
        return $index;
    }

    /* ------------------------------------------------------------------------------------------ Private methods --- */

    public function getStoreKeyForRecipient($recipient)
    {
        return 'u:'.$recipient.':notices';
    }

    private function prepareToStore(){}
}