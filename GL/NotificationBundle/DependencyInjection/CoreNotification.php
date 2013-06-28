<?php

namespace GL\NotificationBundle\DependencyInjection;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationException;
use GL\NotificationBundle\Notifications\NotificationsHandler;
use GL\NotificationBundle\Notifications\NotificationHandler;
use GL\NotificationBundle\Notifications\NotificationInterface;
use Snc\RedisBundle\Client\Phpredis\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class CoreNotification
{
    /**
     * @var Reader
     */
    protected $annotationReader;
    /**
     * @var UserInterface
     */
    protected $user;
    /**
     * @var Client
     */
    protected $redis;

    protected $handlers;

    public function __construct(Reader $annotationReader, Client $redis, SecurityContextInterface $security = null)
    {
        $this->annotationReader = $annotationReader;
        $this->redis = $redis;

        if (
            null != $security &&
            null != $security->getToken() &&
            is_object($user = $security->getToken()->getUser())
        ) {
            $this->user = $user;
        } else {
            $this->user = null;
        }
    }

    private function getNotificationListName($recipient)
    {
        return 'user:'. $recipient .':notifications';
    }

    public function add(NotificationInterface $notification)
    {
        $handler = new NotificationHandler($notification, $this->annotationReader);
        $hash = $handler->serializeNotification();
        $recipient = $handler->getRecipient();
        $index = $this->redis->lPush($this->getNotificationListName($recipient), $hash);
        if ( $index > 0 ){
            $handler->setId($index);
            $this->handlers[$index] = $handler;
        }
        return $index;
    }

    public function getNewNotificationForUser(User $user = null)
    {
        if ( null == $user ){
            $user = $this->user;
        }
        $list = $this->redis->lRange($this->getNotificationListName($this->user->getId()), 0, -1);

        $handler = new NotificationsHandler($list, $user->getId(), $this->annotationReader);

        var_dump($handler->getNotifications());
        die();
        return array();
    }
    public function getCountNewNotificationForUser(User $user = null)
    {
        if ( null == $user ){
            $user = $this->user;
        }
        return $this->redis->lSize( $this->getNotificationListName($this->user->getId()) );
    }

}