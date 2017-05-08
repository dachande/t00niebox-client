<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use Streamer\Stream;

class Client
{
    /**
     * @var string
     */
    protected $uuid = '';

    /**
     * @var boolean
     */
    protected $serverReachable = false;

    /**
     * Initialize the t00niebox client.
     */
    public function __construct()
    {
        // // Load configuration
        // $this->configuration = \Sinergi\Config\Collection::factory([
        //     'path' => CONFIG,
        // ]);
    }

    /**
     * Set the rfid card/transponder uuid.
     *
     * This method should be run before running the main run() method of the
     * t00niebox client.
     *
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Main client
     *
     * @return void
     */
    public function run()
    {
        // Check for valid uuid
        debug(Configure::read('App'));
        exit;
        if ($this->uuid === null || !strlen($this->uuid) || !preg_match(Configure::read('App.uuidRegexp'), $this->uuid)) {
            throw new \Dachande\T00nieBox\Exception\InvalidUuidException();
        }

        // Check if uuid matches lastId
        if ($this->uuid === $this->readLastId()) {
            // TODO: MPD resume
            return true;
        }

        // Get server status
        $this->serverIsReachable();

        print var_export($this->getPlaylist(), 1);
        // Testing lastId handling
        // $this->writeLastId($this->uuid);
        // print $this->readLastId();


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
     * This method checks if the t00niebox server is reachable.
     *
     * Checking that the server is available is essential for a working
     * t00niebox environment because the client acts differently depending on
     * the availability of the server.
     *
     * @return boolean
     */
    protected function serverIsReachable()
    {
        $ping = new \JJG\Ping(Configure::read('Server.host'));
        $ping->setPort(Configure::read('Server.port'));
        $ping->setTimeout(1);

        $latency = $ping->ping('fsockopen');

        $this->serverReachable = ($latency !== false) ? true : false;
        return $this->serverReachable;
    }

    /**
     * Send a http request to the server
     *
     * This method sends an http request to the server and returns the result
     *
     * @param  string  $endpoint
     * @param  string  $method
     * @param  boolean $forceServerCheck
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendServerRequest($endpoint, $method = 'GET', $forceServerCheck = false)
    {
        if ($forceServerCheck) {
            $this->serverIsReachable();
        }

        if ($this->serverReachable === true) {
            $client = new \GuzzleHttp\Client();
            $result = $client->request($method, $this->getServerURI() . $endpoint);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get all playlists managed by the server
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getPlaylists()
    {
        $result = $this->sendServerRequest('/playlists');

        if ($result !== false) {
            $result = json_decode($result->getBody());
        }

        return $result;
    }

    /**
     * Get playlist by uuid
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getPlaylist($filesOnly = true)
    {
        $result = $this->sendServerRequest('/playlists' . '/' . $this->uuid);

        if ($result !== false) {
            $result = json_decode($result->getBody(), true);

            if ($filesOnly) {
                $result = $result['playlist']['files_array'];
            }
        }

        return $result;
    }

    /**
     * Initialize Rsync
     *
     * This method does a full rsync command initialization. If you set $withTarget to false
     * the target will be omitted from the rsync command which can be use for just listing
     * remote files instead of synchronizing them.
     *
     * This will not execute the rsync command. To execute the command use the execute() method on
     * the returned object.
     *
     * @param  boolean $withTarget
     * @return \AFM\Rsync\Command
     */
    protected function initializeRsync($withTarget = true)
    {
        // Set Rsync source
        $source = Configure::read('Rsync.source');
        $sourceUsername = Configure::read('Rsync.sourceUsername');
        if ($sourceUsername !== null) {
            $source = $sourceUsername . '@' . $source;
        }

        // Set Rsync target
        if ($withTarget === true) {
            $target = Configure::read('Rsync.target');
            $targetUsername = Configure::read('Rsync.targetUsername');
            if ($targetUsername !== null) {
                $target = $targetUsername . '@' . $target;
            }
        }

        // Initialize rsync
        $rsync = new \AFM\Rsync\Rsync();
        if ($withTarget === true) {
            $rsyncCommand = $rsync->getCommand($source, $target);
        } else {
            $rsyncCommand = $rsync->getCommand($source, '');
        }

        // Add options
        $options = Configure::read('Rsync.config.options');
        if ($options !== null && is_array($options)) {
            foreach ($options as $option) {
                $rsyncCommand->addOption($option);
            }
        }

        // Add arguments
        $arguments = Configure::read('Rsync.config.arguments');
        if ($arguments !== null && is_array($arguments)) {
            foreach ($arguments as $argument => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $rsyncCommand->addArgument($argument, $subValue);
                    }
                } else {
                    $rsyncCommand->addArgument($argument, $value);
                }
            }
        }

        return $rsyncCommand;
    }

    /**
     * Execute the rsync command and return its output
     *
     * As the rsync command objects execute() method just prints out the result of the
     * rsync command, this method is used to address this issue. Instead of just printing
     * out the result, it is stored in a variable and returned in the end.
     *
     * @param  \AFM\Rsync\Command $rsyncCommand
     * @return string
     */
    protected function executeRsync(\AFM\Rsync\Command $rsyncCommand)
    {
        $output = '';
        $command = $rsyncCommand->getCommand();

        if (($fp = popen($command, "r"))) {
            while (!feof($fp)) {
                $output .= fread($fp, 1024);
            }
            fclose($fp);
        } else {
            throw new \InvalidArgumentException("Cannot execute command: '" .$command. "'");
        }

        return $output;
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

    /**
     * Get protocol://host:port combination from values stored in configuration
     *
     * @return string
     */
    protected function getServerURI()
    {
        $protocol = Configure::read('Server.protocol');
        $hostname = Configure::read('Server.host');
        $port = Configure::read('Server.port');

        return $protocol . '://' . $hostname . ':' . $port;
    }

    protected function readLastId()
    {
        $stream = new Stream(fopen(Configure::read('App.lastIdFile'), 'r'));
        $lastId = $stream->getContent();
        $stream->close();

        return $lastId;
    }

    protected function writeLastId($lastId)
    {
        $stream = new Stream(fopen(Configure::read('App.lastIdFile'), 'w'));
        $stream->write($lastId);
        $stream->close();
    }
}
