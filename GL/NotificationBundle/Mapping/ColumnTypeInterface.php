<?php

namespace GL\NotificationBundle\Mapping;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ColumnTypeInterface {
    public function store($value);
    public function restore($value, ContainerInterface $container = null);
}