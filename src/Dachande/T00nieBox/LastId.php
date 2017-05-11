<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Streamer\Stream;

class LastId
{
    public static function get()
    {
        $stream = new Stream(fopen(Configure::read('App.lastIdFile'), 'r'));
        $lastId = $stream->getContent();
        $stream->close();

        return $lastId;
    }

    public static function set($lastId)
    {
        $stream = new Stream(fopen(Configure::read('App.lastIdFile'), 'w'));
        $stream->write($lastId);
        $stream->close();
    }

    public static function compare($id)
    {
        $lastId = self::get();

        return ($id === $lastId) ? true : false;
    }

}
