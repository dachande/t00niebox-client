<?php
namespace Dachande\T00nieBox;

class Playlist
{
    /**
     * Holds the playlist array that came from the server
     *
     * @param array $list
     */
    protected $list = [];

    /**
     * File list that actually represents the playlist
     *
     * @param array $list
     */
    protected $files = [];

    public function __construct($list)
    {
        $this->set($list);
    }

    public function set($list)
    {
        if (array_key_exists('playlist', $list) && $list['playlist'] !== null) {
            $this->list = $list['playlist'];
        }
    }

    public function getFilesFromList()
    {
        return (array_key_exists('files_array', $this->list)) ? $this->list['files_array'] : [];
    }

    public function getFiles()
    {
        return $this->files;
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
