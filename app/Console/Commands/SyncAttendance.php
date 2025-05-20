<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jmrashed\Zkteco\Lib\ZKTeco;
use App\Models\Attendance;
use Carbon\Carbon;

class SyncAttendance extends Command
{
    protected $signature = 'sync:attendance';
    protected $description = 'Sync attendance data from biometric device to the database, mark absentees';

    public function handle()
    {
        $ip = '192.168.1.201';
        $port = 4370;

        $zk = new ZKTeco($ip, $port);

        if (!$zk->connect()) {
            $this->error('Failed to connect to the biometric device.');
            return;
        }

        $this->info('Connected to device. Fetching data...');

        $attendances = $zk->getAttendance(); // All punched-in data
        $users = $zk->getUser();             // All registered users
        $today = Carbon::today();

        $presentUserIds = [];

        // Step 1: Store present users
        foreach ($attendances as $att) {
            $userId = $att['id'];
            $timestamp = Carbon::parse($att['timestamp'])->startOfMinute();
            $userName = $users[$userId]['name'] ?? 'Unknown';

            $presentUserIds[] = $userId;

            Attendance::updateOrCreate(
                [
                    'device_id' => $userId,
                    'user_id' => $userId,
                    'timestamp' => $timestamp,
                ],
                [
                    'name' => $userName,
                    'status' => 'Present',
                ]
            );
        }

        // Step 2: Mark absent users (who did NOT punch in)
        foreach ($users as $userId => $userData) {
            if (!in_array($userId, $presentUserIds)) {
                $exists = Attendance::where('user_id', $userId)
                    ->whereDate('timestamp', $today)
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'device_id' => $userId,
                        'user_id' => $userId,
                        'name' => $userData['name'] ?? 'Unknown',
                        'timestamp' => $today, // Optional: set time to '00:00:00'
                        'status' => 'Absent',
                    ]);
                }
            }
        }

        $zk->disconnect();

        $this->info('Attendance sync complete: Present and Absent users recorded.');
    }
}
