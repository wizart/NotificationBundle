<?php

namespace GL\NotificationBundle\Mapping;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Annotation
 */
final class DateTime extends AbstractColumnType
{
    /**
     * @param \DateTime $date
     * @return mixed
     */
    public function store($date)
    {
        $timestamp = $date->format('U');
        $timestamp = base_convert($timestamp, 10, 36);
        return $timestamp;
    }

    public function restore($timestamp, ContainerInterface $container = null)
    {
        $timestamp = base_convert($timestamp, 36, 10);
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }
}