<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $municipalitiesCount = Municipality::count();
        $officesCount = Office::count();
        $requestsCount = ServiceRequest::count();
        $pendingRequests = ServiceRequest::where('status', 'pending')->count();
        $revenue = Payment::where('status', 'success')->sum('amount');

        $requestsByOffice = Office::withCount('serviceRequests')
            ->orderByDesc('service_requests_count')
            ->take(6)
            ->get();

        $recentRequests = ServiceRequest::with(['citizen', 'office', 'service'])
            ->latest()
            ->take(8)
            ->get();

        $mapOffices = Office::with(['municipality', 'address'])
            ->whereHas('address', fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->get()
            ->map(fn (Office $office) => [
                'type' => 'office',
                'name' => $office->name,
                'municipality' => $office->municipality->name ?? '',
                'email' => $office->contact_email,
                'phone' => $office->contact_phone,
                'status' => $office->status,
                'lat' => (float) $office->address->latitude,
                'lng' => (float) $office->address->longitude,
                'url' => route('admin.offices.show', $office->id),
            ])
            ->values();

        $mapMunicipalities = Municipality::with('address')
            ->whereHas('address', fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->get()
            ->map(fn (Municipality $municipality) => [
                'type' => 'municipality',
                'name' => $municipality->name,
                'municipality' => $municipality->region,
                'email' => '',
                'phone' => '',
                'status' => $municipality->status,
                'lat' => (float) $municipality->address->latitude,
                'lng' => (float) $municipality->address->longitude,
                'url' => route('admin.municipalities.show', $municipality->id),
            ])
            ->values();

        return view('admin.dashboard', compact(
            'usersCount',
            'municipalitiesCount',
            'officesCount',
            'requestsCount',
            'pendingRequests',
            'revenue',
            'requestsByOffice',
            'recentRequests',
            'mapOffices',
            'mapMunicipalities'
        ));
    }
}
