<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Dachande\T00nieBox\Exception\InvalidUuidException;

class Client
{
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
        if (!preg_match(Configure::read('App.uuidRegexp'), $uuid)) {
            throw new InvalidUuidException();
        }

        $this->uuid = $uuid;
    }

    /**
     * Main client
     *
     * @return void
     */
    public function run()
    {
        if (LastId::compare($this->uuid)) {
            // TODO: MPD resume
            return true;
        }

        debug(Server::getAllPlaylists());
        debug(Server::getPlaylistByUuid($this->uuid));

        LastId::set($this->uuid);

        // Playlist generation
        // $rsyncCommand = $this->initializeRsync(false);
        // $output = $this->executeRsync($rsyncCommand);
        // print $this->generatePlaylistFromRsyncOutput($output) . "\n";

        // File synchronization
        // $rsyncCommand = $this->initializeRsync();
        // $rsyncCommand->execute(true);

        // Server query
        // print $this->getPlaylists()->getBody();
    }

    /**
     * Generate a playlist from rsync output
     *
     * t00niebox client relies on playlists containing all files transmitted over rsync.
     * Therefore a file list from rsync is the perfect source for a playlist file.
     *
     * @param  string $rsyncOutput
     * @return string
     */
    protected function generatePlaylistFromRsyncOutput($rsyncOutput)
    {
        $input = explode("\n", $rsyncOutput);
        $output = [];

        foreach ($input as $line) {
            if (preg_match('/\.aac|\.aax|\.ape|\.flac|\.m4a|\.mp3|\.ogg|\.wav|\.wma$/', $line)) {
                $splittedLine = preg_split('/\s+/', $line, 5);
                $output[] = $splittedLine[4];
            }
        }

        return implode("\n", $output);
    }
}
