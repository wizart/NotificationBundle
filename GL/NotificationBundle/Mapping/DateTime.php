<?php

namespace GL\NotificationBundle\Mapping;

/**
 * @Annotation
 */
final class DateTime extends AbstractColumnType
{
    public function serialize()
    {
        return $this->getNotificationPropertyValue()->format('U');
    }

    public function unserialize($data)
    {
        $date = new \DateTime();
        $date->setTimestamp($data);
        $this->setNotificationPropertyValue($date);
    }
}