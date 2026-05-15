<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMunicipalityController;
use App\Http\Controllers\Admin\AdminOfficeController;
use App\Http\Controllers\Admin\AdminOfficeStaffController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Office\OfficeAppointmentController;
use App\Http\Controllers\Office\OfficeAppointmentSlotController;
use App\Http\Controllers\Office\OfficeCategoryController;
use App\Http\Controllers\Office\OfficeChatController;
use App\Http\Controllers\Office\OfficeDashboardController;
use App\Http\Controllers\Office\OfficeDocumentTypeController;
use App\Http\Controllers\Office\OfficeFeedbackController;
use App\Http\Controllers\Office\OfficeNotificationController;
use App\Http\Controllers\Office\OfficePaymentController;
use App\Http\Controllers\Office\OfficeProfileController;
use App\Http\Controllers\Office\OfficeRequiredDocumentController;
use App\Http\Controllers\Office\OfficeRequestController;
use App\Http\Controllers\Office\OfficeServiceController;
use App\Http\Controllers\Office\OfficeWorkingHourController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\Citizen\CitizenRequestTrackingController;
use App\Http\Controllers\Citizen\CitizenProfileController;
use App\Http\Controllers\Citizen\CitizenServiceRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('auth.login');
    }

    if (Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if (Auth::user()->isOfficeStaff()) {
        return redirect()->route('office.dashboard');
    }

    return redirect()->route('home');
});

Route::get('register', [AuthController::class, 'register'])->name('auth.register');
Route::post('register', [AuthController::class, 'create'])->name('auth.create');

Route::get('login', [AuthController::class, 'login'])->name('auth.login');
Route::post('login', [AuthController::class, 'connect'])->name('auth.connect');
Route::get('logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/otp', [AuthController::class, 'otpForm'])->name('otp.form');
Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

Route::get('/track/{qrCode}', [TrackingController::class, 'show'])->name('tracking.show');

Route::get('/track/{qrCode}/qr', [TrackingController::class, 'qrImage'])
    ->name('tracking.qr-image');

Route::middleware(['isconnected', 'otp'])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');
});

Route::redirect('/Office', '/office');
Route::redirect('/office/dashboard', '/office');

