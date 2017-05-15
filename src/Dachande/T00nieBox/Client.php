<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Dachande\T00nieBox\Exception\InvalidUuidException;

/**
 * The t00niebox client
 */
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
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->setUuid($uuid);
    }

    /**
     * Set the rfid card/transponder uuid.
     *
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log(sprintf('Client - Setting uuid to %s.', $uuid), 'info');
        $this->uuid = $uuid;
    }

    /**
     * Validate uuid
     *
     * @return boolean
     * @throws \Dachande\T00nieBox\Exception\InvalidUuidException
     */
    protected function validateUuid()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (!preg_match(Configure::read('App.uuidRegexp'), $this->uuid)) {
            throw new InvalidUuidException('The supplied uuid is invalid');
        }

        return true;
    }

    /**
     * Main client
     *
     * @return void
     */
    public function run()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log('Client - Running the client.', 'notice');

        // Validate uuid
        $this->validateUuid();

        // Compare with previous uuid.
        // If uuids match, resume playback and exit.
        if (LastId::compare($this->uuid)) {
            // TODO: MPD resume
            return true;
        }

        // Try to get card from server for the specified uuid.
        try {
            $card = Card::create(Server::getCardByUuid($this->uuid));
        } catch (\InvalidArgumentException $e) {
            $this->log($e->getMessage(), 'warning');

            $card = new Card($this->uuid, 'Empty playlist');
        }

        if (sizeof($card->getFiles())) {
            // Card was successfully downloaded.

            // TODO
            // - Generate files list to be stored in a local playlist file playable by MPD
            // - Use rsync to download the files from the server
            // - Start playing the playlist through MPD

            // Write current uuid to lastId
            LastId::set($this->uuid);
        } else {
            // It looks like the server could not be reached or there is no card
            // attached to the requested rfid uuid.
            // So we try to find a local copy of the playlist for that uuid instead.

            // TODO
            // - Check for local playlist file for that uuid
            // - Start playing the playlist through MPD if found and set lastId.
        }
    }
}
