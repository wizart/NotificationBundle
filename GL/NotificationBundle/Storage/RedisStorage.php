<?php

namespace GL\NotificationBundle\Storage;

use Snc\RedisBundle\Client\Predis\Client;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class RedisStorage implements NotificationStorageInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @var Client
     */
    private $redis;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $config = $this->container->getParameter('gl_notification_config');
        if ( !isset($config['storage']['redis']) ){
            throw new InvalidConfigurationException('For use Redis storage you must set redis configuration in notification config.');
        }
        if ( !isset($config['storage']['redis']['client']) ){
            throw new InvalidConfigurationException('You forgot set the redis client service id in notification config.');
        }

        $redis_service_id = $config['storage']['redis']['client'];

        if ( !$this->container->has($redis_service_id) ){
            throw new ServiceNotFoundException($redis_service_id);
        }
        $this->redis = $this->container->get($redis_service_id);
    }

    /**
     * @param string $key
     * @param int $value
     */
    public function add($key, $value)
    {
        $this->redis->lPush($key, $value);
    }

    /**
     * @param string $key
     * @return array mixed
     */
    public function get($key)
    {
        return $this->redis->lRange($key, 0, -1);
    }

    /**
     * @param string $key
     * @return int
     */
    public function count($key)
    {
        return $this->redis->lSize($key);
    }
}