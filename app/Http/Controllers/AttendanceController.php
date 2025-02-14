<?php

namespace App\Http\Controllers;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
class AttendanceController extends Controller
{
    protected $zkteco;

    public function __construct(ZKTecoService $zkteco)
    {
        $this->zkteco = $zkteco;
    }

    public function showAttendance()
    {
        $attendanceData = $this->zkteco->getAttendanceData();
        return view('attendance.index', compact('attendanceData'));
    }

    public function connect()
    {
        if ($this->zkteco->connect()) {
            return response()->json(['success' => true, 'message' => 'Connected successfully!']);
        }
        return response()->json(['success' => false, 'message' => 'Failed to connect to ZKTeco.']);
    }

    public function userList($limit = 10)
    {
        $users = $this->zkteco->getUser(); 
    
        if (!empty($users)) {
            $limitedUsers = array_slice($users, 0, $limit);
            return response()->json(['success' => true, 'users' => $limitedUsers], 200);
        }
    
        return response()->json(['success' => false, 'message' => 'No users found'], 404);
    }
    
}
