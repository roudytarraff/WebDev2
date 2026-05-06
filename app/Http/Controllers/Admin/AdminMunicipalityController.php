<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Municipality;
use Illuminate\Http\Request;

class AdminMunicipalityController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::with('address')->orderBy('name')->get();
        $mapMunicipalities = $municipalities
            ->filter(fn ($municipality) => $municipality->address && $municipality->address->latitude && $municipality->address->longitude)
            ->map(fn ($municipality) => [
                'name' => $municipality->name,
                'region' => $municipality->region,
                'address' => $municipality->address->address_line_1,
                'lat' => (float) $municipality->address->latitude,
                'lng' => (float) $municipality->address->longitude,
                'status' => $municipality->status,
                'url' => route('admin.municipalities.show', $municipality->id),
            ])
            ->values();

        return view('admin.municipalities.index', compact('municipalities', 'mapMunicipalities'));
    }

    public function create()
    {
        return view('admin.municipalities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $address = new Address();
        $this->saveAddress($address, $request);

        $municipality = new Municipality();
        $municipality->name = $request->name;
        $municipality->region = $request->region;
        $municipality->address_id = $address->id;
        $municipality->status = $request->status;
        $municipality->save();

        return redirect()->route('admin.municipalities.index')->with('success', 'Municipality created successfully.');
    }

    public function show(string $id)
    {
        $municipality = Municipality::with(['address', 'offices.address'])->findOrFail($id);
        return view('admin.municipalities.show', compact('municipality'));
    }

    public function edit(string $id)
    {
        $municipality = Municipality::with('address')->findOrFail($id);
        return view('admin.municipalities.edit', compact('municipality'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $municipality = Municipality::with('address')->findOrFail($id);
        $address = $municipality->address ?: new Address();
        $this->saveAddress($address, $request);

        $municipality->name = $request->name;
        $municipality->region = $request->region;
        $municipality->address_id = $address->id;
        $municipality->status = $request->status;
        $municipality->save();

        return redirect()->route('admin.municipalities.index')->with('success', 'Municipality updated successfully.');
    }

    public function destroy(string $id)
    {
        $municipality = Municipality::findOrFail($id);
        $municipality->delete();

        return redirect()->route('admin.municipalities.index')->with('success', 'Municipality deleted successfully.');
    }

    public function toggleStatus(string $id)
    {
        $municipality = Municipality::findOrFail($id);
        $municipality->status = $municipality->status === 'active' ? 'inactive' : 'active';
        $municipality->save();

        return back()->with('success', 'Municipality status changed successfully.');
    }

    private function saveAddress(Address $address, Request $request): void
    {
        $address->address_line_1 = $request->address_line_1;
        $address->address_line_2 = $request->address_line_2;
        $address->city = $request->city;
        $address->region = $request->region ?? $request->city;
        $address->postal_code = $request->postal_code;
        $address->country = $request->country;
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();
    }
}
