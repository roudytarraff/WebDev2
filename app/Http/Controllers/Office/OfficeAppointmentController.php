<?php

namespace App\Http\Controllers\Office;

use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Http\Request;

class OfficeAppointmentController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $appointments = Appointment::with(['citizen', 'request.service', 'slot'])
            ->where('office_id', $office->id)
            ->latest()
            ->get();

        return view('office.appointments.index', compact('office', 'appointments'));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();
        $appointment = Appointment::with(['citizen', 'request.service', 'slot'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        return view('office.appointments.show', compact('appointment'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $office = $this->currentOffice();
        $appointment = Appointment::where('office_id', $office->id)->findOrFail($id);
        $appointment->status = $request->status;
        $appointment->notes = $request->notes;
        $appointment->save();

        Notification::create([
            'user_id' => $appointment->citizen_user_id,
            'type' => 'appointment',
            'title' => 'Appointment updated',
            'message' => 'Your appointment status is now ' . $request->status . '.',
            'channel' => 'system',
            'is_read' => false,
        ]);

        return back()->with('success', 'Appointment updated successfully.');
    }
}
