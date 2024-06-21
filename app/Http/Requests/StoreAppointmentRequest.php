<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $dateTimeString = $this->appointment_date . ' ' . $this->appointment_time;
        $startTimestamp = strtotime($dateTimeString);
        $startDate = date('Y-m-d', $startTimestamp);
        $startOfWeek = date('Y-m-d', strtotime('monday this week', $startTimestamp));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week', $startTimestamp));

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('appointments')->where(function ($query) use ($startOfWeek, $endOfWeek) {
                    return $query->whereBetween('appointment_date', [$startOfWeek, $endOfWeek]);
                })
            ],
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages() {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'You cannot book more than one appointment per week with the same email address.',
            'appointment_date.required' => 'Appointment date is required.',
            'appointment_time.required' => 'Appointment time is required.',
        ];
    }
}