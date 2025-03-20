<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Jmrashed\Zkteco\Lib\ZKTeco;
use Jmrashed\Zkteco\Lib\ZKTeco as LibZKTeco;

class ZKTecoService
{
    protected $zk;

    public function __construct()
    {
        $this->zk = new ZKTeco('192.168.1.201', 4370);  
    }

    public function connect()
    {
        return $this->zk->connect();
    }

    public function getUsers()
    {
        $this->connect(); 
        $users = $this->zk->getUser();
        $this->zk->disconnect(); 

        return $users; 
    }
    

    public function getAttendance()
    {
        $this->connect();
        $attendance = $this->zk->getAttendance();  
        $this->zk->disconnect();

        return $attendance;
    }
    
}
