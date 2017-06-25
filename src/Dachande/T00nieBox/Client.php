<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Dachande\T00nieBox\Uuid;
use Cake\Utility\Security;

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

        $uuid = $this->uuid->get();

        // Check if pause-uuid is transmitted.
        if ($uuid === Configure::read('App.pauseUuid')) {
            $this->log('Client - Pause playback.', 'notice');

            Mpc::command(Mpc::CMD_PAUSE);
            return true;
        }

        // Compare with previous uuid.
        // If uuids match, resume playback and exit.
        if (LastId::compare($uuid)) {
            $this->log('Client - Resuming playback.', 'notice');

            Mpc::command(Mpc::CMD_PLAY);
            return true;
        }

        // Try to get card from server for the specified uuid.
        try {
            $card = CardFactory::createFromJson(Server::getCardByUuid($uuid));
        } catch (\InvalidArgumentException $e) {
            $this->log($e->getMessage(), 'warning');

            $card = new Card($uuid, 'Empty playlist', []);
        }

        // Check if card was successfully downloaded.
        if ($card->hasFiles()) {
            // Get playlist object by feeding it with the file list from downloaded card.
            $playlist = Playlist::create($card->getFiles(), $uuid);

            // Load playlist from remote and save playlist file
            if ($playlist->load(true) !== false) {
                // Synchronize audio files
                $playlist->sync();

                // Clear current playlist and add a new one
                $playlistFile = $playlist->getFilename();
                $this->log(sprintf('Client - Starting playback of playlist %s', $playlistFile), 'notice');

                Mpc::loadNewPlaylist(preg_replace('/^(.*)\.m3u$/', '\1', $playlistFile));

                // Write current uuid to lastId
                LastId::set($uuid);

                return true;
            } else {
                $this->log(sprintf('Client - Could not find playlist file for uuid %s.', $uuid), 'warning');

                return false;
            }
        } else {
            // It looks like the server could not be reached or there is no card
            // attached to the requested rfid uuid.
            // So we try to find a local copy of the playlist for that uuid instead.
            if (Playlist::exists($uuid)) {
                // Clear current playlist and add a new one
                $playlistFile = Playlist::getFilenameFromUuid($uuid);
                $this->log(sprintf('Client - Starting playback of playlist %s', $playlistFile), 'notice');

                Mpc::loadNewPlaylist(preg_replace('/^(.*)\.m3u$/', '\1', $playlistFile));

                // Write current uuid to lastId
                LastId::set($uuid);

                return true;
            } else {
                $this->log(sprintf('Client - Could not find playlist file for uuid %s.', $uuid), 'warning');

                return false;
            }
        }
    }
}
