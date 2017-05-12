<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Streamer\Stream;

class LastId
{
    use \Cake\Log\LogTrait;

    public static function get()
    {
        $lastIdFile = Configure::read('App.lastIdFile');

        static::log(sprintf('Trying to read lastId from file %s.', $lastIdFile), 'debug');

        if (!file_exists($lastIdFile)) {
            static::log(sprintf('File %s does not exist.', $lastIdFile), 'debug');

            return null;
        }

        $stream = new Stream(fopen($lastIdFile, 'r'));
        $lastId = $stream->getContent();

        static::log(sprintf('LastId is %s.', $lastId), 'debug');

        $stream->close();

        return $lastId;
    }

    public static function set($lastId)
    {
        $lastIdFile = Configure::read('App.lastIdFile');

        static::log(sprintf('Writing lastId %s to file %s.', $lastId, $lastIdFile), 'debug');

        $stream = new Stream(fopen($lastIdFile, 'w'));
        $stream->write($lastId);
        $stream->close();
    }

    public static function compare($id)
    {
        $lastId = static::get();

        static::log(sprintf('Comparing uuid %s with lastId %s.', $id, $lastId), 'debug');

        if ($id === $lastId) {
            static::log('MATCH!', 'debug');
            return true;
        } else {
            static::log('MISMATCH!', 'debug');
            return false;
        }
    }
}
