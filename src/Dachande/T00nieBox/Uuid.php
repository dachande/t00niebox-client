<?php

namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Dachande\T00nieBox\Exception\InvalidUuidException;

class Uuid
{

    protected $uuid = null;

    public function __construct($uuid)
    {
        $this->set($uuid);
    }

    public function get()
    {
        return $this->uuid;
    }

    public function set($uuid)
    {
        if (preg_match(Configure::read('App.uuidRegexp'), $uuid) === 1) {
            $this->uuid = $uuid;
        } else {
            throw new InvalidUuidException();
        }
    }
}
