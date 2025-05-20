<?php

namespace App\Http\Controllers;

use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
class AttendanceController extends Controller
{
    protected $zkService;

    public function __construct(ZKTecoService $zkService)
    {
        $this->zkService = $zkService;
    }
     public function checkConnection()
    {
        $connectionStatus = $this->zkService->connect(); 

        if ($connectionStatus) {
            return response()->json(['status' => 'success', 'message' => 'Connected to the device']);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to connect to the device'], 500);
    }


    public function getUsers()
    {
        $users = $this->zkService->getUsers();

        if (!empty($users)) {
            return response()->json(['success' => true, 'users' => $users], 200);
        }

        return response()->json(['success' => false, 'message' => 'No users found'], 404);
    }


    public function getAttendanceByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date', 
        ]);
        $inputDate = Carbon::parse($request->date)->format('Y-m-d');

        $attendanceData = $this->zkService->getAttendance();

        $filteredAttendance = array_filter($attendanceData, function($attendance) use ($inputDate) {
            $attendanceDate = Carbon::parse($attendance['timestamp'])->format('Y-m-d');

            return $attendanceDate === $inputDate;
        });

        if (count($filteredAttendance) > 0) {
            return response()->json([
                'success' => true,
                'attendance' => array_values($filteredAttendance),  
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No attendance records found for the specified date'
        ], 404);
    }
    public function storeAttendance(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:users,id',
            'timestamp' => 'nullable|date',
        ]);

        $userId = $validatedData['id'];
        $timestamp = $validatedData['timestamp'] ?? now();

        $attendanceData = $request->input('attendance');
    
        foreach ($attendanceData as $attendance) {
            Attendance::create([
                'user_id' => $attendance['id'],
                'biometric_id' => $attendance['uid'], 
                'timestamp' => $attendance['timestamp'],
                'status' => 'Present',
                'state' => $attendance['state'],
                'type' => $attendance['type'],
            ]);
        }
    
        return response()->json(['message' => 'Attendance recorded successfully'], 200);
    }

public function getAttendanceByDB(Request $request)
{
    $request->validate([
        'date' => 'required|date',
    ]);

    $inputDate = Carbon::parse($request->date)->format('Y-m-d');
    $attendanceData = Attendance::whereDate('timestamp', $inputDate)->get();

    if ($attendanceData->count() > 0) {
        return response()->json([
            'success' => true,
            'attendance' => $attendanceData,
        ], 200);
    }

    return response()->json([
        'success' => false,
        'message' => 'No attendance found for the given date',
    ], 404);
}


}

