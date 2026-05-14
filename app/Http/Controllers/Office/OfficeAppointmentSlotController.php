<?php

namespace App\Http\Controllers\Office;

use App\Models\AppointmentSlot;
use App\Models\Service;
use Illuminate\Http\Request;

class OfficeAppointmentSlotController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $slots = AppointmentSlot::with(['service', 'appointments'])
            ->where('office_id', $office->id)
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        return view('office.appointment_slots.index', compact('office', 'slots'));
    }

    public function create()
    {
        $office = $this->currentOffice();
        $services = Service::where('office_id', $office->id)->where('requires_appointment', true)->orderBy('name')->get();
        return view('office.appointment_slots.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $office = $this->currentOffice();
        Service::where('office_id', $office->id)->findOrFail($request->service_id);

        $slot = new AppointmentSlot();
        $this->fillSlot($slot, $request, $office->id);
        $slot->save();

        return redirect()->route('office.appointment-slots.index')->with('success', 'Appointment slot created successfully.');
    }

    public function edit(string $id)
    {
        $office = $this->currentOffice();
        $slot = AppointmentSlot::where('office_id', $office->id)->findOrFail($id);
        $services = Service::where('office_id', $office->id)->where('requires_appointment', true)->orderBy('name')->get();
        return view('office.appointment_slots.edit', compact('slot', 'services'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate($this->rules());

        $office = $this->currentOffice();
        Service::where('office_id', $office->id)->findOrFail($request->service_id);

        $slot = AppointmentSlot::where('office_id', $office->id)->findOrFail($id);
        $this->fillSlot($slot, $request, $office->id);
        $slot->save();

        return redirect()->route('office.appointment-slots.index')->with('success', 'Appointment slot updated successfully.');
    }

    public function destroy(string $id)
    {
        $office = $this->currentOffice();
        $slot = AppointmentSlot::where('office_id', $office->id)->findOrFail($id);
        $slot->delete();

        return redirect()->route('office.appointment-slots.index')->with('success', 'Appointment slot deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'slot_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,full,disabled',
        ];
    }

    private function fillSlot(AppointmentSlot $slot, Request $request, int $officeId): void
    {
        $slot->office_id = $officeId;
        $slot->service_id = $request->service_id;
        $slot->slot_date = $request->slot_date;
        $slot->start_time = $request->start_time;
        $slot->end_time = $request->end_time;
        $slot->capacity = $request->capacity;
        $slot->status = $request->status;
    }
}
