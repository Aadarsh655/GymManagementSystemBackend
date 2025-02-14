<?php

namespace App\Services;

use Rats\Zkteco\Lib\ZKTeco;


class ZKTecoService
{
    protected $zk;

    public function __construct()
    {
        $this->zk = new ZKTeco(config('zkteco.ip'), config('zkteco.port'));

    }

    public function connect()
    {
        return $this->zk->connect();
    }

    public function getAttendanceData()
    {
        // Example: Get attendance records
        return $this->zk->getAttendance();
    }

    public function addUser($uid, $userid, $name, $password = '', $role = 0, $cardno = 0)
    {
        return $this->zk->setUser($uid, $userid, $name, $password, $role, $cardno);
    }
    public function getUser()
    {
        return $this->zk->getUser();
    }
}
?>