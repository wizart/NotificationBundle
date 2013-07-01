<?php

namespace GL\NotificationBundle\Mapping;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Annotation
 */
final class String extends AbstractColumnType
{
    /**
     * @param string $value
     * @return mixed
     */
    public function store($value)
    {
        return $value;
    }

    public function restore($value, ContainerInterface $container = null)
    {
        return $value;
    }
}