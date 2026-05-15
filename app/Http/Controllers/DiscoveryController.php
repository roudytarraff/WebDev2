<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $municipalityId = $request->input('municipality_id');
        $categoryName = $request->input('category');

        $municipalities = Municipality::where('status', 'active')
            ->orderBy('name')
            ->get();

        $categoryNames = ServiceCategory::whereHas('office', function ($query) {
                $query->where('status', 'active');
            })
            ->whereHas('services', function ($query) {
                $query->where('status', 'active');
            })
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        $offices = Office::with([
                'municipality',
                'address',
                'workingHours' => function ($query) {
                    $query->orderBy('weekday_number');
                },
                'services' => function ($query) use ($categoryName) {
                    $query->where('status', 'active')
                        ->when($categoryName, function ($serviceQuery) use ($categoryName) {
                            $serviceQuery->whereHas('category', function ($categoryQuery) use ($categoryName) {
                                $categoryQuery->where('name', $categoryName);
                            });
                        })
                        ->with(['category', 'documents'])
                        ->orderBy('name');
                },
            ])
            ->withCount([
                'services as active_services_count' => function ($query) {
                    $query->where('status', 'active');
                },
                'services as matching_category_services_count' => function ($query) use ($categoryName) {
                    $query->where('status', 'active')
                        ->when($categoryName, function ($serviceQuery) use ($categoryName) {
                            $serviceQuery->whereHas('category', function ($categoryQuery) use ($categoryName) {
                                $categoryQuery->where('name', $categoryName);
                            });
                        });
                },
            ])
            ->where('status', 'active')
            ->whereHas('municipality', function ($query) {
                $query->where('status', 'active');
            })
            ->when($municipalityId, function ($query) use ($municipalityId) {
                $query->where('municipality_id', $municipalityId);
            })
            ->when($categoryName, function ($query) use ($categoryName) {
                $query->whereHas('services', function ($serviceQuery) use ($categoryName) {
                    $serviceQuery->where('status', 'active')
                        ->whereHas('category', function ($categoryQuery) use ($categoryName) {
                            $categoryQuery->where('name', $categoryName);
                        });
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%")
                        ->orWhere('contact_phone', 'like', "%{$search}%")
                        ->orWhereHas('municipality', function ($municipalityQuery) use ($search) {
                            $municipalityQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('region', 'like', "%{$search}%");
                        })
                        ->orWhereHas('address', function ($addressQuery) use ($search) {
                            $addressQuery->where('city', 'like', "%{$search}%")
                                ->orWhere('region', 'like', "%{$search}%")
                                ->orWhere('address_line_1', 'like', "%{$search}%");
                        })
                        ->orWhereHas('services', function ($serviceQuery) use ($search) {
                            $serviceQuery->where('status', 'active')
                                ->where(function ($serviceSearchQuery) use ($search) {
                                    $serviceSearchQuery->where('name', 'like', "%{$search}%")
                                        ->orWhere('description', 'like', "%{$search}%")
                                        ->orWhere('instructions', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->orderBy('name')
            ->get();

        $totalServices = Service::where('status', 'active')
            ->whereHas('office', function ($query) {
                $query->where('status', 'active')
                    ->whereHas('municipality', function ($municipalityQuery) {
                        $municipalityQuery->where('status', 'active');
                    });
            })
            ->count();

        return view('discovery.index', compact(
            'offices',
            'municipalities',
            'categoryNames',
            'totalServices',
            'search',
            'municipalityId',
            'categoryName'
        ));
    }

    public function showOffice(string $id)
    {
        $office = Office::with([
                'municipality',
                'address',
                'workingHours' => function ($query) {
                    $query->orderBy('weekday_number');
                },
                'services' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['category', 'documents'])
                        ->orderBy('name');
                },
            ])
            ->where('status', 'active')
            ->whereHas('municipality', function ($query) {
                $query->where('status', 'active');
            })
            ->findOrFail($id);

        return view('discovery.office-show', compact('office'));
    }

    public function showService(string $id)
    {
        $service = Service::with([
                'category',
                'documents.documentType',
                'office.municipality',
                'office.address',
                'office.workingHours',
            ])
            ->where('status', 'active')
            ->whereHas('office', function ($query) {
                $query->where('status', 'active')
                    ->whereHas('municipality', function ($municipalityQuery) {
                        $municipalityQuery->where('status', 'active');
                    });
            })
            ->findOrFail($id);

        return view('discovery.service-show', compact('service'));
    }
}