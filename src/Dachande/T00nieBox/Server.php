<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use JJG\Ping;

/**
 * t00niebox server interaction.
 *
 * This class is used to interact with the t00niebox server.
 * It does general reachability checks and retrieves playlists and
 * eventually other information from the server as well.
 */
class Server
{
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
        $ping = new Ping(Configure::read('Server.host'));
        $ping->setPort(Configure::read('Server.port'));
        $ping->setTimeout(1);

        $latency = $ping->ping('fsockopen');

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
}
