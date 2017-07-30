<?php
namespace Dachande\T00nieBox\Playlist;

use Cake\Core\Configure;
use Cake\Utility\Security;
use Streamer\Stream;
use Dachande\T00nieBox\Rsync;

/**
 * Playlist handling.
 */
class Playlist
{
    use \Cake\Log\LogTrait;

    /**
     * Card object
     *
     * @var \Dachande\T00nieBox\Card\Card
     */
    protected $card;

    /**
     * List of audio files as a string to be stored in a playlist file.
     *
     * @var string
     */
    protected $playlist = '';

    /**
     * Filename for a temporary file used as a --files-from parameter for rsync
     *
     * @var string
     */
    protected $tempFilesFromFilename = '';

    /**
     * Initializes a playlist.
     *
     * @param \Dachande\T00nieBox\Card\Card $card
     */
    public function __construct(\Dachande\T00nieBox\Card\Card $card)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->card = $card;
        $this->tempFilesFromFilename = $this->generateFilesFromFilename();
        $this->storeFiles();
    }

    /**
     * Removes the temporary file if it exists
     */
    public function __destruct()
    {
        if (file_exists($this->tempFilesFromFilename)) {
            unlink($this->tempFilesFromFilename);
        }
    }

    /**
     * Generate a unique filename for the temporary files-from file used by rsync.
     *
     * @return string
     */
    protected function generateFilesFromFilename()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return ROOT . DS . Security::hash($this->filesToString(), 'md5') . '.txt';
    }

    /**
     * Returns a string containing all files seperated by newline.
     *
     * @return string
     */
    protected function filesToString()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return implode("\n", $this->card->getFiles());
    }

    /**
     * Stores files in temporary file.
     *
     * @return void
     */
    protected function storeFiles()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log(sprintf('Playlist - Writing file list to temporary file. (%s)', basename($this->tempFilesFromFilename)), 'info');
        $stream = new Stream(fopen($this->tempFilesFromFilename, 'w'));

        try {
            $stream->write($this->filesToString());
        } catch (\Exception $e) {
            $this->log(sprintf('Playlist - Error while writing to file %s. (%s)', basename($this->tempFilesFromFilename), $e->getMessage()), 'error');
        }

        $stream->close();
    }

    /**
     * Returns the playlist filename.
     *
     * @param bool $fullPath
     * @return string
     */
    public function getFilename($fullPath = false)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return static::getFilenameFromUuid($this->card->getUuid(), $fullPath);
    }

    /**
     * Get playlist filename.
     *
     * @param \Dachande\T00nieBox\Uuid $uuid
     * @param bool $fullPath
     * @return string
     */
    public static function getFilenameFromUuid(\Dachande\T00nieBox\Uuid $uuid, $fullPath = false)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $filename = Configure::read('Mpd.playlists') . DS . $uuid->get() . '.m3u';

        return ($fullPath === true) ? $filename : basename($filename);
    }

    /**
     * Check if playlist file exists.
     *
     * @param  \Dachande\T00nieBox\Uuid $uuid
     * @return bool
     */
    public static function exists(\Dachande\T00nieBox\Uuid $uuid)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        return file_exists(static::getFilenameFromUuid($uuid, true));
    }

    /**
     * Load playlist from remote using rsync
     *
     * By setting $save to true the method will call the save() method
     * for you to store the retrieved playlist in the playlist file.
     *
     * @param  bool $save
     * @return string|bool
     */
    public function load($save = false)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log('Playlist - Downloading playlist...', 'notice');

        Rsync::initialize(false, $this->card->getShare(), $this->tempFilesFromFilename);
        $rsyncOutput = Rsync::execute();
        // TODO: Validate Rsync output
        $this->playlist = $this->generatePlaylistFromRsyncOutput($rsyncOutput);

        if ($save === true) {
            return $this->save();
        }

        return $this->playlist;
    }

    /**
     * Save playlist to file.
     *
     * @return string|bool
     */
    public function save()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (!empty($this->playlist)) {
            $playlistFile = $this->getFilename(true);
            $stream = new Stream(fopen($playlistFile, 'w'));

            try {
                $stream->write($this->playlist);
            } catch (\Exception $e) {
                $this->log(sprintf('Playlist - Error while writing to file %s. (%s)', basename($playlistFile), $e->getMessage()), 'error');
            }

            $stream->close();

            return $this->playlist;
        }

        return false;
    }

    /**
     * Download/synchronize audio files from remote.
     *
     * @return void
     */
    public function sync()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log('Playlist - Synchronizing files...', 'notice');

        Rsync::initialize(true, $this->card->getShare(), $this->tempFilesFromFilename);
        Rsync::execute(false);
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
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $input = explode("\n", $rsyncOutput);
        $output = [];

        foreach ($input as $line) {
            if (preg_match(Configure::read('App.audioFileRegexp'), $line)) {
                $splittedLine = preg_split('/\s+/', $line, 5);
                $output[] = $splittedLine[4];
            }
        }

        return implode("\n", $output) . "\n";
    }
}
