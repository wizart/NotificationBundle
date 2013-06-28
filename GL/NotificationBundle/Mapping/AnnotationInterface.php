<?php

namespace GL\NotificationBundle\Mapping;

use GL\NotificationBundle\Notifications\NotificationInterface;

interface AnnotationInterface {
    public function setNotification(NotificationInterface $notification);
    public function getNotificationPropertyValue($property = null);
    public function setPropertyName($name);
    public function getPropertyName();
}