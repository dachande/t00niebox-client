<?php
namespace Dachande\T00nieBox;

/**
 * Playlist handling.
 */
class Playlist
{
    use \Cake\Log\LogTrait;

    /**
     * Holds the playlist array that came from the server
     *
     * @param array $list
     */
    protected $list = [];

    /**
     * Initializes a playlist.
     *
     * @param mixed $list
     */
    public function __construct($list)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (is_array($list)) {
            $this->log('Playlist - Generating playlist object.', 'info');
            $this->setList($list);
        } else {
            $this->log('Playlist - Generating empty playlist object.', 'info');
        }
    }

    /**
     * Set a new playlist list that has been retrieved from a server earlier.
     *
     * @param array $list
     */
    public function setList(array $list)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (array_key_exists('playlist', $list)) {
            if ($list['playlist'] !== null) {
                $this->log('Playlist - Playlist populated with new list.', 'info');
                $this->list = $list['playlist'];
            } else {
                $this->log('Playlist - Playlist populated with empty list', 'info');
                $this->list = [];
            }
        } else {
            $this->log('Playlist - Playlist populated with new list but list format might be invalid.', 'warning');
            $this->list = $list;
        }
    }

    /**
     * Query t00niebox server to retrieve a playlist and returns
     * a new playlist object.
     *
     * @param  string $uuid
     * @return \Dachande\T00nieBox\Playlist
     */
    public static function initializeFromServerWithUuid($uuid)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        $result = Server::getPlaylistByUuid($uuid);

        return new static($result);
    }

    /**
     * Get all files from a playlist list.
     *
     * @return array
     */
    public function getFiles()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        if (array_key_exists('files_array', $this->list)) {
            return $this->list['files_array'];
        } elseif (array_key_exists('files', $this->list)) {
            return unserialize($this->list['files']);
        }

        return [];
    }

    public function save()
    {

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
    public static function generateFromRsyncOutput($rsyncOutput)
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
