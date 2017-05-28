<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Dachande\T00nieBox\Exception\InvalidUuidException;
use Cake\Utility\Security;

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

        // Check if pause-uuid is transmitted.
        if ($this->uuid === Configure::read('App.pauseUuid')) {
            $this->log('Client - Pause playback.', 'notice');

            Mpc::command(Mpc::CMD_PAUSE);
            return true;
        }

        // Compare with previous uuid.
        // If uuids match, resume playback and exit.
        if (LastId::compare($this->uuid)) {
            $this->log('Client - Resuming playback.', 'notice');

            Mpc::command(Mpc::CMD_PLAY);
            return true;
        }

        // Try to get card from server for the specified uuid.
        try {
            $card = Card::create(Server::getCardByUuid($this->uuid));
        } catch (\InvalidArgumentException $e) {
            $this->log($e->getMessage(), 'warning');

            $card = new Card($this->uuid, 'Empty playlist');
        }

        // Check if card was successfully downloaded.
        if ($card->hasFiles()) {
            // Get playlist object by feeding it with the file list from downloaded card.
            $playlist = Playlist::create($card->getFiles(), $this->uuid);

            // Load playlist from remote and save playlist file
            if ($playlist->load(true) !== false) {
                // Synchronize audio files
                $playlist->sync();

                // Clear current playlist and add a new one
                $playlistFile = $playlist->getFilename();
                $this->log(sprintf('Client - Starting playback of playlist %s', $playlistFile), 'notice');

                Mpc::loadNewPlaylist(preg_replace('/^(.*)\.m3u$/', '\1', $playlistFile));

                // Write current uuid to lastId
                LastId::set($this->uuid);

                return true;
            } else {
                $this->log(sprintf('Client - Could not find playlist file for uuid %s.', $this->uuid), 'warning');

                return false;
            }
        } else {
            // It looks like the server could not be reached or there is no card
            // attached to the requested rfid uuid.
            // So we try to find a local copy of the playlist for that uuid instead.
            if (Playlist::exists($this->uuid)) {
                // Clear current playlist and add a new one
                $playlistFile = Playlist::getFilenameFromUuid($this->uuid);
                $this->log(sprintf('Client - Starting playback of playlist %s', $playlistFile), 'notice');

                Mpc::loadNewPlaylist(preg_replace('/^(.*)\.m3u$/', '\1', $playlistFile));

                // Write current uuid to lastId
                LastId::set($this->uuid);

                return true;
            } else {
                $this->log(sprintf('Client - Could not find playlist file for uuid %s.', $this->uuid), 'warning');

                return false;
            }
        }
    }
}
