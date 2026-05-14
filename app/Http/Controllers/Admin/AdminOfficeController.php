<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Municipality;
use App\Models\Office;
use Illuminate\Http\Request;

class AdminOfficeController extends Controller
{
    public function index()
    {
        $offices = Office::with(['municipality', 'address'])->orderBy('name')->get();
        $mapOffices = $offices
            ->filter(fn ($office) => $office->address && $office->address->latitude && $office->address->longitude)
            ->map(fn ($office) => [
                'name' => $office->name,
                'municipality' => $office->municipality->name ?? 'No municipality',
                'address' => $office->address->address_line_1,
                'email' => $office->contact_email,
                'phone' => $office->contact_phone,
                'lat' => (float) $office->address->latitude,
                'lng' => (float) $office->address->longitude,
                'status' => $office->status,
                'url' => route('admin.offices.show', $office->id),
            ])
            ->values();

        return view('admin.offices.index', compact('offices', 'mapOffices'));
    }

    public function create()
    {
        $municipalities = Municipality::where('status', 'active')->orderBy('name')->get();
        return view('admin.offices.create', compact('municipalities'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $address = new Address();
        $this->saveAddress($address, $request);

        $office = new Office();
        $office->municipality_id = $request->municipality_id;
        $office->address_id = $address->id;
        $office->name = $request->name;
        $office->contact_email = $request->contact_email;
        $office->contact_phone = $request->contact_phone;
        $office->status = $request->status;
        $office->save();

        return redirect()->route('admin.offices.index')->with('success', 'Office created successfully.');
    }

    public function show(string $id)
    {
        $office = Office::with(['municipality', 'address', 'staff.user', 'services', 'workingHours'])->findOrFail($id);
        return view('admin.offices.show', compact('office'));
    }

    public function edit(string $id)
    {
        $office = Office::with('address')->findOrFail($id);
        $municipalities = Municipality::where('status', 'active')->orderBy('name')->get();
        return view('admin.offices.edit', compact('office', 'municipalities'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate($this->rules());

        $office = Office::with('address')->findOrFail($id);
        $address = $office->address ?: new Address();
        $this->saveAddress($address, $request);

        $office->municipality_id = $request->municipality_id;
        $office->address_id = $address->id;
        $office->name = $request->name;
        $office->contact_email = $request->contact_email;
        $office->contact_phone = $request->contact_phone;
        $office->status = $request->status;
        $office->save();

        return redirect()->route('admin.offices.index')->with('success', 'Office updated successfully.');
    }

    public function destroy(string $id)
    {
        $office = Office::findOrFail($id);
        $office->delete();

        return redirect()->route('admin.offices.index')->with('success', 'Office deleted successfully.');
    }

    public function toggleStatus(string $id)
    {
        $office = Office::findOrFail($id);
        $office->status = $office->status === 'active' ? 'inactive' : 'active';
        $office->save();

        return back()->with('success', 'Office status changed successfully.');
    }

    private function rules(): array
    {
        return [
            'municipality_id' => 'required|exists:municipalities,id',
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:active,inactive',
        ];
    }

    private function saveAddress(Address $address, Request $request): void
    {
        $address->address_line_1 = $request->address_line_1;
        $address->address_line_2 = $request->address_line_2;
        $address->city = $request->city;
        $address->region = $request->region;
        $address->postal_code = $request->postal_code;
        $address->country = $request->country;
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();
    }
}
