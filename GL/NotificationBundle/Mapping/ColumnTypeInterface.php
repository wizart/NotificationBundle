<?php

namespace GL\NotificationBundle\Mapping;

use GL\NotificationBundle\Notifications\NotificationInterface;

interface ColumnTypeInterface {
    public function getShortName();
    public function serialize();
    public function unserialize($data);
}