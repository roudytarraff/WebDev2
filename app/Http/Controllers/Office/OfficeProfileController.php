<?php

namespace App\Http\Controllers\Office;

use App\Models\Address;
use Illuminate\Http\Request;

class OfficeProfileController extends OfficeBaseController
{
    public function show()
    {
        $office = $this->currentOffice()->load(['municipality', 'address', 'workingHours']);
        return view('office.profile.show', compact('office'));
    }

    public function edit()
    {
        $office = $this->currentOffice()->load('address');
        return view('office.profile.edit', compact('office'));
    }

    public function update(Request $request)
    {
        $request->validate([
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
        ]);

        $office = $this->currentOffice()->load('address');
        $address = $office->address ?: new Address();
        $address->address_line_1 = $request->address_line_1;
        $address->address_line_2 = $request->address_line_2;
        $address->city = $request->city;
        $address->region = $request->region;
        $address->postal_code = $request->postal_code;
        $address->country = $request->country;
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();

        $office->name = $request->name;
        $office->contact_email = $request->contact_email;
        $office->contact_phone = $request->contact_phone;
        $office->address_id = $address->id;
        $office->save();

        return redirect()->route('office.profile.show')->with('success', 'Office profile updated successfully.');
    }
}
