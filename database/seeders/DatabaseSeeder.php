<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\CitizenProfile;
use App\Models\DocumentType;
use App\Models\Feedback;
use App\Models\GeneratedDocument;
use App\Models\Municipality;
use App\Models\Notification;
use App\Models\Office;
use App\Models\OfficeStaff;
use App\Models\OfficeWorkingHour;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\RequestDocument;
use App\Models\RequestStatusHistory;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\ServiceRequiredDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $citizenRole = Role::firstOrCreate(['name' => 'citizen']);

        $admin = User::updateOrCreate(['email' => 'admin@eservices.test'], [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '70000001',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $staff = User::updateOrCreate(['email' => 'staff@eservices.test'], [
            'first_name' => 'Maya',
            'last_name' => 'Haddad',
            'phone' => '70000002',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $staff->roles()->syncWithoutDetaching([$staffRole->id]);

        $oldDemoStaff = User::updateOrCreate(['email' => 'staff@example.com'], [
            'first_name' => 'Office',
            'last_name' => 'Staff',
            'phone' => '70000005',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $oldDemoStaff->roles()->syncWithoutDetaching([$staffRole->id]);

        $staffTwo = User::updateOrCreate(['email' => 'clerk@eservices.test'], [
            'first_name' => 'Karim',
            'last_name' => 'Nasser',
            'phone' => '70000004',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $staffTwo->roles()->syncWithoutDetaching([$staffRole->id]);

        $citizen = User::updateOrCreate(['email' => 'citizen@eservices.test'], [
            'first_name' => 'Rami',
            'last_name' => 'Mansour',
            'phone' => '70000003',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $citizen->roles()->syncWithoutDetaching([$citizenRole->id]);

        $municipalityAddress = Address::updateOrCreate(['address_line_1' => 'Beirut Municipality Building'], [
            'address_line_2' => null,
            'city' => 'Beirut',
            'region' => 'Beirut',
            'postal_code' => '1100',
            'country' => 'Lebanon',
            'latitude' => 33.8938000,
            'longitude' => 35.5018000,
        ]);

        $municipality = Municipality::updateOrCreate(['name' => 'Beirut Municipality'], [
            'region' => 'Beirut',
            'address_id' => $municipalityAddress->id,
            'status' => 'active',
        ]);

        $civilAddress = Address::updateOrCreate(['address_line_1' => 'Civil Registry Office - Downtown'], [
            'address_line_2' => 'Martyrs Square',
            'city' => 'Beirut',
            'region' => 'Beirut',
            'postal_code' => '1100',
            'country' => 'Lebanon',
            'latitude' => 33.8953000,
            'longitude' => 35.5076000,
        ]);

        $permitsAddress = Address::updateOrCreate(['address_line_1' => 'Municipal Permits Office'], [
            'address_line_2' => 'Hamra Street',
            'city' => 'Beirut',
            'region' => 'Beirut',
            'postal_code' => '1103',
            'country' => 'Lebanon',
            'latitude' => 33.8969000,
            'longitude' => 35.4823000,
        ]);

        $civilOffice = Office::updateOrCreate(['name' => 'Civil Registry Office'], [
            'municipality_id' => $municipality->id,
            'address_id' => $civilAddress->id,
            'contact_email' => 'civil@beirut.gov.lb',
            'contact_phone' => '01-123456',
            'status' => 'active',
        ]);

        $permitsOffice = Office::updateOrCreate(['name' => 'Permits Office'], [
            'municipality_id' => $municipality->id,
            'address_id' => $permitsAddress->id,
            'contact_email' => 'permits@beirut.gov.lb',
            'contact_phone' => '01-654321',
            'status' => 'active',
        ]);

        OfficeStaff::updateOrCreate(['office_id' => $civilOffice->id, 'user_id' => $staff->id], [
            'job_title' => 'Office Manager',
            'status' => 'active',
        ]);

        OfficeStaff::updateOrCreate(['office_id' => $civilOffice->id, 'user_id' => $oldDemoStaff->id], [
            'job_title' => 'Office Staff',
            'status' => 'active',
        ]);

        OfficeStaff::updateOrCreate(['office_id' => $civilOffice->id, 'user_id' => $staffTwo->id], [
            'job_title' => 'Front Desk Clerk',
            'status' => 'active',
        ]);

        foreach ([1, 2, 3, 4, 5] as $day) {
            OfficeWorkingHour::updateOrCreate(['office_id' => $civilOffice->id, 'weekday_number' => $day], [
                'open_time' => '08:00',
                'close_time' => '15:00',
                'is_closed' => false,
            ]);
        }

        $civilCategory = ServiceCategory::updateOrCreate(['office_id' => $civilOffice->id, 'name' => 'Civil Documents'], [
            'description' => 'Certificates and civil status documents.',
        ]);

        $permitCategory = ServiceCategory::updateOrCreate(['office_id' => $permitsOffice->id, 'name' => 'Permits'], [
            'description' => 'Construction and occupancy permits.',
        ]);

        $birthCertificate = Service::updateOrCreate(['office_id' => $civilOffice->id, 'name' => 'Birth Certificate'], [
            'category_id' => $civilCategory->id,
            'description' => 'Official birth certificate request.',
            'instructions' => 'Upload your national ID and family record extract.',
            'price' => 10,
            'duration_minutes' => 20,
            'requires_appointment' => false,
            'supports_online_payment' => true,
            'supports_crypto_payment' => true,
            'status' => 'active',
        ]);

        Service::updateOrCreate(['office_id' => $permitsOffice->id, 'name' => 'Building Permit'], [
            'category_id' => $permitCategory->id,
            'description' => 'Building permit application review.',
            'instructions' => 'Upload ownership documents and engineering plans.',
            'price' => 150,
            'duration_minutes' => 45,
            'requires_appointment' => true,
            'supports_online_payment' => true,
            'supports_crypto_payment' => false,
            'status' => 'active',
        ]);

        $idDocument = ServiceRequiredDocument::updateOrCreate(['service_id' => $birthCertificate->id, 'document_name' => 'National ID'], [
            'is_required' => true,
        ]);

        ServiceRequiredDocument::updateOrCreate(['service_id' => $birthCertificate->id, 'document_name' => 'Family Record Extract'], [
            'is_required' => true,
        ]);

        $serviceRequest = ServiceRequest::updateOrCreate(['request_number' => 'REQ-2026-0001'], [
            'citizen_user_id' => $citizen->id,
            'office_id' => $civilOffice->id,
            'service_id' => $birthCertificate->id,
            'assigned_to_user_id' => $staff->id,
            'status' => 'in_progress',
            'description' => 'Citizen requested a birth certificate for university paperwork.',
            'qr_code' => 'QR-REQ-2026-0001',
            'submitted_at' => now()->subDays(2),
        ]);

        RequestStatusHistory::updateOrCreate(['request_id' => $serviceRequest->id, 'new_status' => 'in_progress'], [
            'old_status' => 'pending',
            'changed_by_user_id' => $staff->id,
            'note' => 'Request reviewed and assigned to office manager.',
            'changed_at' => now()->subDay(),
        ]);

        RequestDocument::updateOrCreate(['request_id' => $serviceRequest->id, 'required_document_id' => $idDocument->id], [
            'uploaded_by_user_id' => $citizen->id,
            'file_name' => 'national-id.pdf',
            'file_path' => 'seed/national-id.pdf',
            'file_type' => 'pdf',
            'document_role' => 'citizen_upload',
            'uploaded_at' => now()->subDays(2),
        ]);
        Storage::disk('public')->put('seed/national-id.pdf', 'Demo national ID file for seeded request ' . $serviceRequest->request_number);

        $payment = Payment::updateOrCreate(['transaction_reference' => 'CARD-2026-0001'], [
            'request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'amount' => 10,
            'currency' => 'USD',
            'payment_method' => 'card',
            'provider' => 'DemoPay',
            'status' => 'success',
            'paid_at' => now()->subDays(2),
        ]);

        PaymentTransaction::updateOrCreate(['payment_id' => $payment->id, 'provider_reference' => 'DP-778899'], [
            'transaction_type' => 'charge',
            'tx_hash' => null,
            'status' => 'success',
            'processed_at' => now()->subDays(2),
        ]);

        $slot = AppointmentSlot::updateOrCreate(['office_id' => $civilOffice->id, 'service_id' => $birthCertificate->id, 'slot_date' => now()->addDays(2)->toDateString(), 'start_time' => '09:00'], [
            'end_time' => '09:30',
            'capacity' => 4,
            'status' => 'available',
        ]);

        Appointment::updateOrCreate(['request_id' => $serviceRequest->id, 'slot_id' => $slot->id], [
            'citizen_user_id' => $citizen->id,
            'office_id' => $civilOffice->id,
            'status' => 'scheduled',
            'notes' => 'Bring original ID for verification.',
        ]);

        $chat = Chat::updateOrCreate(['request_id' => $serviceRequest->id], [
            'citizen_user_id' => $citizen->id,
            'office_id' => $civilOffice->id,
            'status' => 'open',
        ]);

        ChatMessage::updateOrCreate(['chat_id' => $chat->id, 'sender_user_id' => $citizen->id, 'message_text' => 'Hello, when can I receive the certificate?'], [
            'attachment_path' => null,
            'sent_at' => now()->subDay(),
        ]);

        Feedback::updateOrCreate(['request_id' => $serviceRequest->id], [
            'citizen_user_id' => $citizen->id,
            'office_id' => $civilOffice->id,
            'rating' => 4,
            'comment' => 'The online request was easy to submit.',
            'office_reply' => null,
        ]);

        Notification::updateOrCreate(['user_id' => $staff->id, 'title' => 'New request assigned'], [
            'type' => 'request_assignment',
            'message' => 'Request REQ-2026-0001 has been assigned to you.',
            'channel' => 'system',
            'is_read' => false,
        ]);

        $extraCitizens = [
            ['first_name' => 'Lara', 'last_name' => 'Khoury', 'email' => 'lara.khoury@eservices.test', 'phone' => '70000006'],
            ['first_name' => 'Omar', 'last_name' => 'Sabbagh', 'email' => 'omar.sabbagh@eservices.test', 'phone' => '70000007'],
            ['first_name' => 'Nour', 'last_name' => 'Aoun', 'email' => 'nour.aoun@eservices.test', 'phone' => '70000008'],
            ['first_name' => 'Jad', 'last_name' => 'Salameh', 'email' => 'jad.salameh@eservices.test', 'phone' => '70000009'],
        ];

        $citizens = collect([$citizen]);
        foreach ($extraCitizens as $citizenData) {
            $demoCitizen = User::updateOrCreate(['email' => $citizenData['email']], [
                'first_name' => $citizenData['first_name'],
                'last_name' => $citizenData['last_name'],
                'phone' => $citizenData['phone'],
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);
            $demoCitizen->roles()->syncWithoutDetaching([$citizenRole->id]);
            $citizens->push($demoCitizen);
        }

        $extraStaffUsers = [
            ['first_name' => 'Dana', 'last_name' => 'Farah', 'email' => 'dana.farah@eservices.test', 'phone' => '70000010', 'job_title' => 'Permits Supervisor', 'office' => $permitsOffice],
            ['first_name' => 'Elias', 'last_name' => 'Maalouf', 'email' => 'elias.maalouf@eservices.test', 'phone' => '70000011', 'job_title' => 'Inspection Coordinator', 'office' => $permitsOffice],
        ];

        foreach ($extraStaffUsers as $staffData) {
            $demoStaff = User::updateOrCreate(['email' => $staffData['email']], [
                'first_name' => $staffData['first_name'],
                'last_name' => $staffData['last_name'],
                'phone' => $staffData['phone'],
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);
            $demoStaff->roles()->syncWithoutDetaching([$staffRole->id]);

            OfficeStaff::updateOrCreate(['office_id' => $staffData['office']->id, 'user_id' => $demoStaff->id], [
                'job_title' => $staffData['job_title'],
                'status' => 'active',
            ]);
        }

        $extraMunicipalities = [
            [
                'name' => 'Jounieh Municipality',
                'region' => 'Keserwan',
                'address' => ['address_line_1' => 'Jounieh Municipality Building', 'city' => 'Jounieh', 'region' => 'Keserwan', 'postal_code' => '1200', 'latitude' => 33.9808000, 'longitude' => 35.6178000],
                'offices' => [
                    ['name' => 'Jounieh Public Works Office', 'email' => 'works@jounieh.gov.lb', 'phone' => '09-930001', 'address_line_1' => 'Public Works Office - Jounieh', 'latitude' => 33.9821000, 'longitude' => 35.6191000],
                    ['name' => 'Jounieh Citizen Service Office', 'email' => 'citizens@jounieh.gov.lb', 'phone' => '09-930002', 'address_line_1' => 'Citizen Service Center - Jounieh', 'latitude' => 33.9796000, 'longitude' => 35.6162000],
                ],
            ],
            [
                'name' => 'Tripoli Municipality',
                'region' => 'North Lebanon',
                'address' => ['address_line_1' => 'Tripoli Municipality Building', 'city' => 'Tripoli', 'region' => 'North Lebanon', 'postal_code' => '1300', 'latitude' => 34.4367000, 'longitude' => 35.8497000],
                'offices' => [
                    ['name' => 'Tripoli Sanitation Office', 'email' => 'sanitation@tripoli.gov.lb', 'phone' => '06-440001', 'address_line_1' => 'Sanitation Office - Tripoli', 'latitude' => 34.4380000, 'longitude' => 35.8469000],
                    ['name' => 'Tripoli Tax Office', 'email' => 'tax@tripoli.gov.lb', 'phone' => '06-440002', 'address_line_1' => 'Municipal Tax Office - Tripoli', 'latitude' => 34.4349000, 'longitude' => 35.8512000],
                ],
            ],
        ];

        $allOffices = collect([$civilOffice, $permitsOffice]);
        foreach ($extraMunicipalities as $municipalityData) {
            $address = Address::updateOrCreate(['address_line_1' => $municipalityData['address']['address_line_1']], [
                'address_line_2' => null,
                'city' => $municipalityData['address']['city'],
                'region' => $municipalityData['address']['region'],
                'postal_code' => $municipalityData['address']['postal_code'],
                'country' => 'Lebanon',
                'latitude' => $municipalityData['address']['latitude'],
                'longitude' => $municipalityData['address']['longitude'],
            ]);

            $demoMunicipality = Municipality::updateOrCreate(['name' => $municipalityData['name']], [
                'region' => $municipalityData['region'],
                'address_id' => $address->id,
                'status' => 'active',
            ]);

            foreach ($municipalityData['offices'] as $officeData) {
                $officeAddress = Address::updateOrCreate(['address_line_1' => $officeData['address_line_1']], [
                    'address_line_2' => null,
                    'city' => $municipalityData['address']['city'],
                    'region' => $municipalityData['address']['region'],
                    'postal_code' => $municipalityData['address']['postal_code'],
                    'country' => 'Lebanon',
                    'latitude' => $officeData['latitude'],
                    'longitude' => $officeData['longitude'],
                ]);

                $demoOffice = Office::updateOrCreate(['name' => $officeData['name']], [
                    'municipality_id' => $demoMunicipality->id,
                    'address_id' => $officeAddress->id,
                    'contact_email' => $officeData['email'],
                    'contact_phone' => $officeData['phone'],
                    'status' => 'active',
                ]);

                $allOffices->push($demoOffice);

                foreach ([1, 2, 3, 4, 5] as $day) {
                    OfficeWorkingHour::updateOrCreate(['office_id' => $demoOffice->id, 'weekday_number' => $day], [
                        'open_time' => '08:30',
                        'close_time' => '14:30',
                        'is_closed' => false,
                    ]);
                }
            }
        }

        $serviceTemplates = [
            ['category' => 'Civil Documents', 'name' => 'Marriage Certificate', 'price' => 12, 'appointment' => false, 'documents' => ['National ID', 'Family Record Extract', 'Marriage Record Number']],
            ['category' => 'Civil Documents', 'name' => 'Family Record Extract', 'price' => 8, 'appointment' => false, 'documents' => ['National ID', 'Mukhtar Statement']],
            ['category' => 'Permits', 'name' => 'Renovation Permit', 'price' => 75, 'appointment' => true, 'documents' => ['Property Deed', 'Engineer Report', 'Neighbor Consent']],
            ['category' => 'Public Works', 'name' => 'Road Damage Report', 'price' => 0, 'appointment' => false, 'documents' => ['Location Photo', 'Citizen ID']],
            ['category' => 'Municipal Taxes', 'name' => 'Municipal Tax Clearance', 'price' => 20, 'appointment' => false, 'documents' => ['Property Number', 'National ID', 'Previous Receipt']],
            ['category' => 'Sanitation', 'name' => 'Waste Collection Complaint', 'price' => 0, 'appointment' => false, 'documents' => ['Location Photo']],
        ];

        $documentTypeNames = collect($serviceTemplates)
            ->pluck('documents')
            ->flatten()
            ->merge(['National ID', 'Family Record Extract'])
            ->unique()
            ->values();

        foreach ($allOffices as $office) {
            foreach ($documentTypeNames as $documentTypeName) {
                DocumentType::updateOrCreate(['office_id' => $office->id, 'name' => $documentTypeName], [
                    'description' => 'Reusable document type for ' . $office->name . '.',
                    'status' => 'active',
                ]);
            }
        }

        foreach ($civilOffice->documentTypes as $documentType) {
            ServiceRequiredDocument::whereHas('service', function ($query) use ($civilOffice) {
                $query->where('office_id', $civilOffice->id);
            })->where('document_name', $documentType->name)->update([
                'document_type_id' => $documentType->id,
            ]);
        }

        $allServices = collect([$birthCertificate]);
        foreach ($allOffices as $index => $office) {
            foreach ($serviceTemplates as $templateIndex => $template) {
                if ($index % 2 !== $templateIndex % 2 && $office->id !== $civilOffice->id) {
                    continue;
                }

                $category = ServiceCategory::updateOrCreate(['office_id' => $office->id, 'name' => $template['category']], [
                    'description' => $template['category'] . ' services handled by ' . $office->name . '.',
                ]);

                $service = Service::updateOrCreate(['office_id' => $office->id, 'name' => $template['name']], [
                    'category_id' => $category->id,
                    'description' => $template['name'] . ' request submitted through the e-services platform.',
                    'instructions' => 'Fill the form, upload the required documents, and follow the request status online.',
                    'price' => $template['price'],
                    'duration_minutes' => $template['appointment'] ? 45 : 20,
                    'requires_appointment' => $template['appointment'],
                    'supports_online_payment' => $template['price'] > 0,
                    'supports_crypto_payment' => $template['price'] > 0 && $templateIndex % 2 === 0,
                    'status' => 'active',
                ]);

                foreach ($template['documents'] as $documentName) {
                    $documentType = DocumentType::where('office_id', $office->id)->where('name', $documentName)->first();

                    ServiceRequiredDocument::updateOrCreate(['service_id' => $service->id, 'document_name' => $documentName], [
                        'document_type_id' => $documentType?->id,
                        'is_required' => true,
                    ]);
                }

                $allServices->push($service);
            }
        }

        $statuses = ['pending', 'approved', 'in_progress', 'completed', 'rejected'];
        $requestCounter = 2;
        foreach ($allServices->take(14) as $service) {
            $requestNumber = 'REQ-2026-' . str_pad((string) $requestCounter, 4, '0', STR_PAD_LEFT);
            $office = $service->office;
            $demoCitizen = $citizens[$requestCounter % $citizens->count()];
            $status = $statuses[$requestCounter % count($statuses)];
            $assignedUserId = $office->staff()->where('status', 'active')->value('user_id');

            $demoRequest = ServiceRequest::updateOrCreate(['request_number' => $requestNumber], [
                'citizen_user_id' => $demoCitizen->id,
                'office_id' => $office->id,
                'service_id' => $service->id,
                'assigned_to_user_id' => $assignedUserId,
                'status' => $status,
                'description' => 'Demo request for ' . $service->name . '.',
                'qr_code' => 'QR-' . $requestNumber,
                'submitted_at' => now()->subDays($requestCounter),
            ]);

            RequestStatusHistory::updateOrCreate(['request_id' => $demoRequest->id, 'new_status' => $status], [
                'old_status' => 'pending',
                'changed_by_user_id' => $assignedUserId ?: $admin->id,
                'note' => 'Demo status history for dashboard testing.',
                'changed_at' => now()->subDays(max(1, $requestCounter - 1)),
            ]);

            $firstDocument = $service->documents()->first();
            if ($firstDocument) {
                $demoFilePath = 'seed/demo-document-' . $requestCounter . '.pdf';

                RequestDocument::updateOrCreate(['request_id' => $demoRequest->id, 'required_document_id' => $firstDocument->id], [
                    'uploaded_by_user_id' => $demoCitizen->id,
                    'file_name' => 'demo-document-' . $requestCounter . '.pdf',
                    'file_path' => $demoFilePath,
                    'file_type' => 'pdf',
                    'document_role' => 'citizen_upload',
                    'uploaded_at' => now()->subDays($requestCounter),
                ]);

                Storage::disk('public')->put($demoFilePath, 'Demo uploaded document for request ' . $requestNumber);
            }

            if ($service->price > 0) {
                $demoPayment = Payment::updateOrCreate(['transaction_reference' => 'CARD-2026-' . str_pad((string) $requestCounter, 4, '0', STR_PAD_LEFT)], [
                    'request_id' => $demoRequest->id,
                    'user_id' => $demoCitizen->id,
                    'amount' => $service->price,
                    'currency' => 'USD',
                    'payment_method' => $requestCounter % 3 === 0 ? 'crypto' : 'card',
                    'provider' => 'DemoPay',
                    'status' => $status === 'rejected' ? 'failed' : 'success',
                    'paid_at' => $status === 'rejected' ? null : now()->subDays($requestCounter),
                ]);

                PaymentTransaction::updateOrCreate(['payment_id' => $demoPayment->id, 'provider_reference' => 'DP-' . (880000 + $requestCounter)], [
                    'transaction_type' => 'charge',
                    'tx_hash' => $requestCounter % 3 === 0 ? '0xdemo' . $requestCounter : null,
                    'status' => $demoPayment->status,
                    'processed_at' => now()->subDays($requestCounter),
                ]);
            }

            if ($service->requires_appointment) {
                $demoSlot = AppointmentSlot::updateOrCreate([
                    'office_id' => $office->id,
                    'service_id' => $service->id,
                    'slot_date' => now()->addDays($requestCounter)->toDateString(),
                    'start_time' => '10:00',
                ], [
                    'end_time' => '10:45',
                    'capacity' => 5,
                    'status' => 'available',
                ]);

                Appointment::updateOrCreate(['request_id' => $demoRequest->id, 'slot_id' => $demoSlot->id], [
                    'citizen_user_id' => $demoCitizen->id,
                    'office_id' => $office->id,
                    'status' => $status === 'completed' ? 'completed' : 'scheduled',
                    'notes' => 'Demo appointment generated by the seeder.',
                ]);
            }

            $demoChat = Chat::updateOrCreate(['request_id' => $demoRequest->id], [
                'citizen_user_id' => $demoCitizen->id,
                'office_id' => $office->id,
                'status' => $status === 'completed' ? 'closed' : 'open',
            ]);

            ChatMessage::updateOrCreate(['chat_id' => $demoChat->id, 'sender_user_id' => $demoCitizen->id, 'message_text' => 'Can I get an update about ' . $service->name . '?'], [
                'attachment_path' => null,
                'sent_at' => now()->subHours($requestCounter),
            ]);

            if ($status === 'completed') {
                Feedback::updateOrCreate(['request_id' => $demoRequest->id], [
                    'citizen_user_id' => $demoCitizen->id,
                    'office_id' => $office->id,
                    'rating' => 3 + ($requestCounter % 3),
                    'comment' => 'Demo feedback for a completed request.',
                    'office_reply' => $requestCounter % 2 === 0 ? 'Thank you for your feedback.' : null,
                ]);
            }

            if ($assignedUserId) {
                Notification::updateOrCreate(['user_id' => $assignedUserId, 'title' => 'Request ' . $requestNumber], [
                    'type' => 'request_update',
                    'message' => 'A demo request is waiting in your office dashboard.',
                    'channel' => 'system',
                    'is_read' => $requestCounter % 2 === 0,
                ]);
            }

            $requestCounter++;
        }

        foreach ($citizens as $index => $demoCitizen) {
            $profileAddress = Address::updateOrCreate(['address_line_1' => 'Citizen Demo Address ' . ($index + 1)], [
                'address_line_2' => 'Floor ' . (($index % 5) + 1),
                'city' => ['Beirut', 'Jounieh', 'Tripoli', 'Saida', 'Zahle'][$index % 5],
                'region' => ['Beirut', 'Keserwan', 'North Lebanon', 'South Lebanon', 'Bekaa'][$index % 5],
                'postal_code' => '10' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'country' => 'Lebanon',
                'latitude' => 33.85 + ($index * 0.03),
                'longitude' => 35.48 + ($index * 0.025),
            ]);

            CitizenProfile::updateOrCreate(['user_id' => $demoCitizen->id], [
                'address_id' => $profileAddress->id,
                'national_id_number' => '1999' . str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                'date_of_birth' => now()->subYears(22 + $index)->toDateString(),
                'id_document_path' => 'seed/citizen-id-' . ($index + 1) . '.pdf',
                'verification_status' => ['verified', 'pending', 'verified', 'rejected', 'verified'][$index % 5],
            ]);

            Storage::disk('public')->put('seed/citizen-id-' . ($index + 1) . '.pdf', 'Demo citizen ID profile file for ' . $demoCitizen->full_name);
        }

        $moreCitizens = [
            ['first_name' => 'Mira', 'last_name' => 'Yazbek', 'email' => 'mira.yazbek@eservices.test', 'phone' => '70000012'],
            ['first_name' => 'Tarek', 'last_name' => 'Saab', 'email' => 'tarek.saab@eservices.test', 'phone' => '70000013'],
            ['first_name' => 'Salma', 'last_name' => 'Karam', 'email' => 'salma.karam@eservices.test', 'phone' => '70000014'],
            ['first_name' => 'Fadi', 'last_name' => 'Hobeika', 'email' => 'fadi.hobeika@eservices.test', 'phone' => '70000015'],
            ['first_name' => 'Rita', 'last_name' => 'Matar', 'email' => 'rita.matar@eservices.test', 'phone' => '70000016'],
            ['first_name' => 'George', 'last_name' => 'Nehme', 'email' => 'george.nehme@eservices.test', 'phone' => '70000017'],
        ];

        foreach ($moreCitizens as $index => $citizenData) {
            $demoCitizen = User::updateOrCreate(['email' => $citizenData['email']], [
                'first_name' => $citizenData['first_name'],
                'last_name' => $citizenData['last_name'],
                'phone' => $citizenData['phone'],
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);
            $demoCitizen->roles()->syncWithoutDetaching([$citizenRole->id]);
            $citizens->push($demoCitizen);

            $profileAddress = Address::updateOrCreate(['address_line_1' => 'Expanded Citizen Address ' . ($index + 1)], [
                'address_line_2' => null,
                'city' => ['Zahle', 'Saida', 'Byblos', 'Aley', 'Nabatieh', 'Baalbek'][$index],
                'region' => ['Bekaa', 'South Lebanon', 'Mount Lebanon', 'Mount Lebanon', 'Nabatieh', 'Baalbek-Hermel'][$index],
                'postal_code' => '20' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'country' => 'Lebanon',
                'latitude' => 33.55 + ($index * 0.11),
                'longitude' => 35.35 + ($index * 0.07),
            ]);

            CitizenProfile::updateOrCreate(['user_id' => $demoCitizen->id], [
                'address_id' => $profileAddress->id,
                'national_id_number' => '2000' . str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                'date_of_birth' => now()->subYears(25 + $index)->toDateString(),
                'id_document_path' => 'seed/expanded-citizen-id-' . ($index + 1) . '.pdf',
                'verification_status' => ['verified', 'pending', 'verified', 'verified', 'rejected', 'pending'][$index],
            ]);

            Storage::disk('public')->put('seed/expanded-citizen-id-' . ($index + 1) . '.pdf', 'Expanded demo citizen profile file for ' . $demoCitizen->full_name);
        }

        $moreMunicipalities = [
            [
                'name' => 'Saida Municipality',
                'region' => 'South Lebanon',
                'address' => ['address_line_1' => 'Saida Municipality Building', 'city' => 'Saida', 'postal_code' => '1600', 'latitude' => 33.5631000, 'longitude' => 35.3688000],
                'offices' => [
                    ['name' => 'Saida Civil Affairs Office', 'email' => 'civil@saida.gov.lb', 'phone' => '07-720001', 'address_line_1' => 'Civil Affairs Office - Saida', 'latitude' => 33.5622000, 'longitude' => 35.3710000],
                    ['name' => 'Saida Licensing Office', 'email' => 'licensing@saida.gov.lb', 'phone' => '07-720002', 'address_line_1' => 'Licensing Office - Saida', 'latitude' => 33.5650000, 'longitude' => 35.3658000],
                ],
            ],
            [
                'name' => 'Zahle Municipality',
                'region' => 'Bekaa',
                'address' => ['address_line_1' => 'Zahle Municipality Building', 'city' => 'Zahle', 'postal_code' => '1800', 'latitude' => 33.8463000, 'longitude' => 35.9020000],
                'offices' => [
                    ['name' => 'Zahle Tax Office', 'email' => 'tax@zahle.gov.lb', 'phone' => '08-820001', 'address_line_1' => 'Municipal Tax Office - Zahle', 'latitude' => 33.8491000, 'longitude' => 35.9013000],
                    ['name' => 'Zahle Public Health Office', 'email' => 'health@zahle.gov.lb', 'phone' => '08-820002', 'address_line_1' => 'Public Health Office - Zahle', 'latitude' => 33.8448000, 'longitude' => 35.9043000],
                ],
            ],
            [
                'name' => 'Byblos Municipality',
                'region' => 'Mount Lebanon',
                'address' => ['address_line_1' => 'Byblos Municipality Building', 'city' => 'Byblos', 'postal_code' => '1400', 'latitude' => 34.1230000, 'longitude' => 35.6519000],
                'offices' => [
                    ['name' => 'Byblos Heritage Permits Office', 'email' => 'heritage@byblos.gov.lb', 'phone' => '09-540001', 'address_line_1' => 'Heritage Permits Office - Byblos', 'latitude' => 34.1219000, 'longitude' => 35.6507000],
                    ['name' => 'Byblos Citizen Support Office', 'email' => 'support@byblos.gov.lb', 'phone' => '09-540002', 'address_line_1' => 'Citizen Support Office - Byblos', 'latitude' => 34.1241000, 'longitude' => 35.6533000],
                ],
            ],
        ];

        foreach ($moreMunicipalities as $municipalityData) {
            $address = Address::updateOrCreate(['address_line_1' => $municipalityData['address']['address_line_1']], [
                'address_line_2' => null,
                'city' => $municipalityData['address']['city'],
                'region' => $municipalityData['region'],
                'postal_code' => $municipalityData['address']['postal_code'],
                'country' => 'Lebanon',
                'latitude' => $municipalityData['address']['latitude'],
                'longitude' => $municipalityData['address']['longitude'],
            ]);

            $demoMunicipality = Municipality::updateOrCreate(['name' => $municipalityData['name']], [
                'region' => $municipalityData['region'],
                'address_id' => $address->id,
                'status' => 'active',
            ]);

            foreach ($municipalityData['offices'] as $officeData) {
                $officeAddress = Address::updateOrCreate(['address_line_1' => $officeData['address_line_1']], [
                    'address_line_2' => null,
                    'city' => $municipalityData['address']['city'],
                    'region' => $municipalityData['region'],
                    'postal_code' => $municipalityData['address']['postal_code'],
                    'country' => 'Lebanon',
                    'latitude' => $officeData['latitude'],
                    'longitude' => $officeData['longitude'],
                ]);

                $demoOffice = Office::updateOrCreate(['name' => $officeData['name']], [
                    'municipality_id' => $demoMunicipality->id,
                    'address_id' => $officeAddress->id,
                    'contact_email' => $officeData['email'],
                    'contact_phone' => $officeData['phone'],
                    'status' => 'active',
                ]);
                $allOffices->push($demoOffice);

                foreach ([1, 2, 3, 4, 5] as $day) {
                    OfficeWorkingHour::updateOrCreate(['office_id' => $demoOffice->id, 'weekday_number' => $day], [
                        'open_time' => $day === 5 ? '08:30' : '08:00',
                        'close_time' => $day === 5 ? '13:00' : '15:30',
                        'is_closed' => false,
                    ]);
                }
                OfficeWorkingHour::updateOrCreate(['office_id' => $demoOffice->id, 'weekday_number' => 6], [
                    'open_time' => '00:00',
                    'close_time' => '00:00',
                    'is_closed' => true,
                ]);
            }
        }

        $staffNames = [
            ['Lea', 'Sfeir', 'Operations Clerk'],
            ['Nabil', 'Hanna', 'Records Officer'],
            ['Carla', 'Azar', 'Payment Officer'],
            ['Samir', 'Younes', 'Service Coordinator'],
            ['Hiba', 'Mouawad', 'Front Desk Officer'],
            ['Walid', 'Kassis', 'Appointments Officer'],
            ['Mona', 'Sarkis', 'Document Officer'],
            ['Tony', 'Rahme', 'Municipal Agent'],
        ];

        foreach ($allOffices->values() as $index => $office) {
            $staffInfo = $staffNames[$index % count($staffNames)];
            $email = strtolower($staffInfo[0] . '.' . $staffInfo[1] . '.' . $office->id . '@eservices.test');
            $demoStaff = User::updateOrCreate(['email' => $email], [
                'first_name' => $staffInfo[0],
                'last_name' => $staffInfo[1],
                'phone' => '7100' . str_pad((string) $office->id, 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);
            $demoStaff->roles()->syncWithoutDetaching([$staffRole->id]);

            OfficeStaff::updateOrCreate(['office_id' => $office->id, 'user_id' => $demoStaff->id], [
                'job_title' => $staffInfo[2],
                'status' => 'active',
            ]);
        }

        $expandedServiceTemplates = [
            ['category' => 'Citizen Services', 'name' => 'Residence Certificate', 'price' => 7, 'appointment' => false, 'documents' => ['National ID', 'Lease Contract', 'Utility Bill']],
            ['category' => 'Citizen Services', 'name' => 'Address Change Request', 'price' => 5, 'appointment' => false, 'documents' => ['National ID', 'Proof of Address']],
            ['category' => 'Public Health', 'name' => 'Food Safety Inspection', 'price' => 60, 'appointment' => true, 'documents' => ['Business Registration', 'Location Photo', 'Owner ID']],
            ['category' => 'Licensing', 'name' => 'Shop Sign Permit', 'price' => 35, 'appointment' => true, 'documents' => ['Business Registration', 'Sign Design', 'Property Owner Approval']],
            ['category' => 'Municipal Taxes', 'name' => 'Payment Plan Request', 'price' => 0, 'appointment' => false, 'documents' => ['National ID', 'Tax Statement']],
            ['category' => 'Heritage', 'name' => 'Facade Renovation Approval', 'price' => 90, 'appointment' => true, 'documents' => ['Property Deed', 'Facade Photos', 'Engineer Report']],
            ['category' => 'Complaints', 'name' => 'Noise Complaint', 'price' => 0, 'appointment' => false, 'documents' => ['Location Photo', 'Citizen ID']],
            ['category' => 'Environment', 'name' => 'Tree Trimming Request', 'price' => 15, 'appointment' => false, 'documents' => ['Location Photo', 'Neighbor Consent']],
        ];

        $expandedDocumentNames = collect($expandedServiceTemplates)->pluck('documents')->flatten()->unique();
        foreach ($allOffices as $office) {
            foreach ($expandedDocumentNames as $documentName) {
                DocumentType::updateOrCreate(['office_id' => $office->id, 'name' => $documentName], [
                    'description' => 'Expanded reusable document type for ' . $office->name . '.',
                    'status' => 'active',
                ]);
            }
        }

        foreach ($allOffices->values() as $officeIndex => $office) {
            foreach ($expandedServiceTemplates as $templateIndex => $template) {
                if (($officeIndex + $templateIndex) % 3 === 1) {
                    continue;
                }

                $category = ServiceCategory::updateOrCreate(['office_id' => $office->id, 'name' => $template['category']], [
                    'description' => $template['category'] . ' handled through ' . $office->name . '.',
                ]);

                $service = Service::updateOrCreate(['office_id' => $office->id, 'name' => $template['name']], [
                    'category_id' => $category->id,
                    'description' => 'Expanded demo service: ' . $template['name'] . '.',
                    'instructions' => 'Submit the request, attach the listed documents, and wait for office review.',
                    'price' => $template['price'],
                    'duration_minutes' => $template['appointment'] ? 50 : 25,
                    'requires_appointment' => $template['appointment'],
                    'supports_online_payment' => $template['price'] > 0,
                    'supports_crypto_payment' => $template['price'] > 30,
                    'status' => ($officeIndex + $templateIndex) % 7 === 0 ? 'inactive' : 'active',
                ]);
                $allServices->push($service);

                foreach ($template['documents'] as $documentName) {
                    $documentType = DocumentType::where('office_id', $office->id)->where('name', $documentName)->first();

                    ServiceRequiredDocument::updateOrCreate(['service_id' => $service->id, 'document_name' => $documentName], [
                        'document_type_id' => $documentType?->id,
                        'is_required' => true,
                    ]);
                }
            }
        }

        $expandedStatuses = ['pending', 'approved', 'in_progress', 'completed', 'rejected'];
        foreach ($allServices->unique('id')->values()->take(50) as $serviceIndex => $service) {
            $requestNumber = 'REQ-2026-X' . str_pad((string) ($serviceIndex + 1), 4, '0', STR_PAD_LEFT);
            $office = $service->office;
            $demoCitizen = $citizens[$serviceIndex % $citizens->count()];
            $assignedUserId = $office->staff()->where('status', 'active')->skip($serviceIndex % 2)->value('user_id')
                ?: $office->staff()->where('status', 'active')->value('user_id');
            $status = $expandedStatuses[$serviceIndex % count($expandedStatuses)];

            $demoRequest = ServiceRequest::updateOrCreate(['request_number' => $requestNumber], [
                'citizen_user_id' => $demoCitizen->id,
                'office_id' => $office->id,
                'service_id' => $service->id,
                'assigned_to_user_id' => $assignedUserId,
                'status' => $status,
                'description' => 'Expanded seeded request for ' . $service->name . ' at ' . $office->name . '.',
                'qr_code' => 'QR-' . $requestNumber,
                'submitted_at' => now()->subDays(($serviceIndex % 20) + 1),
            ]);

            RequestStatusHistory::updateOrCreate(['request_id' => $demoRequest->id, 'new_status' => 'pending'], [
                'old_status' => 'pending',
                'changed_by_user_id' => $assignedUserId ?: $admin->id,
                'note' => 'Request submitted through the portal.',
                'changed_at' => now()->subDays(($serviceIndex % 20) + 1),
            ]);
            if ($status !== 'pending') {
                RequestStatusHistory::updateOrCreate(['request_id' => $demoRequest->id, 'new_status' => $status], [
                    'old_status' => 'pending',
                    'changed_by_user_id' => $assignedUserId ?: $admin->id,
                    'note' => 'Office updated the request during demo processing.',
                    'changed_at' => now()->subDays($serviceIndex % 10),
                ]);
            }

            foreach ($service->documents()->take(2)->get() as $documentIndex => $requiredDocument) {
                $filePath = 'seed/expanded-request-' . $demoRequest->id . '-' . $documentIndex . '.pdf';
                RequestDocument::updateOrCreate(['request_id' => $demoRequest->id, 'required_document_id' => $requiredDocument->id], [
                    'uploaded_by_user_id' => $documentIndex === 0 ? $demoCitizen->id : ($assignedUserId ?: $admin->id),
                    'file_name' => 'expanded-document-' . $demoRequest->id . '-' . $documentIndex . '.pdf',
                    'file_path' => $filePath,
                    'file_type' => 'pdf',
                    'document_role' => $documentIndex === 0 ? 'citizen_upload' : 'office_upload',
                    'uploaded_at' => now()->subDays($serviceIndex % 8),
                ]);
                Storage::disk('public')->put($filePath, 'Expanded seeded document for ' . $requestNumber);
            }

            if ($service->price > 0) {
                $paymentStatus = $status === 'rejected' ? 'failed' : ($serviceIndex % 6 === 0 ? 'pending' : 'success');
                $demoPayment = Payment::updateOrCreate(['transaction_reference' => 'EXP-' . $requestNumber], [
                    'request_id' => $demoRequest->id,
                    'user_id' => $demoCitizen->id,
                    'amount' => $service->price,
                    'currency' => 'USD',
                    'payment_method' => $serviceIndex % 4 === 0 ? 'crypto' : 'card',
                    'provider' => $serviceIndex % 4 === 0 ? 'DemoChain' : 'DemoPay',
                    'status' => $paymentStatus,
                    'paid_at' => $paymentStatus === 'success' ? now()->subDays($serviceIndex % 12) : null,
                ]);

                PaymentTransaction::updateOrCreate(['payment_id' => $demoPayment->id, 'provider_reference' => 'EXP-DP-' . (910000 + $serviceIndex)], [
                    'transaction_type' => $paymentStatus === 'failed' ? 'failed_charge' : 'charge',
                    'tx_hash' => $demoPayment->payment_method === 'crypto' ? '0xexpanded' . $serviceIndex : null,
                    'status' => $paymentStatus,
                    'processed_at' => now()->subDays($serviceIndex % 12),
                ]);
            }

            if ($service->requires_appointment || $serviceIndex % 3 === 0) {
                $slotDate = now()->addDays(($serviceIndex % 15) + 1)->toDateString();
                $slotHour = 9 + ($serviceIndex % 5);
                $startTime = str_pad((string) $slotHour, 2, '0', STR_PAD_LEFT) . ':00';
                $endTime = str_pad((string) ($slotHour + 1), 2, '0', STR_PAD_LEFT) . ':00';

                $slot = AppointmentSlot::updateOrCreate([
                    'office_id' => $office->id,
                    'service_id' => $service->id,
                    'slot_date' => $slotDate,
                    'start_time' => $startTime,
                ], [
                    'end_time' => $endTime,
                    'capacity' => 3 + ($serviceIndex % 4),
                    'status' => $serviceIndex % 9 === 0 ? 'full' : 'available',
                ]);

                Appointment::updateOrCreate(['request_id' => $demoRequest->id, 'slot_id' => $slot->id], [
                    'citizen_user_id' => $demoCitizen->id,
                    'office_id' => $office->id,
                    'status' => $status === 'completed' ? 'completed' : ($status === 'rejected' ? 'cancelled' : 'scheduled'),
                    'notes' => 'Expanded seeded appointment.',
                ]);
            }

            $chat = Chat::updateOrCreate(['request_id' => $demoRequest->id], [
                'citizen_user_id' => $demoCitizen->id,
                'office_id' => $office->id,
                'status' => $status === 'completed' ? 'closed' : 'open',
            ]);

            ChatMessage::updateOrCreate(['chat_id' => $chat->id, 'sender_user_id' => $demoCitizen->id, 'message_text' => 'I uploaded the documents for ' . $service->name . '.'], [
                'attachment_path' => null,
                'sent_at' => now()->subHours($serviceIndex + 2),
            ]);

            if ($assignedUserId) {
                ChatMessage::updateOrCreate(['chat_id' => $chat->id, 'sender_user_id' => $assignedUserId, 'message_text' => 'Thank you, we are reviewing your request.'], [
                    'attachment_path' => null,
                    'sent_at' => now()->subHours($serviceIndex + 1),
                ]);
            }

            if (in_array($status, ['completed', 'approved'])) {
                Feedback::updateOrCreate(['request_id' => $demoRequest->id], [
                    'citizen_user_id' => $demoCitizen->id,
                    'office_id' => $office->id,
                    'rating' => 3 + ($serviceIndex % 3),
                    'comment' => 'Expanded seeded feedback for service quality.',
                    'office_reply' => $serviceIndex % 2 === 0 ? 'We appreciate your feedback.' : null,
                ]);
            }

            foreach (['certificate', 'receipt', 'report'] as $docTypeIndex => $documentType) {
                if ($docTypeIndex > ($serviceIndex % 3)) {
                    continue;
                }

                $generatedPath = 'generated-documents/seed-' . $documentType . '-' . $requestNumber . '.pdf';
                GeneratedDocument::updateOrCreate(['request_id' => $demoRequest->id, 'document_type' => $documentType], [
                    'file_path' => $generatedPath,
                    'generated_at' => now()->subDays($docTypeIndex),
                ]);
                Storage::disk('public')->put($generatedPath, 'Seeded generated ' . $documentType . ' for ' . $requestNumber);
            }

            foreach (['system', 'email', 'sms'] as $channelIndex => $channel) {
                Notification::updateOrCreate(['user_id' => $demoCitizen->id, 'title' => 'Request update ' . $requestNumber . ' ' . $channel], [
                    'type' => 'request_status',
                    'message' => 'Your request ' . $requestNumber . ' is currently ' . str_replace('_', ' ', $status) . '.',
                    'channel' => $channel,
                    'is_read' => $channelIndex === 0,
                ]);
            }
            if ($assignedUserId) {
                Notification::updateOrCreate(['user_id' => $assignedUserId, 'title' => 'Assigned request ' . $requestNumber], [
                    'type' => 'request_assignment',
                    'message' => 'A request is assigned to you for follow-up.',
                    'channel' => 'system',
                    'is_read' => $serviceIndex % 2 === 0,
                ]);
            }
        }

        $weeklySchedules = [
            1 => ['08:00', '15:30', false],
            2 => ['08:00', '15:30', false],
            3 => ['08:00', '15:30', false],
            4 => ['08:00', '15:30', false],
            5 => ['08:30', '13:00', false],
            6 => ['09:00', '12:00', false],
            7 => ['00:00', '00:00', true],
        ];

        foreach (Office::all() as $office) {
            foreach ($weeklySchedules as $weekdayNumber => [$openTime, $closeTime, $isClosed]) {
                OfficeWorkingHour::updateOrCreate([
                    'office_id' => $office->id,
                    'weekday_number' => $weekdayNumber,
                ], [
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'is_closed' => $isClosed,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Extra Appointment Slots For Testing
        |--------------------------------------------------------------------------
        | This creates many available future slots for every active service that
        | requires an appointment. It does not delete existing slots or requests.
        */
        $appointmentServices = Service::where('status', 'active')
            ->where('requires_appointment', true)
            ->with('office')
            ->get();

        $slotTimes = [
            ['09:00', '10:00'],
            ['10:00', '11:00'],
            ['11:00', '12:00'],
            ['13:00', '14:00'],
            ['14:00', '15:00'],
        ];

        foreach ($appointmentServices as $service) {
            for ($day = 1; $day <= 30; $day++) {
                $date = now()->addDays($day);

                // Skip Sunday. Carbon dayOfWeek: 0 = Sunday, 6 = Saturday.
                if ($date->dayOfWeek === 0) {
                    continue;
                }

                foreach ($slotTimes as [$startTime, $endTime]) {
                    AppointmentSlot::updateOrCreate([
                        'office_id' => $service->office_id,
                        'service_id' => $service->id,
                        'slot_date' => $date->toDateString(),
                        'start_time' => $startTime,
                    ], [
                        'end_time' => $endTime,
                        'capacity' => 10,
                        'status' => 'available',
                    ]);
                }
            }
        }
    }
}
