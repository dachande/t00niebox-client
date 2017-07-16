<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Streamer\Stream;

class LastUuid
{
    use \Cake\Log\LogTrait;

    public static function get(): \Dachande\T00nieBox\Uuid
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $lastUuidFile = Configure::read('App.lastUuidFile');

        static::log(sprintf('LastUuid - Trying to read lastUuid from file %s.', $lastUuidFile), 'info');

        if (!file_exists($lastUuidFile)) {
            static::log(sprintf('LastUuid - File %s does not exist.', $lastUuidFile), 'info');

            return new Uuid(Configure::read('App.invalidUuid'));
        }

        $stream = new Stream(fopen($lastUuidFile, 'r'));
        $lastUuid = $stream->getContent();

        static::log(sprintf('LastUuid - LastUuid is %s.', $lastUuid), 'info');

        $stream->close();

        return new Uuid($lastUuid);
    }

    public static function set(\Dachande\T00nieBox\Uuid $uuid)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $lastUuidFile = Configure::read('App.lastUuidFile');

        static::log(sprintf('LastUuid - Writing lastUuid %s to file %s.', $uuid->get(), $lastUuidFile), 'info');

        $stream = new Stream(fopen($lastUuidFile, 'w'));

        try {
            $stream->write($uuid->get());
        } catch (\Exception $e) {
            static::log(sprintf('LastUuid - Error while writing to file %s. (%s)', $lastUuidFile, $e->getMessage()), 'error');
        }

        $stream->close();
    }

    public static function compare(\Dachande\T00nieBox\Uuid $uuid)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $lastUuid = static::get();

        static::log(sprintf('LastUuid - Comparing uuid %s with lastUuid %s.', $uuid->get(), $lastUuid->get()), 'info');

        if ($uuid->get() === $lastUuid->get()) {
            static::log('LastUuid - MATCH!', 'info');
            return true;
        } else {
            static::log('LastUuid - MISMATCH!', 'info');
            return false;
        }
    }
}
