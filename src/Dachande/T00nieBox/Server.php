<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use JJG\Ping;

class Server
{
    /**
     * Stores if the server is reachable

     * @var boolean
     */
    protected $isReachable = false;

    /**
     * This method checks if the t00niebox server is reachable.
     *
     * Checking that the server is available is essential for a working
     * t00niebox environment because the client acts differently depending on
     * the availability of the server.
     *
     * @return boolean
     */
    public function checkReachability()
    {
        $ping = new Ping(Configure::read('Server.host'));
        $ping->setPort(Configure::read('Server.port'));
        $ping->setTimeout(1);

        $latency = $ping->ping('fsockopen');

        $this->isReachable = ($latency !== false) ? true : false;
        return $this->isReachable;
    }

    public function isReachable($recheck = false)
    {
        return ($recheck === true) ? $this->checkReachability() : $this->isReachable;
    }
}
