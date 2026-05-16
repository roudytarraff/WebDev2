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
use App\Services\RequestQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CitizenServiceRequestController extends Controller
{
    private string $sessionKey = 'citizen_service_request_wizard';

    public function start(Service $service)
    {
        $service->load(['office.municipality', 'category', 'documents']);

        if ($service->status !== 'active') {
            return redirect()
                ->route('discovery.services.show', $service->id)
                ->withErrors([
                    'service' => 'This service is not available right now.',
                ]);
        }

        session()->put($this->sessionKey, [
            'service_id' => $service->id,
            'description' => null,
            'documents' => [],
            'slot_id' => null,
            'payment_method' => null,
        ]);

        return view('citizen.service-requests.start', [
            'service' => $service,
        ]);
    }

    public function storeDetails(Request $request, Service $service)
    {
        $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $wizard = session($this->sessionKey, []);

        $wizard['service_id'] = $service->id;
        $wizard['description'] = trim($request->input('description'));
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

        $service = Service::with(['office', 'documents'])
            ->findOrFail($wizard['service_id']);

        return view('citizen.service-requests.documents', [
            'service' => $service,
            'wizard' => $wizard,
        ]);
    }

    public function storeDocuments(Request $request)
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with('documents')->findOrFail($wizard['service_id']);

        $rules = $this->buildDocumentValidationRules($service, $wizard);

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

        $matchingServiceIds = $this->getMatchingServiceIds($service);

        $allAvailableSlots = AppointmentSlot::withCount([
                'appointments as booked_appointments_count' => function ($query) {
                    $query->whereIn('status', ['scheduled', 'completed']);
                },
            ])
            ->whereIn('service_id', $matchingServiceIds)
            ->where('office_id', $service->office_id)
            ->where('status', 'available')
            ->whereDate('slot_date', '>=', now()->toDateString())
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        $allAvailableSlots = $allAvailableSlots->filter(function ($slot) {
            return $this->getRemainingCapacity($slot) > 0;
        })->values();

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

        $slots = $allAvailableSlots->filter(function ($slot) use ($selectedDate) {
            return $slot->slot_date == $selectedDate;
        })->values();

        return view('citizen.service-requests.appointment', [
            'service' => $service,
            'slots' => $slots,
            'wizard' => $wizard,
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
        ]);
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

        $slot = $this->findAvailableSlot($service, $request->input('slot_id'));

        if (! $slot) {
            return back()
                ->withErrors([
                    'slot_id' => 'The selected appointment slot is not available for this service and office.',
                ])
                ->withInput();
        }

        if ($this->getRemainingCapacity($slot) <= 0) {
            return back()
                ->withErrors([
                    'slot_id' => 'The selected appointment slot is full.',
                ])
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

        return view('citizen.service-requests.payment', [
            'service' => $service,
            'wizard' => $wizard,
        ]);
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

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'card' && ! $service->supports_online_payment) {
            return back()->withErrors([
                'payment_method' => 'Card payment is not supported for this service.',
            ]);
        }

        if ($paymentMethod === 'crypto' && ! $service->supports_crypto_payment) {
            return back()->withErrors([
                'payment_method' => 'Crypto payment is not supported for this service.',
            ]);
        }

        $wizard['payment_method'] = $paymentMethod;

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

        return view('citizen.service-requests.review', [
            'service' => $service,
            'wizard' => $wizard,
            'slot' => $slot,
        ]);
    }

    public function submit()
    {
        $wizard = $this->getWizardOrRedirect();

        if ($wizard instanceof \Illuminate\Http\RedirectResponse) {
            return $wizard;
        }

        $service = Service::with('documents')->findOrFail($wizard['service_id']);

        $validationRedirect = $this->validateBeforeSubmit($wizard, $service);

        if ($validationRedirect) {
            return $validationRedirect;
        }

        try {
            $result = DB::transaction(function () use ($wizard, $service) {
                $requestNumber = $this->generateRequestNumber();
                $qrCode = 'QR-' . $requestNumber . '-' . Str::upper(Str::random(8));

                $targetOfficeId = $service->office_id;

                if ($service->requires_appointment && ! empty($wizard['slot_id'])) {
                    $selectedSlot = AppointmentSlot::findOrFail($wizard['slot_id']);
                    $targetOfficeId = $selectedSlot->office_id;
                }

                $serviceRequest = $this->createServiceRequest(
                    $service,
                    $wizard,
                    $requestNumber,
                    $qrCode,
                    $targetOfficeId
                );

                app(RequestQrCodeService::class)->generate($serviceRequest);

                $this->createInitialStatusHistory($serviceRequest);
                $this->saveUploadedDocuments($serviceRequest, $wizard);

                if ($service->requires_appointment && ! empty($wizard['slot_id'])) {
                    $this->bookAppointment($serviceRequest, $service, $wizard['slot_id']);
                }

                $payment = $this->createPaymentIfNeeded($serviceRequest, $service, $wizard);
                $this->notifyCitizenAndStaff($serviceRequest, $service, $targetOfficeId);

                return [
                    'serviceRequest' => $serviceRequest,
                    'payment' => $payment,
                ];
            });
        } catch (\Exception $exception) {
            return redirect()
                ->route('citizen.service-requests.appointment')
                ->withErrors([
                    'slot_id' => $exception->getMessage(),
                ]);
        }

        session()->forget($this->sessionKey);

        $payment = $result['payment'];
        $serviceRequest = $result['serviceRequest'];

        if ($payment && $payment->payment_method === 'card') {
            return redirect()->route('citizen.payments.stripe.checkout', $payment);
        }

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
                $slot->update([
                    'status' => 'available',
                ]);
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

    private function buildDocumentValidationRules(Service $service, array $wizard): array
    {
        $rules = [];

        foreach ($service->documents as $document) {
            $fieldName = 'documents.' . $document->id;

            $isAlreadyUploaded = ! empty($wizard['documents'][$document->id]);

            if ($document->is_required && ! $isAlreadyUploaded) {
                $requiredRule = 'required';
            } else {
                $requiredRule = 'nullable';
            }

            $rules[$fieldName] = [
                $requiredRule,
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ];
        }

        return $rules;
    }

    private function getMatchingServiceIds(Service $service)
    {
        return Service::where('name', $service->name)
            ->where('office_id', $service->office_id)
            ->pluck('id')
            ->push($service->id)
            ->unique()
            ->values();
    }

    private function findAvailableSlot(Service $service, int $slotId)
    {
        $matchingServiceIds = $this->getMatchingServiceIds($service);

        return AppointmentSlot::withCount([
                'appointments as booked_appointments_count' => function ($query) {
                    $query->whereIn('status', ['scheduled', 'completed']);
                },
            ])
            ->where('id', $slotId)
            ->whereIn('service_id', $matchingServiceIds)
            ->where('office_id', $service->office_id)
            ->where('status', 'available')
            ->whereDate('slot_date', '>=', now()->toDateString())
            ->first();
    }

    private function getRemainingCapacity(AppointmentSlot $slot): int
    {
        return (int) $slot->capacity - (int) $slot->booked_appointments_count;
    }

    private function validateBeforeSubmit(array $wizard, Service $service)
    {
        foreach ($service->documents->where('is_required', true) as $document) {
            if (empty($wizard['documents'][$document->id])) {
                return redirect()
                    ->route('citizen.service-requests.documents')
                    ->withErrors([
                        'documents' => 'Please upload all required documents before submitting.',
                    ]);
            }
        }

        if ($service->requires_appointment && empty($wizard['slot_id'])) {
            return redirect()
                ->route('citizen.service-requests.appointment')
                ->withErrors([
                    'slot_id' => 'Please select an appointment before submitting.',
                ]);
        }

        return null;
    }

    private function createServiceRequest(
        Service $service,
        array $wizard,
        string $requestNumber,
        string $qrCode,
        int $targetOfficeId
    ): ServiceRequest {
        return ServiceRequest::create([
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
    }

    private function createInitialStatusHistory(ServiceRequest $serviceRequest): void
    {
        RequestStatusHistory::create([
            'request_id' => $serviceRequest->id,
            'old_status' => 'pending',
            'new_status' => 'pending',
            'changed_by_user_id' => Auth::id(),
            'note' => 'Request submitted by citizen.',
            'changed_at' => now(),
        ]);
    }

    private function saveUploadedDocuments(ServiceRequest $serviceRequest, array $wizard): void
    {
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
    }

    private function bookAppointment(ServiceRequest $serviceRequest, Service $service, int $slotId): void
    {
        $slot = $this->findAvailableSlotForBooking($service, $slotId);

        if (! $slot) {
            throw new \Exception('The selected appointment slot is no longer available.');
        }

        if ($this->getRemainingCapacity($slot) <= 0) {
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

        $bookedCount = $slot->appointments()
            ->whereIn('status', ['scheduled', 'completed'])
            ->count();

        if ($bookedCount >= (int) $slot->capacity) {
            $slot->update([
                'status' => 'full',
            ]);
        }
    }

    private function findAvailableSlotForBooking(Service $service, int $slotId)
    {
        $matchingServiceIds = $this->getMatchingServiceIds($service);

        return AppointmentSlot::withCount([
                'appointments as booked_appointments_count' => function ($query) {
                    $query->whereIn('status', ['scheduled', 'completed']);
                },
            ])
            ->where('id', $slotId)
            ->whereIn('service_id', $matchingServiceIds)
            ->where('office_id', $service->office_id)
            ->where('status', 'available')
            ->lockForUpdate()
            ->first();
    }

    private function createPaymentIfNeeded(ServiceRequest $serviceRequest, Service $service, array $wizard): ?Payment
    {
        if ((float) $service->price <= 0) {
            return null;
        }

        $paymentMethod = $wizard['payment_method'] ?? 'cash';

        return Payment::create([
            'request_id' => $serviceRequest->id,
            'user_id' => Auth::id(),
            'amount' => $service->price,
            'currency' => strtoupper(config('services.stripe.currency', 'usd')),
            'payment_method' => $paymentMethod,
            'provider' => $paymentMethod === 'card' ? 'stripe' : null,
            'status' => 'pending',
            'transaction_reference' => null,
            'paid_at' => null,
        ]);
    }

    private function notifyCitizenAndStaff(ServiceRequest $serviceRequest, Service $service, int $targetOfficeId): void
    {
        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'request_submitted',
            'title' => 'Request submitted successfully',
            'message' => 'Your request ' . $serviceRequest->request_number . ' was submitted successfully.',
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
                'message' => 'A new request ' . $serviceRequest->request_number . ' was submitted for ' . $service->name . '.',
                'channel' => 'system',
                'is_read' => false,
            ]);
        }
    }

    private function getWizardOrRedirect()
    {
        $wizard = session($this->sessionKey);

        if (! $wizard || empty($wizard['service_id'])) {
            return redirect()
                ->route('discovery.index')
                ->withErrors([
                    'request' => 'Please choose a service before starting a request.',
                ]);
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