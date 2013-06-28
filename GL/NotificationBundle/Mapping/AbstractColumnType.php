<?php

namespace GL\NotificationBundle\Mapping;

abstract class AbstractColumnType extends AbstractAnnotation implements ColumnTypeInterface
{
    /** @var boolean */
    public $serializable = true;
    /** @var string */
    public $shortName = null;

    protected $tempData;

    public function getShortName()
    {
        return null == $this->shortName ? $this->propertyName : $this->shortName;
    }

    public function unserialize($data)
    {
        $this->tempData = $data;
    }

}