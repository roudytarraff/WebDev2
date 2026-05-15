<?php

namespace App\Http\Controllers\Office;

use App\Models\AppointmentSlot;
use App\Models\OfficeWorkingHour;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OfficeAppointmentSlotController extends OfficeBaseController
{
    public function index(Request $request)
    {
        $office = $this->currentOffice();

        $selectedServiceId = $request->input('service_id');
        $selectedStatus = $request->input('status');
        $selectedDate = $request->input('slot_date');

        $services = $this->appointmentServices($office->id);
        $bookedStatuses = $this->bookedStatuses();

        $slots = AppointmentSlot::with(['service'])
            ->withCount([
                'appointments as booked_appointments_count' => function ($query) use ($bookedStatuses) {
                    $query->whereIn('status', $bookedStatuses);
                },
            ])
            ->where('office_id', $office->id)
            ->when($selectedServiceId, function ($query) use ($selectedServiceId) {
                $query->where('service_id', $selectedServiceId);
            })
            ->when($selectedStatus, function ($query) use ($selectedStatus) {
                $query->where('status', $selectedStatus);
            })
            ->when($selectedDate, function ($query) use ($selectedDate) {
                $query->whereDate('slot_date', $selectedDate);
            })
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        $totalSlots = $slots->count();
        $availableSlots = $slots->where('status', 'available')->count();
        $fullSlots = $slots->where('status', 'full')->count();
        $disabledSlots = $slots->where('status', 'disabled')->count();

        return view('office.appointment_slots.index', compact(
            'office',
            'slots',
            'services',
            'selectedServiceId',
            'selectedStatus',
            'selectedDate',
            'totalSlots',
            'availableSlots',
            'fullSlots',
            'disabledSlots'
        ));
    }

    public function create()
    {
        $office = $this->currentOffice();
        $services = $this->appointmentServices($office->id);

        return view('office.appointment_slots.create', compact('office', 'services'));
    }

    public function store(Request $request)
    {
        $office = $this->currentOffice();

        $validated = $this->validatedData($request, $office->id);

        $slot = new AppointmentSlot();
        $this->fillSlot($slot, $validated, $office->id);
        $slot->save();

        return redirect()
            ->route('office.appointment-slots.index')
            ->with('success', 'Appointment slot created successfully.');
    }

    public function edit(string $id)
    {
        $office = $this->currentOffice();
        $bookedStatuses = $this->bookedStatuses();

        $slot = AppointmentSlot::with(['service'])
            ->withCount([
                'appointments as booked_appointments_count' => function ($query) use ($bookedStatuses) {
                    $query->whereIn('status', $bookedStatuses);
                },
            ])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        $services = $this->appointmentServices($office->id);

        if ($slot->service && ! $services->contains('id', $slot->service_id)) {
            $services = $services->push($slot->service)->sortBy('name')->values();
        }

        return view('office.appointment_slots.edit', compact('office', 'slot', 'services'));
    }

    public function update(Request $request, string $id)
    {
        $office = $this->currentOffice();

        $slot = AppointmentSlot::where('office_id', $office->id)->findOrFail($id);

        $validated = $this->validatedData($request, $office->id, $slot);

        $this->fillSlot($slot, $validated, $office->id);
        $slot->save();

        return redirect()
            ->route('office.appointment-slots.index')
            ->with('success', 'Appointment slot updated successfully.');
    }

    public function destroy(string $id)
    {
        $office = $this->currentOffice();

        $slot = AppointmentSlot::where('office_id', $office->id)->findOrFail($id);

        $bookedAppointments = $slot->appointments()
            ->whereIn('status', $this->bookedStatuses())
            ->count();

        if ($bookedAppointments > 0) {
            return back()->withErrors([
                'slot' => 'This slot already has appointments. Disable it instead of deleting it.',
            ]);
        }

        $slot->delete();

        return redirect()
            ->route('office.appointment-slots.index')
            ->with('success', 'Appointment slot deleted successfully.');
    }

    private function validatedData(Request $request, int $officeId, ?AppointmentSlot $slot = null): array
    {
        $validator = Validator::make($request->all(), [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(function ($query) use ($officeId) {
                    $query->where('office_id', $officeId)
                        ->where('status', 'active')
                        ->where('requires_appointment', true);
                }),
            ],
            'slot_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['available', 'full', 'disabled'])],
        ]);

        $validator->after(function ($validator) use ($request, $officeId, $slot) {
            if ($validator->errors()->any()) {
                return;
            }

            $serviceId = (int) $request->input('service_id');
            $slotDate = $request->input('slot_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $capacity = (int) $request->input('capacity');

            if (! $this->isInsideWorkingHours($officeId, $slotDate, $startTime, $endTime)) {
                $validator->errors()->add(
                    'slot_date',
                    'The slot must be inside the office working hours for the selected day.'
                );
            }

            if ($this->hasOverlappingSlot($officeId, $serviceId, $slotDate, $startTime, $endTime, $slot?->id)) {
                $validator->errors()->add(
                    'start_time',
                    'Another active slot already overlaps this date and time for the selected service.'
                );
            }

            if ($slot) {
                $bookedAppointments = $slot->appointments()
                    ->whereIn('status', $this->bookedStatuses())
                    ->count();

                if ($capacity < $bookedAppointments) {
                    $validator->errors()->add(
                        'capacity',
                        'Capacity cannot be less than the number of already booked appointments.'
                    );
                }
            }
        });

        return $validator->validate();
    }

    private function fillSlot(AppointmentSlot $slot, array $data, int $officeId): void
    {
        $slot->office_id = $officeId;
        $slot->service_id = $data['service_id'];
        $slot->slot_date = $data['slot_date'];
        $slot->start_time = $data['start_time'];
        $slot->end_time = $data['end_time'];
        $slot->capacity = $data['capacity'];
        $slot->status = $data['status'];
    }

    private function appointmentServices(int $officeId)
    {
        return Service::where('office_id', $officeId)
            ->where('status', 'active')
            ->where('requires_appointment', true)
            ->orderBy('name')
            ->get();
    }

    private function isInsideWorkingHours(int $officeId, string $slotDate, string $startTime, string $endTime): bool
    {
        $weekdayNumber = Carbon::parse($slotDate)->dayOfWeekIso;

        $workingHour = OfficeWorkingHour::where('office_id', $officeId)
            ->where('weekday_number', $weekdayNumber)
            ->first();

        if (! $workingHour || $workingHour->is_closed) {
            return false;
        }

        $openTime = substr($workingHour->open_time, 0, 5);
        $closeTime = substr($workingHour->close_time, 0, 5);

        return $startTime >= $openTime && $endTime <= $closeTime;
    }

    private function hasOverlappingSlot(
        int $officeId,
        int $serviceId,
        string $slotDate,
        string $startTime,
        string $endTime,
        ?int $ignoreSlotId = null
    ): bool {
        return AppointmentSlot::where('office_id', $officeId)
            ->where('service_id', $serviceId)
            ->whereDate('slot_date', $slotDate)
            ->where('status', '!=', 'disabled')
            ->when($ignoreSlotId, function ($query) use ($ignoreSlotId) {
                $query->where('id', '!=', $ignoreSlotId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();
    }

    private function bookedStatuses(): array
    {
        return ['scheduled', 'completed'];
    }
}