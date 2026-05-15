<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Notification;
use App\Models\OfficeStaff;
use App\Models\Payment;
use App\Models\RequestDocument;
use App\Models\RequestStatusHistory;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Services\RequestQrCodeService;

class CitizenServiceRequestController extends Controller
{
    private string $sessionKey = 'citizen_service_request_wizard';

    public function start(Service $service)
    {
        $service->load(['office.municipality', 'category', 'documents']);

        if ($service->status !== 'active') {
            return redirect()
                ->route('discovery.services.show', $service->id)
                ->withErrors(['service' => 'This service is not available right now.']);
        }

        session()->put($this->sessionKey, [
            'service_id' => $service->id,
            'description' => null,
            'documents' => [],
            'slot_id' => null,
            'payment_method' => null,
        ]);

        return view('citizen.service-requests.start', compact('service'));
    }

    public function storeDetails(Request $request, Service $service)
    {
        $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $wizard = session($this->sessionKey, []);

        $wizard['service_id'] = $service->id;
        $wizard['description'] = $request->description;
        $wizard['documents'] = $wizard['documents'] ?? [];
        $wizard['slot_id'] = $wizard['slot_id'] ?? null;
        $wizard['payment_method'] = $wizard['payment_method'] ?? null;

        session()->put($this->sessionKey, $wizard);

        return redirect()->route('citizen.service-requests.documents');
    }

    public function documents()
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with(['office', 'documents'])->findOrFail($wizard['service_id']);

