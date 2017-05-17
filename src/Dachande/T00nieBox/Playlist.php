<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Cake\Utility\Security;
use Streamer\Stream;

/**
 * Playlist handling.
 */
class Playlist
{
    use \Cake\Log\LogTrait;

    /**
     * UUid needed for playlist file generation.
     *
     * @var $string
     */
    protected $uuid = '';

    /**
     * Holds an array of files/folders stored in a card
     *
     * @var array
     */
    protected $files = [];

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
     * @param array $files
     */
    public function __construct(array $files, $uuid)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->setFiles($files);
        $this->uuid = $uuid;
    }

    /**
     * Removes the temporary file if it exists
     */
    public function __destruct()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (file_exists($this->tempFilesFromFilename)) {
            $this->log(sprintf('Playlist - Removing temporary file. (%s)', basename($this->tempFilesFromFilename)), 'info');
            unlink($this->tempFilesFromFilename);
        }
    }

    /**
     * Create an instance of a playlist and initialize.
     *
     * @param  array $files
     * @return \Dachande\T00nieBox\Playlist
     */
    public static function create(array $files, $uuid)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        return new static($files, $uuid);
    }

    /**
     * Set a file list from a card and initializes its corresponding temporary file for rsync.
     *
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->files = $files;
        $this->generateFilesFromFilename();
        $this->storeFiles();
    }

    /**
     * Generate a unique filename for the temporary files-from file used by rsync.
     *
     * @return string
     */
    protected function generateFilesFromFilename()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->tempFilesFromFilename = ROOT . DS . Security::hash($this->filesToString(), 'md5') . '.txt';
    }

    /**
     * Returns a string containing all files seperated by newline.
     *
     * @return string
     */
    protected function filesToString()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return implode("\n", $this->files);
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
        $stream->write($this->filesToString());
        $stream->close();
    }

    public function getPlaylistFilename()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return static::createFilenameFromUuid($this->uuid);
    }

    /**
     * Get playlist filename.
     *
     * @param boolean $fullPath
     * @return string
     */
    public static function createFilenameFromUuid($uuid, $fullPath = false)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $filename = Configure::read('Mpd.playlists') . DS . $uuid . '.m3u';

        return ($fullPath === true) ? $filename : basename($filename);
    }

    public function state()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return file_exists(static::createFilenameFromUuid($this->uuid, true));
    }

    public static function exists($uuid = '')
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        return file_exists(static::createFilenameFromUuid($uuid, true));
    }

    public function load($save = false)
    {
        Rsync::initialize(false, $this->tempFilesFromFilename);
        $rsyncOutput = Rsync::execute();
        $this->playlist = $this->generateFromRsyncOutput($rsyncOutput);

        if ($save === true) {
            return $this->save();
        }

        return $this->playlist;
    }

    /**
     * Save playlist to file.
     *
     * @return void
     */
    public function save()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (!empty($this->playlist)) {
            $stream = new Stream(fopen(static::createFilenameFromUuid($this->uuid, true), 'w'));
            $stream->write($this->playlist);
            $stream->close();

            return $this->playlist;
        }

        return false;
    }

    public function sync()
    {
        Rsync::initialize(true, $this->tempFilesFromFilename);
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
    protected function generateFromRsyncOutput($rsyncOutput)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

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
