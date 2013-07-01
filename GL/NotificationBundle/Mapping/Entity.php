<?php

namespace GL\NotificationBundle\Mapping;

use GL\NotificationBundle\Exception\MethodNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Annotation
 */
final class Entity extends AbstractColumnType
{
    /**
     * @var string
     */
    public $repository;

    /**
     * @var string
     */
    public $findMethod = "find";

    /**
     * @var mixed
     */
    public $serializeProperties = array("id");

    public function store($entity)
    {
        $serializeData = array();
        foreach( $this->serializeProperties as $property ){
            $getMethod = 'get'.ucfirst($property);
            if ( !method_exists($entity, $getMethod) ){
                throw new MethodNotFoundException( sprintf('Method "%s" does not exist in %s', $getMethod, get_class($entity)) );
            }
            $serializeData[$property] = $entity->$getMethod();
        }
        return $serializeData;
    }

    public function restore($data, ContainerInterface $container = null)
    {
        return $container->get('doctrine.orm.entity_manager')->getRepository($this->repository)->find($data);
    }
}