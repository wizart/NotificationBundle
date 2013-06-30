<?php

namespace GL\NotificationBundle\Mapping;

abstract class AbstractColumnType implements ColumnTypeInterface, AnnotationInterface
{
    /** @var boolean */
    public $serializable = true;
    /** @var string */
    public $alias = null;

    protected $tempData;
}