        return view('citizen.service-requests.documents', compact('service', 'wizard'));
    }

    public function storeDocuments(Request $request)
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with('documents')->findOrFail($wizard['service_id']);

        $rules = [];

        foreach ($service->documents as $document) {
            $rule = $document->is_required ? 'required' : 'nullable';

            if (! empty($wizard['documents'][$document->id])) {
                $rule = 'nullable';
            }

            $rules['documents.' . $document->id] = [
                $rule,
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ];
        }

        $request->validate($rules);

        $uploadedDocuments = $wizard['documents'] ?? [];

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $requiredDocumentId => $file) {
                if (! $file) {
                    continue;
                }

                if (isset($uploadedDocuments[$requiredDocumentId]['file_path'])) {
                    Storage::disk('public')->delete($uploadedDocuments[$requiredDocumentId]['file_path']);
                }

                $path = $file->store('request-documents/temp', 'public');

                $uploadedDocuments[$requiredDocumentId] = [
                    'required_document_id' => (int) $requiredDocumentId,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                ];
            }
        }

        $wizard['documents'] = $uploadedDocuments;
        session()->put($this->sessionKey, $wizard);

        if ($service->requires_appointment) {
            return redirect()->route('citizen.service-requests.appointment');
        }

        return redirect()->route('citizen.service-requests.payment');
    }

    public function appointment(Request $request)
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with('office')->findOrFail($wizard['service_id']);

        if (! $service->requires_appointment) {
            return redirect()->route('citizen.service-requests.payment');
        }

        $bookedStatuses = ['scheduled', 'completed'];

        /*
         * This is the safest logic:
         * 1. Prefer slots that match the exact selected service_id.
         * 2. If the project demo data has duplicated service records with the same name,
         *    also allow same-name services in the same office.
         * 3. Never use slots from a different office.
         */
        $matchingServiceIds = Service::where('name', $service->name)
            ->where('office_id', $service->office_id)
            ->pluck('id')
            ->push($service->id)
            ->unique()
            ->values();

        $allAvailableSlots = AppointmentSlot::withCount([
                'appointments as booked_appointments_count' => function ($query) use ($bookedStatuses) {
                    $query->whereIn('status', $bookedStatuses);
                },
            ])
            ->whereIn('service_id', $matchingServiceIds)
            ->where('office_id', $service->office_id)
            ->where('status', 'available')
            ->whereDate('slot_date', '>=', now()->toDateString())
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get()
            ->filter(function ($slot) {
                $remaining = (int) $slot->capacity - (int) $slot->booked_appointments_count;
                return $remaining > 0;
            })
            ->values();

        $availableDates = $allAvailableSlots
            ->pluck('slot_date')
            ->unique()
            ->values();

        $selectedDate = $request->query('date');

        if (! $selectedDate && $availableDates->count() > 0) {
            $selectedDate = $availableDates->first();
        }

        if (! $selectedDate) {
            $selectedDate = now()->toDateString();
        }

        $slots = $allAvailableSlots
            ->filter(function ($slot) use ($selectedDate) {
                return $slot->slot_date == $selectedDate;
            })
            ->values();

        return view('citizen.service-requests.appointment', compact(
            'service',
            'slots',
            'wizard',
            'availableDates',
            'selectedDate'
        ));
    }

    public function storeAppointment(Request $request)
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::findOrFail($wizard['service_id']);

        if (! $service->requires_appointment) {
            return redirect()->route('citizen.service-requests.payment');
        }

        $request->validate([
            'slot_id' => ['required', 'exists:appointment_slots,id'],
        ]);

        $bookedStatuses = ['scheduled', 'completed'];

        $matchingServiceIds = Service::where('name', $service->name)
            ->where('office_id', $service->office_id)
            ->pluck('id')
            ->push($service->id)
            ->unique()
            ->values();

        $slot = AppointmentSlot::withCount([
                'appointments as booked_appointments_count' => function ($query) use ($bookedStatuses) {
                    $query->whereIn('status', $bookedStatuses);
                },
            ])
            ->where('id', $request->slot_id)
            ->whereIn('service_id', $matchingServiceIds)
            ->where('office_id', $service->office_id)
            ->where('status', 'available')
            ->whereDate('slot_date', '>=', now()->toDateString())
            ->first();

        if (! $slot) {
            return back()
                ->withErrors(['slot_id' => 'The selected appointment slot is not available for this service and office.'])
                ->withInput();
        }

        $remaining = (int) $slot->capacity - (int) $slot->booked_appointments_count;

        if ($remaining <= 0) {
            return back()
                ->withErrors(['slot_id' => 'The selected appointment slot is full.'])
                ->withInput();
        }

        $wizard['slot_id'] = $slot->id;
        session()->put($this->sessionKey, $wizard);

        return redirect()->route('citizen.service-requests.payment');
    }

    public function payment()
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with('office')->findOrFail($wizard['service_id']);

        if ((float) $service->price <= 0) {
            $wizard['payment_method'] = null;
            session()->put($this->sessionKey, $wizard);

            return redirect()->route('citizen.service-requests.review');
        }

        return view('citizen.service-requests.payment', compact('service', 'wizard'));
    }

    public function storePayment(Request $request)
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::findOrFail($wizard['service_id']);

        if ((float) $service->price <= 0) {
            $wizard['payment_method'] = null;
            session()->put($this->sessionKey, $wizard);

            return redirect()->route('citizen.service-requests.review');
        }

        $request->validate([
            'payment_method' => ['required', 'in:card,cash,crypto'],
        ]);

        if ($request->payment_method === 'card' && ! $service->supports_online_payment) {
            return back()->withErrors([
                'payment_method' => 'Card payment is not supported for this service.',
            ]);
        }

        if ($request->payment_method === 'crypto' && ! $service->supports_crypto_payment) {
            return back()->withErrors([
                'payment_method' => 'Crypto payment is not supported for this service.',
            ]);
        }

        $wizard['payment_method'] = $request->payment_method;
        session()->put($this->sessionKey, $wizard);

        return redirect()->route('citizen.service-requests.review');
    }

    public function review()
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with(['office.municipality', 'category', 'documents'])
            ->findOrFail($wizard['service_id']);

        $slot = null;

        if (! empty($wizard['slot_id'])) {
            $slot = AppointmentSlot::withCount([
                    'appointments as booked_appointments_count' => function ($query) {
                        $query->whereIn('status', ['scheduled', 'completed']);
                    },
                ])
                ->find($wizard['slot_id']);
        }

        return view('citizen.service-requests.review', compact('service', 'wizard', 'slot'));
    }

    public function submit()
{
    $wizard = $this->getWizardOrRedirect();

    if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
        return $wizard;
    }

    $service = Service::with(['documents', 'office.staff'])->findOrFail($wizard['service_id']);

    foreach ($service->documents->where('is_required', true) as $document) {
        if (empty($wizard['documents'][$document->id])) {
            return redirect()
                ->route('citizen.service-requests.documents')
                ->withErrors(['documents' => 'Please upload all required documents before submitting.']);
        }
    }

    if ($service->requires_appointment && empty($wizard['slot_id'])) {
        return redirect()
            ->route('citizen.service-requests.appointment')
            ->withErrors(['slot_id' => 'Please select an appointment before submitting.']);
    }

    try {
        $serviceRequest = DB::transaction(function () use ($wizard, $service) {
            $requestNumber = $this->generateRequestNumber();
            $qrCode = 'QR-' . $requestNumber . '-' . Str::upper(Str::random(8));

            $selectedSlot = null;
            $targetOfficeId = $service->office_id;

            if ($service->requires_appointment && ! empty($wizard['slot_id'])) {
                $selectedSlot = AppointmentSlot::findOrFail($wizard['slot_id']);

                /*
                 * IMPORTANT FIX:
                 * If the citizen selected an appointment slot, save the request
                 * under the same office as the slot.
                 * This makes it appear in the correct office dashboard.
                 */
                $targetOfficeId = $selectedSlot->office_id;
            }

            $serviceRequest = ServiceRequest::create([
                'request_number' => $requestNumber,
                'citizen_user_id' => Auth::id(),
                'office_id' => $targetOfficeId,
                'service_id' => $service->id,
                'assigned_to_user_id' => null,
                'status' => 'pending',
                'description' => $wizard['description'],
                'qr_code' => $qrCode,
                'submitted_at' => now(),
            ]);

            app(RequestQrCodeService::class)->generate($serviceRequest);

            RequestStatusHistory::create([
                'request_id' => $serviceRequest->id,
                'old_status' => 'pending',
                'new_status' => 'pending',
                'changed_by_user_id' => Auth::id(),
                'note' => 'Request submitted by citizen.',
                'changed_at' => now(),
            ]);

            foreach (($wizard['documents'] ?? []) as $documentData) {
                RequestDocument::create([
                    'request_id' => $serviceRequest->id,
                    'required_document_id' => $documentData['required_document_id'],
                    'uploaded_by_user_id' => Auth::id(),
                    'file_name' => $documentData['file_name'],
                    'file_path' => $documentData['file_path'],
                    'file_type' => $documentData['file_type'],
                    'document_role' => 'citizen_upload',
                    'uploaded_at' => now(),
                ]);
            }

            if ($service->requires_appointment && ! empty($wizard['slot_id'])) {
                $bookedStatuses = ['scheduled', 'completed'];

                $matchingServiceIds = Service::where('name', $service->name)
                    ->pluck('id')
                    ->push($service->id)
                    ->unique()
                    ->values();

                $slot = AppointmentSlot::withCount([
                        'appointments as booked_appointments_count' => function ($query) use ($bookedStatuses) {
                            $query->whereIn('status', $bookedStatuses);
                        },
                    ])
                    ->where('id', $wizard['slot_id'])
                    ->whereIn('service_id', $matchingServiceIds)
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->firstOrFail();

                $remaining = (int) $slot->capacity - (int) $slot->booked_appointments_count;

                if ($remaining <= 0) {
                    throw new \Exception('The selected appointment slot is full.');
                }

                Appointment::create([
                    'request_id' => $serviceRequest->id,
                    'citizen_user_id' => Auth::id(),
                    'office_id' => $slot->office_id,
                    'slot_id' => $slot->id,
                    'status' => 'scheduled',
                    'notes' => 'Appointment selected during request submission.',
                ]);

                $bookedAfterCreate = $slot->appointments()
                    ->whereIn('status', $bookedStatuses)
                    ->count();

                if ($bookedAfterCreate >= (int) $slot->capacity) {
                    $slot->status = 'full';
                    $slot->save();
                }
            }

            if ((float) $service->price > 0) {
                $paymentMethod = $wizard['payment_method'] ?? 'cash';

                Payment::create([
                    'request_id' => $serviceRequest->id,
                    'user_id' => Auth::id(),
                    'amount' => $service->price,
                    'currency' => 'USD',
                    'payment_method' => $paymentMethod,
                    'provider' => $paymentMethod === 'cash' ? null : 'mock',
                    'status' => $paymentMethod === 'cash' ? 'pending' : 'success',
                    'transaction_reference' => $paymentMethod === 'cash' ? null : 'MOCK-' . Str::upper(Str::random(10)),
                    'paid_at' => $paymentMethod === 'cash' ? null : now(),
                ]);
            }

            Notification::create([
                'user_id' => Auth::id(),
                'type' => 'request_submitted',
                'title' => 'Request submitted successfully',
                'message' => 'Your request ' . $requestNumber . ' was submitted successfully.',
                'channel' => 'system',
                'is_read' => false,
            ]);

            $staffUsers = OfficeStaff::where('office_id', $targetOfficeId)
                ->where('status', 'active')
                ->get();

            foreach ($staffUsers as $staff) {
                Notification::create([
                    'user_id' => $staff->user_id,
                    'type' => 'new_request',
                    'title' => 'New service request',
                    'message' => 'A new request ' . $requestNumber . ' was submitted for ' . $service->name . '.',
                    'channel' => 'system',
                    'is_read' => false,
                ]);
            }

            return $serviceRequest;
        });
    } catch (\Exception $exception) {
        return redirect()
            ->route('citizen.service-requests.appointment')
            ->withErrors(['slot_id' => $exception->getMessage()]);
    }

    session()->forget($this->sessionKey);

    return redirect()
        ->route('citizen.requests.show', $serviceRequest->id)
        ->with('success', 'Request submitted successfully.');
}

    public function cancelAppointment(Appointment $appointment)
    {
        if ($appointment->citizen_user_id !== Auth::id()) {
            abort(403);
        }

        if ($appointment->status === 'cancelled') {
            return back()->withErrors([
                'appointment' => 'This appointment is already cancelled.',
            ]);
        }

        $slot = $appointment->slot;

        if (! $slot) {
            return back()->withErrors([
                'appointment' => 'Appointment slot was not found.',
            ]);
        }

        $appointmentDateTime = Carbon::parse($slot->slot_date . ' ' . $slot->start_time);

        if (now()->diffInHours($appointmentDateTime, false) < 24) {
            return back()->withErrors([
                'appointment' => 'You can only cancel appointments at least 24 hours before the appointment time.',
            ]);
        }

        DB::transaction(function () use ($appointment, $slot) {
            $appointment->update([
                'status' => 'cancelled',
                'notes' => trim(($appointment->notes ?? '') . "\nCancelled by citizen."),
            ]);

            if ($slot->status === 'full') {
                $slot->status = 'available';
                $slot->save();
            }

            Notification::create([
                'user_id' => Auth::id(),
                'type' => 'appointment_cancelled',
                'title' => 'Appointment cancelled',
                'message' => 'Your appointment was cancelled successfully.',
                'channel' => 'system',
                'is_read' => false,
            ]);
        });

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    public function cancel()
    {
        $wizard = session($this->sessionKey, []);

        foreach (($wizard['documents'] ?? []) as $documentData) {
            if (! empty($documentData['file_path'])) {
                Storage::disk('public')->delete($documentData['file_path']);
            }
        }

        session()->forget($this->sessionKey);

        return redirect()
            ->route('discovery.index')
            ->with('success', 'Request submission cancelled.');
    }

    private function getWizardOrRedirect()
    {
        $wizard = session($this->sessionKey);

        if (! $wizard || empty($wizard['service_id'])) {
            return redirect()
                ->route('discovery.index')
                ->withErrors(['request' => 'Please choose a service before starting a request.']);
        }

        return $wizard;
    }

    private function generateRequestNumber(): string
    {
        do {
            $number = 'REQ-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (ServiceRequest::where('request_number', $number)->exists());

        return $number;
    }
}