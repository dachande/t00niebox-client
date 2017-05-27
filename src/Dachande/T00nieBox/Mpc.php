<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;

use Streamer\Stream;

class Mpc
{
    use \Cake\Log\LogTrait;

    // Responses
    const RES_OK = "OK";
    const RES_ERR = "ACK";

    // States
    const STATE_PLAYING = "play";
    const STATE_STOPPED = "stop";
    const STATE_PAUSED = "pause";

    // Commands
    const CMD_PWD = "password";
    const CMD_STATS = "stats";
    const CMD_STATUS = "status";
    const CMD_PLIST = "playlistinfo";
    const CMD_CONSUME = "consume";
    const CMD_XFADE = "crossfade";
    const CMD_RANDOM = "random";
    const CMD_REPEAT = "repeat";
    const CMD_SETVOL = "setvol";
    const CMD_SINGLE = "single";
    const CMD_NEXT = "next";
    const CMD_PREV = "previous";
    const CMD_PAUSE = "pause";
    const CMD_PLAY = "play";
    const CMD_PLAYID = "playid";
    const CMD_SEEK = "seek";
    const CMD_SEEKID = "seekid";
    const CMD_STOP = "stop";
    const CMD_PL_ADD = "add";
    const CMD_PL_ADDID = "addid";
    const CMD_PL_CLEAR = "clear";
    const CMD_PL_DELETEID = "deleteid";
    const CMD_PL_MOVE = "move";
    const CMD_PL_MOVE_MULTI = "move";
    const CMD_PL_MOVE_ID = "moveid";
    const CMD_PL_SHUFFLE = "shuffle";
    const CMD_DB_DIRLIST = "lsinfo";
    const CMD_DB_LIST = "list";
    const CMD_DB_SEARCH = "search";
    const CMD_DB_COUNT = "count";
    const CMD_DB_UPDATE = "update";
    const CMD_CURRENTSONG = "currentsong";
    const CMD_LISTPLAYLISTS = "listplaylists";
    const CMD_PLAYLISTINFO = "listplaylistinfo";
    const CMD_PLAYLISTLOAD = "load";
    const CMD_PLAYLISTADD = "playlistadd";
    const CMD_PLAYLISTCLEAR = "playlistclear";
    const CMD_PLAYLISTDELETE = "playlistdelete";
    const CMD_PLAYLISTMOVE = "playlistmove";
    const CMD_PLAYLISTRENAME = "rename";
    const CMD_PLAYLISTREMOVE = "rm";
    const CMD_PLAYLISTSAVE = "save";
    const CMD_CLOSE = "close";
    const CMD_KILL = "kill";

    /**
     * Server connection state
     *
     * @var boolean
     */
    protected static $isConnected = false;

    /**
     * Authentication state
     *
     * @var boolean
     */
    protected static $isAuthenticated = false;

    /**
     * Server connection
     *
     * @var \Streamer\Stream
     */
    protected static $server = null;

    /**
     * Connect to the MPD server
     *
     * @param boolean $reconnect
     * @return boolean
     */
    public static function connect($reconnect = false)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        if (static::$server === null || static::$isConnected === false || $reconnect === true) {
            // Close the server connection if it is open
            if (static::$server !== null || static::$isConnected === true) {
                static::disconnect();
            }

            // Connect
            $serverAddress = Configure::read('Mpd.host') . ':' . Configure::read('Mpd.port');
            static::log(sprintf('Mpc - Connecting to MPD server at %s', $serverAddress), 'info');
            static::$server = new Stream(stream_socket_client('tcp://' . Configure::read('Mpd.host') . ':' . Configure::read('Mpd.port')));

            $message = static::$server->read();

            if (strncmp(static::RES_OK, $message, strlen(static::RES_OK)) === 0) {
                static::log('Mpc - Connection established.', 'info');
                static::$isConnected = true;
                static::$isAuthenticated = false;
                return true;
            } else {
                static::log('Mpc - Connection failed.', 'warning');
                return false;
            }
        }
    }

    /**
     * Disconnect from MPD server
     *
     * @return void
     */
    public static function disconnect()
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        if (static::$server !== null && static::$server instanceof \Streamer\Stream) {
            static::$server->close();
            static::$isConnected = false;
            static::$isAuthenticated = false;
        }

        static::$server = null;
    }

    /**
     * Flattens and converts command argument list
     *
     * @param array $array
     * @return array
     */
    protected static function condense(array $args)
    {
        $result = [];

        foreach (array_values($args) as $value) {
            if (is_scalar($value)) {
                $result[] = $value;
            } elseif (is_array($value)) {
                $result = array_merge($result, static::condense($value));
            } elseif (is_object($value)) {
                $result = array_merge($result, static::condense((array)$value));
            } else {
                throw new \Exception("Unrecognized object type");
            }
        }

        return $result;
    }

    /**
     * Dispatch a command to the MPD server
     *
     * @return boolean
     */
    protected static function command()
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        // Connect if not already connected
        if (static::$isConnected === false) {
            static::connect();
        }

        // Get arguments and method
        $args = func_get_args();
        $method = array_shift($args);
        $args = static::condense($args);

        // Prepare arguments
        array_walk($args, function (&$value, $key) {
            $value = str_replace('"', '\"', $value);
            $value = str_replace("'", "\\'", $value);
            $value = '"' . $value . '"';
        });

        // Prepare command
        $command = trim($method . ' ' . implode(' ', $args)) . "\n";

        static::log(sprintf('Mpc - Dispatching command "%s" with arguments %s.', $method, implode('|', $args)), 'debug');

        // Send command to server
        static::$server->write($command);

        // Receive Response
        $response = static::$server->read();

        static::log(sprintf('Mpc - Server response: "%s".', trim(preg_replace('/\s\s+/', ' ', $response))), 'debug');

        return (strncmp(static::RES_OK, $response, strlen(static::RES_OK)) === 0) ? true : false;
    }

    /**
     * Authenticate at the MPD server.
     *
     * Authentication is required even if the server does not need a password
     * to make sure we have read access to the server at least.
     *
     * @return boolean
     */
    public static function authenticate()
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        if (static::$isAuthenticated === true) {
            return true;
        }

        if (Configure::check('Mpd.password') === true) {
            static::log('Mpc - Authenticating with password', 'debug');
            if (static::command(static::CMD_PWD, Configure::read('Mpd.password')) === false) {
                static::log('Mpc - Password authentication failed', 'error');

                static::disconnect();
                return false;
            }

            static::log('Mpc - Successfully authenticated.', 'debug');

            if (static::update() === false) {
                static::log('Mpc - Password does not have read access.', 'error');

                static::disconnect();
                return false;
            }
        } else {
            if (static::update() === false) {
                static::log('Mpc - Password required to access server', 'error');

                static::disconnect();
                return false;
            }

            static::log('Mpc - Successfully authenticated.', 'debug');
        }

        static::$isAuthenticated = true;
        return true;
    }

    public static function update()
    {
        // TODO: Add logic to update server information locally.
        return true;
    }

    /**
     * Play new playlist.
     *
     * This will clear the current playlist being played, adds a new Playlist
     * and starts playback of the new playlist.
     *
     * @param string $playlist
     */
    public static function playNewPlaylist($playlist)
    {
        // TODO: Check commands to send to server to play a new playlist

        static::command(static::CMD_PL_CLEAR);
        static::command(static::CMD_PL_ADD, $playlist);
        static::command(static::CMD_PLAY);
    }
}
