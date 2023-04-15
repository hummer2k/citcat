<?php

namespace App\Helper;

use Doctrine\DBAL\Connection;

declare(ticks=3000000);

class ConnectionKeepAlive
{
    /**
     * @var Connection[]
     */
    protected $connections;

    protected $isAttached;

    public function __construct()
    {
        $this->connections = array();
        $this->isAttached = false;
    }

    public function detach()
    {
        unregister_tick_function(array($this, 'kick'));
        $this->isAttached = false;
    }

    public function attach()
    {
        if ($this->isAttached || register_tick_function(array($this, 'kick'))) {
            $this->isAttached = true;
            return;
        }
        throw new \RuntimeException('Unable to attach keep alive to the system');
    }

    public function addConnection(Connection $logConnection)
    {
        $this->connections[spl_object_hash($logConnection)] = $logConnection;
    }

    public function kick()
    {
        foreach ($this->connections as $conn) {
            try {
                $conn->executeQuery('SELECT 1')->closeCursor();
            } catch (\Exception $e) {
                if ($conn === null || stripos($e->getMessage(),
                        'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') === false) {
                    throw $e;
                }
                $conn->close();
                $conn->connect();
            }
        }
    }
}
