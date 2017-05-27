<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Streamer\Stream;

class LastId
{
    use \Cake\Log\LogTrait;

    public static function get()
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $lastIdFile = Configure::read('App.lastIdFile');

        static::log(sprintf('LastId - Trying to read lastId from file %s.', $lastIdFile), 'info');

        if (!file_exists($lastIdFile)) {
            static::log(sprintf('LastId - File %s does not exist.', $lastIdFile), 'info');

            return null;
        }

        $stream = new Stream(fopen($lastIdFile, 'r'));
        $lastId = $stream->getContent();

        static::log(sprintf('LastId - LastId is %s.', $lastId), 'info');

        $stream->close();

        return $lastId;
    }

    public static function set($lastId)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $lastIdFile = Configure::read('App.lastIdFile');

        static::log(sprintf('LastId - Writing lastId %s to file %s.', $lastId, $lastIdFile), 'info');

        $stream = new Stream(fopen($lastIdFile, 'w'));

        try {
            $stream->write($lastId);
        } catch (\Exception $e) {
            static::log(sprintf('LastId - Error while writing to file %s. (%s)', $lastIdFile, $e->getMessage()), 'error');
        }

        $stream->close();
    }

    public static function compare($id)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $lastId = static::get();

        static::log(sprintf('LastId - Comparing uuid %s with lastId %s.', $id, $lastId), 'info');

        if ($id === $lastId) {
            static::log('LastId - MATCH!', 'info');
            return true;
        } else {
            static::log('LastId - MISMATCH!', 'info');
            return false;
        }
    }
}
