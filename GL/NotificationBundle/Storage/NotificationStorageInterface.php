<?php

namespace GL\NotificationBundle\Storage;

interface NotificationStorageInterface
{
    public function add($key, $value);

    public function get($key);

    public function count($key);
}