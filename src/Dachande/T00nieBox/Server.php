<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use GuzzleHttp\Exception\ClientException;
use JJG\Ping;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * t00niebox server interaction.
 *
 * This class is used to interact with the t00niebox server.
 * It does general reachability checks and retrieves playlists and
 * eventually other information from the server as well.
 */
class Server
{
    use \Cake\Log\LogTrait;

    /**
     * Stores if the server is reachable
     *
     * @var boolean
     */
    protected static $isReachable = null;

    /**
     * Check the t00niebox server reachability
     *
     * Checking that the server is available is essential for a working
     * t00niebox environment because the client acts differently depending on
     * the reachability of the server.
     *
     * @return boolean
     */
    public static function checkReachability()
    {
        $host = Configure::read('Server.host');
        $port = Configure::read('Server.port');

        static::log(sprintf('Trying to reach the server at %s:%s.', $host, $port), 'debug');

        $ping = new Ping(Configure::read('Server.host'));
        $ping->setPort(Configure::read('Server.port'));
        $ping->setTimeout(1);

        $latency = $ping->ping('fsockopen');

        if ($latency !== false) {
            static::log(sprintf('Server is reachable with a latency of %d.', $latency), 'debug');
        } else {
            static::log('Server is unreachable', 'debug');
        }

        static::$isReachable = ($latency !== false) ? true : false;
        return static::$isReachable;
    }

    /**
     * Return server reachability.
     *
     * Does a reachability check if reachability has not yet been checked or
     * if a reachability check is being forced by setting $recheck to true.
     *
     * @param  boolean $recheck
     * @return boolean
     */
    public static function isReachable($recheck = false)
    {
        if ($recheck === true || static::$isReachable === null) {
            return static::checkReachability();
        }

        return static::$isReachable;
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
    protected static function query($endpoint, $method = 'GET')
    {
        if (static::isReachable() === true) {
            $url = static::getURI() . '/' . $endpoint;

            static::log(sprintf('Sending server request to %s', $url), 'debug');

            $client = new Client();
            $result = $client->request($method, $url);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get protocol://host:port combination from values stored in configuration
     *
     * @return string
     */
    protected static function getURI()
    {
        $protocol = Configure::read('Server.protocol');
        $hostname = Configure::read('Server.host');
        $port = Configure::read('Server.port');

        return $protocol . '://' . $hostname . ':' . $port;
    }

    public static function getAllPlaylists()
    {
        try {
            $result = static::query('playlists')->getBody()->getContents();
        } catch (ClientException $e) {
            $result = $e->getResponse()->getBody()->getContents();
        }

        return json_decode($result, true);
    }

    public static function getPlaylistByUuid($uuid)
    {
        try {
            $result = static::query('playlists/' . $uuid)->getBody()->getContents();
        } catch (ClientException $e) {
            $result = $e->getResponse()->getBody()->getContents();
        }

        return json_decode($result, true);
    }
}
