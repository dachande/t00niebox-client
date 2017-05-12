<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Dachande\T00nieBox\Exception\InvalidUuidException;

class Client
{
    use \Cake\Log\LogTrait;

    /**
     * @var string
     */
    protected $uuid = '';

    /**
     * Initialize the t00niebox client.
     */
    public function __construct($uuid)
    {
        $this->setUuid($uuid);
    }

    /**
     * Set the rfid card/transponder uuid.
     *
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->log(sprintf('Setting uuid to %s.', $uuid), 'debug');
        $this->uuid = $uuid;
    }

    /**
     * Main client
     *
     * @return void
     */
    public function run()
    {
        $this->log('Client started.', 'debug');
        if (!preg_match(Configure::read('App.uuidRegexp'), $this->uuid)) {
            throw new InvalidUuidException('The supplied uuid is invalid');
        }

        if (LastId::compare($this->uuid)) {
            // TODO: MPD resume
            return true;
        }

        debug(Server::getAllPlaylists());
        debug(Server::getPlaylistByUuid($this->uuid));

        LastId::set($this->uuid);
    }
}
