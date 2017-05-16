<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use JJG\Ping;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

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
        static::log(sprintf('%s', __METHOD__), 'debug');

        $host = Configure::read('Server.host');
        $port = Configure::read('Server.port');

        static::log(sprintf('Server - Trying to reach the server at %s:%s.', $host, $port), 'info');

        $ping = new Ping(Configure::read('Server.host'));
        $ping->setPort(Configure::read('Server.port'));
        $ping->setTimeout(1);

        $latency = $ping->ping('fsockopen');

        if ($latency !== false) {
            static::log(sprintf('Server - Server is reachable with a latency of %d.', $latency), 'info');
        } else {
            static::log('Server - Server is unreachable', 'warning');
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
        static::log(sprintf('%s', __METHOD__), 'debug');

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
    public static function query($endpoint, $method = 'GET')
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        if (static::isReachable() === true) {
            $url = static::getURI() . '/' . $endpoint;

            static::log(sprintf('Server - Sending server request to %s', $url), 'notice');

            $client = new Client();

            try {
                $result = $client->request($method, $url);
                static::log(sprintf('Server - Server returned code: %d.', $result->getStatusCode()), 'info');
            } catch (\Exception $e) {
                $result = false;
                static::log(sprintf('Server - Connection to server failed with message: %s.', $e->getMessage()), 'warning');
            }
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
        static::log(sprintf('%s', __METHOD__), 'debug');

        $protocol = Configure::read('Server.protocol');
        $hostname = Configure::read('Server.host');
        $port = Configure::read('Server.port');

        return $protocol . '://' . $hostname . ':' . $port;
    }

    /**
     * Get all playlists from the server.
     *
     * In a default t00niebox implementation this method should not be used
     * as it is currently only used for debugging purposes.
     * It might be removed in the near future.
     *
     * @return array|null
     */
    public static function getAllCards()
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        try {
            $result = static::query('cards');

            if ($result !== false) {
                $result = $result->getBody()->getContents();
            }
        } catch (ClientException $e) {
            $result = $e->getResponse()->getBody()->getContents();
        }

        return json_decode($result, true);
    }

    /**
     * Get a single playlist from the server by its uuid.
     *
     * @param  string $uuid
     * @return array|null
     */
    public static function getCardByUuid($uuid)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        try {
            $result = static::query('cards/' . $uuid);

            if ($result !== false) {
                $result = $result->getBody()->getContents();
            }
        } catch (ClientException $e) {
            $result = $e->getResponse()->getBody()->getContents();
        }

        return json_decode($result, true);
    }
}
