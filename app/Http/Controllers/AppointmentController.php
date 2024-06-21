<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Http\Requests\StoreAppointmentRequest;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller {
    public function create() {
        return view('appointments.create');
    }

    public function store(StoreAppointmentRequest $request) {
        
        // Combine the date and time from the request
        $dateTimeString = $request->appointment_date . ' ' . $request->appointment_time;
        
        // Convert to a Unix timestamp
        $timestamp = strtotime($dateTimeString);

        // Check if the timestamp
        if ($timestamp === false) {
            return response()->json(['error' => 'Invalid date or time format.'], 400);
        }

        // Format the start time
        $startTime = date('H:i', $timestamp);
        $dateOnly = date('Y-m-d', $timestamp);

        // Calculate the end time (1 hour later)
        $endTimestamp = strtotime('+1 hour', $timestamp);
        $endTime = date('H:i', $endTimestamp);

        // Store the appointment
        Appointment::create([
            'user_id' => Auth::id(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'appointment_date' => $dateOnly,
            'appointment_start_time' => $startTime,
            'appointment_end_time' => $endTime
        ]);
        
        return response()->json(['message' => 'Appointment booked successfully.']);
    }

    public function getAvailableSlots(Request $request) {
        $date = $request->date;

        // Get all booked time slots
        $bookedSlots = Appointment::where('appointment_date', $date)
            ->get(['appointment_start_time'])
            ->pluck('appointment_start_time')
            ->toArray();

        // Generate all possible time slots
        $allSlots = $this->generateTimeSlots();

        // Filter out booked slots
        $availableSlots = array_filter($allSlots, function($slot) use ($bookedSlots) {
            return in_array($slot, $bookedSlots);
        });

        return response()->json(['slots' => array_values($availableSlots)]);
    }

    private function generateTimeSlots() {
        $slots = [];
        for ($i = 10; $i < 19; $i++) {
            $time = sprintf('%02d:00:00', $i);
            $slots[] = $time;
        }
        return $slots;
    }

    public function checkDateAvailability(Request $request) {
        $date = $request->date;
        $maxSlots = 9;
        $bookedCount = Appointment::where('appointment_date', $date)->count();

        // Check if all slots are booked
        $isAvailable = $bookedCount < $maxSlots;

        return response()->json(['isAvailable' => $isAvailable]);
    }
}