Route::prefix('admin')->name('admin.')->middleware(['isconnected', 'otp', 'isAdmin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class);
    Route::post('users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

    Route::resource('municipalities', AdminMunicipalityController::class);
    Route::post('municipalities/{id}/toggle-status', [AdminMunicipalityController::class, 'toggleStatus'])->name('municipalities.toggle-status');

    Route::resource('offices', AdminOfficeController::class);
    Route::post('offices/{id}/toggle-status', [AdminOfficeController::class, 'toggleStatus'])->name('offices.toggle-status');

    Route::resource('office-staff', AdminOfficeStaffController::class);
    Route::post('office-staff/{id}/toggle-status', [AdminOfficeStaffController::class, 'toggleStatus'])->name('office-staff.toggle-status');

    Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
});

Route::prefix('office')->name('office.')->middleware(['isconnected', 'otp', 'isOfficeStaff'])->group(function () {
    Route::get('/', [OfficeDashboardController::class, 'index'])->name('dashboard');

    Route::get('profile', [OfficeProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [OfficeProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [OfficeProfileController::class, 'update'])->name('profile.update');

    Route::resource('working-hours', OfficeWorkingHourController::class)->except(['show']);
    Route::resource('categories', OfficeCategoryController::class)->except(['show']);
    Route::resource('document-types', OfficeDocumentTypeController::class)->except(['show']);
    Route::resource('services', OfficeServiceController::class);

    Route::get('services/{service}/required-documents/create', [OfficeRequiredDocumentController::class, 'create'])->name('required-documents.create');
    Route::post('services/{service}/required-documents', [OfficeRequiredDocumentController::class, 'store'])->name('required-documents.store');
    Route::get('required-documents/{id}/edit', [OfficeRequiredDocumentController::class, 'edit'])->name('required-documents.edit');
    Route::put('required-documents/{id}', [OfficeRequiredDocumentController::class, 'update'])->name('required-documents.update');
    Route::delete('required-documents/{id}', [OfficeRequiredDocumentController::class, 'destroy'])->name('required-documents.destroy');

    Route::get('requests', [OfficeRequestController::class, 'index'])->name('requests.index');
    Route::get('requests/{id}', [OfficeRequestController::class, 'show'])->name('requests.show');
    Route::get('requests/{id}/chat', [OfficeChatController::class, 'openForRequest'])->name('requests.chat');
    Route::put('requests/{id}/status', [OfficeRequestController::class, 'updateStatus'])->name('requests.update-status');
    Route::post('requests/{id}/documents', [OfficeRequestController::class, 'uploadDocument'])->name('requests.documents.store');
    Route::get('requests/{id}/documents/{documentId}/download', [OfficeRequestController::class, 'downloadDocument'])->name('requests.documents.download');
    Route::post('requests/{id}/generated-documents', [OfficeRequestController::class, 'generateDocument'])->name('requests.generated-documents.store');
    Route::get('requests/{id}/generated-documents/{documentId}/download', [OfficeRequestController::class, 'downloadGeneratedDocument'])->name('requests.generated-documents.download');

    Route::resource('appointment-slots', OfficeAppointmentSlotController::class)->except(['show']);
    Route::get('appointments', [OfficeAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/{id}', [OfficeAppointmentController::class, 'show'])->name('appointments.show');
    Route::put('appointments/{id}', [OfficeAppointmentController::class, 'update'])->name('appointments.update');

    Route::get('feedback', [OfficeFeedbackController::class, 'index'])->name('feedback.index');
    Route::get('feedback/{id}', [OfficeFeedbackController::class, 'show'])->name('feedback.show');
    Route::put('feedback/{id}', [OfficeFeedbackController::class, 'reply'])->name('feedback.reply');

    Route::get('chats', [OfficeChatController::class, 'index'])->name('chats.index');
    Route::get('chats/{id}', [OfficeChatController::class, 'show'])->name('chats.show');
    Route::get('chats/{id}/messages', [OfficeChatController::class, 'messages'])->name('chats.messages.index');
    Route::post('chats/{id}/messages', [OfficeChatController::class, 'storeMessage'])->name('chats.messages.store');

    Route::get('payments', [OfficePaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{id}', [OfficePaymentController::class, 'show'])->name('payments.show');

    Route::get('notifications', [OfficeNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications', [OfficeNotificationController::class, 'store'])->name('notifications.store');
    Route::put('notifications/{id}/read', [OfficeNotificationController::class, 'markRead'])->name('notifications.read');

    
});

    Route::get('/discover', [DiscoveryController::class, 'index'])->name('discovery.index');
    Route::get('/discover/offices/{id}', [DiscoveryController::class, 'showOffice'])->name('discovery.offices.show');
    Route::get('/discover/services/{id}', [DiscoveryController::class, 'showService'])->name('discovery.services.show');

   Route::prefix('citizen')->name('citizen.')->middleware(['isconnected', 'otp'])->group(function () {
    Route::get('profile', [CitizenProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [CitizenProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [CitizenProfileController::class, 'update'])->name('profile.update');

    Route::middleware('citizenVerified')->group(function () {

    Route::get('services/{service}/request/start', [CitizenServiceRequestController::class, 'start'])
            ->name('service-requests.start');

        Route::post('services/{service}/request/details', [CitizenServiceRequestController::class, 'storeDetails'])
            ->name('service-requests.details.store');

        Route::get('service-requests/documents', [CitizenServiceRequestController::class, 'documents'])
            ->name('service-requests.documents');

        Route::post('service-requests/documents', [CitizenServiceRequestController::class, 'storeDocuments'])
            ->name('service-requests.documents.store');

        Route::get('service-requests/appointment', [CitizenServiceRequestController::class, 'appointment'])
            ->name('service-requests.appointment');

        Route::post('service-requests/appointment', [CitizenServiceRequestController::class, 'storeAppointment'])
            ->name('service-requests.appointment.store');

        Route::get('service-requests/payment', [CitizenServiceRequestController::class, 'payment'])
            ->name('service-requests.payment');

        Route::post('service-requests/payment', [CitizenServiceRequestController::class, 'storePayment'])
            ->name('service-requests.payment.store');

        Route::get('service-requests/review', [CitizenServiceRequestController::class, 'review'])
            ->name('service-requests.review');

        Route::post('service-requests/submit', [CitizenServiceRequestController::class, 'submit'])
            ->name('service-requests.submit');

        Route::get('service-requests/cancel', [CitizenServiceRequestController::class, 'cancel'])
            ->name('service-requests.cancel');

        Route::post('appointments/{appointment}/cancel', [CitizenServiceRequestController::class, 'cancelAppointment'])
            ->name('appointments.cancel');

        Route::get('requests', [CitizenRequestTrackingController::class, 'index'])->name('requests.index');

        Route::get('requests/{id}', [CitizenRequestTrackingController::class, 'show'])->name('requests.show');

        Route::get('requests/{id}/documents/{documentId}/download', [CitizenRequestTrackingController::class, 'downloadDocument'])
            ->name('requests.documents.download');

        Route::get('requests/{id}/generated-documents/{documentId}/download', [CitizenRequestTrackingController::class, 'downloadGeneratedDocument'])
            ->name('requests.generated-documents.download');
    });
});
