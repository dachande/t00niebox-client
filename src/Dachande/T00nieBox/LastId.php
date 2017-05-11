<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Streamer\Stream;

class LastId
{
    public static function get()
    {
        $lastIdFile = Configure::read('App.lastIdFile');

        if (!file_exists($lastIdFile)) {
            return null;
        }

        $stream = new Stream(fopen($lastIdFile, 'r'));
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
