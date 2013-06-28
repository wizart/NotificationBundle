<?php

namespace GL\NotificationBundle\Notifications;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Collections\ArrayCollection;
use GL\NotificationBundle\Mapping\AbstractColumnType;
use GL\NotificationBundle\Mapping\Recipient;

class PropertyHandler
{
    /**
     * @var Recipient
     */
    private $recipientAnnotation;

    /**
     * @var AbstractColumnType
     */
    private $columnAnnotation;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $otherAnnotations;


    public function __construct()
    {
        $this->otherAnnotations = new ArrayCollection();
    }

    public function setRecipientAnnotation($recipientAnnotation)
    {
        if ( null != $this->recipientAnnotation ){
            throw new AnnotationException( sprintf('%s property has two or more ColumnTypes in %s', $recipientAnnotation->getPropertyName(), '') );
        }
        $this->recipientAnnotation = $recipientAnnotation;
    }
    public function getRecipientAnnotation()
    {
        return $this->recipientAnnotation;
    }
    public function setColumnAnnotation($columnAnnotation)
    {
        if ( null != $this->columnAnnotation ){
            throw new AnnotationException( sprintf('%s property has two or more ColumnTypes in %s', $columnAnnotation->getPropertyName(), '') );
        }
        $this->columnAnnotation = $columnAnnotation;
    }
    public function getColumnAnnotation()
    {
        return $this->columnAnnotation;
    }
    public function addOtherAnnotation($annotation)
    {
        $this->otherAnnotations->add($annotation);
    }

    public function hasColumnAnnotation()
    {
        return $this->columnAnnotation !== null;
    }
    public function hasRecipientAnnotation()
    {
        return $this->recipientAnnotation !== null;
    }


}