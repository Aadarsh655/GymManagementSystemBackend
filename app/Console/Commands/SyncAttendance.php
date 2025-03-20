<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jmrashed\Zkteco\Lib\ZKTeco;
use App\Models\Attendance;
use Carbon\Carbon;

class SyncAttendance extends Command
{
    protected $signature = 'sync:attendance';
    protected $description = 'Sync attendance data from biometric device to the database';


    public function handle()
    {
        $ip = '192.168.1.201';
        $port = 4370; 
    
        $zk = new ZKTeco($ip, $port);
        
        if ($zk->connect()) {
            $attendances = $zk->getAttendance();
            $users = $zk->getUser(); 
            $date = now()->format('Y-m-d');
    
            $presentUserIds = [];
            foreach ($attendances as $att) {
                $status = $att['status'] ?? 'Present'; 
                $userId = $att['id']; 
                $userName = isset($users[$userId]) ? $users[$userId]['name'] : 'Unknown';
    
                $presentUserIds[] = $userId;
    
                Attendance::updateOrCreate(
                    [
                        'device_id' => $att['id'], 
                        'user_id' => $att['id'], 
                        'timestamp' => Carbon::parse($att['timestamp']),
                    ],
                    [
                        'name' => $userName,
                        'status' => $status
                    ]
                );
            }
            foreach ($users as $userId => $userData) {
                if (!in_array($userId, $presentUserIds)) {
                    Attendance::updateOrCreate(
                        [
                            'device_id' => $userId,
                            'user_id' => $userId,
                            'timestamp' => Carbon::parse($date . ' 23:59:59'), 
                        ],
                        [
                            'name' => $userData['name'] ?? 'Unknown',
                            'status' => 'Absent' 
                        ]
                    );
                }
            }
            $zk->disconnect();
            $this->info('Attendance data synced successfully, absentees marked.');
        } else {
            $this->error('Failed to connect to the biometric device.');
        }
    }
    
}
