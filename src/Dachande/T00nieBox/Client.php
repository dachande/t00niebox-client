<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Cake\Utility\Security;
use Dachande\T00nieBox\Uuid;
use Dachande\T00nieBox\Card\CardFactory;
use Dachande\T00nieBox\Playlist\Playlist;

/**
 * The t00niebox client
 */
class Client
{
    use \Cake\Log\LogTrait;

    /**
     * @var \Dachande\T00nieBox\Uuid
     */
    protected $uuid = '';

    /**
     * Initialize the t00niebox client.
     */
    public function __construct($uuid)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->uuid = new Uuid($uuid);
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

        // Check if pause-uuid is transmitted.
        if ($this->uuid->get() === Configure::read('App.pauseUuid')) {
            $this->log('Client - Pause playback.', 'notice');

            // Send pause command to MPD
            Mpc::command(Mpc::CMD_PAUSE);
            return true;
        }

        // Compare with previous uuid.
        // If uuids match, resume playback and exit.
        if (LastUuid::compare($this->uuid)) {
            $this->log('Client - Resuming playback.', 'notice');

            // Send play command to MPD
            Mpc::command(Mpc::CMD_PLAY);
            return true;
        }

        // Try to get card from server for the specified uuid.
        try {
            $card = Server::getCardByUuid($this->uuid);
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'warning');

            $card = CardFactory::createEmpty($this->uuid);
        }

        // Check if card was successfully downloaded.
        if ($card->hasFiles()) {
            // Get playlist object by feeding it with the downloaded card.
            $playlist = new Playlist($card);
            // Load playlist from remote and save playlist file
            if ($playlist->load(true) !== false) {
                // Synchronize audio files
                $playlist->sync();

                // Clear current playlist and add a new one
                $playlistFile = $playlist->getFilename();
                $this->log(sprintf('Client - Starting playback of playlist %s', $playlistFile), 'notice');

                Mpc::loadNewPlaylist(preg_replace('/^(.*)\.m3u$/', '\1', $playlistFile));

                // Write current uuid to lastId
                LastUuid::set($this->uuid);

                return true;
            } else {
                $this->log(sprintf('Client - Could not find playlist file for uuid %s.', $uuid), 'warning');

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
                LastUuid::set($this->uuid);

                return true;
            } else {
                $this->log(sprintf('Client - Could not find playlist file for uuid %s.', $this->uuid->get()), 'warning');

                return false;
            }
        }
    }
}
