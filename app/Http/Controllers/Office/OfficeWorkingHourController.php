<?php

namespace App\Http\Controllers\Office;

use App\Models\OfficeWorkingHour;
use Illuminate\Http\Request;

class OfficeWorkingHourController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $hours = OfficeWorkingHour::where('office_id', $office->id)->orderBy('weekday_number')->get();
        return view('office.working_hours.index', compact('office', 'hours'));
    }

    public function create()
    {
        return view('office.working_hours.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'weekday_number' => 'required|integer|between:1,7',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'is_closed' => 'nullable|boolean',
        ]);

        if (! $request->boolean('is_closed') && strtotime($request->close_time) <= strtotime($request->open_time)) {
            return back()->withErrors(['close_time' => 'Close time must be after open time.'])->withInput();
        }

        $office = $this->currentOffice();
        $exists = OfficeWorkingHour::where('office_id', $office->id)
            ->where('weekday_number', $request->weekday_number)
            ->exists();

        if ($exists) {
            return back()->withErrors(['weekday_number' => 'This day already has working hours.'])->withInput();
        }

        $hour = new OfficeWorkingHour();
        $hour->office_id = $office->id;
        $hour->weekday_number = $request->weekday_number;
        $hour->open_time = $request->open_time;
        $hour->close_time = $request->close_time;
        $hour->is_closed = $request->boolean('is_closed');
        $hour->save();

        return redirect()->route('office.working-hours.index')->with('success', 'Working hours created successfully.');
    }

    public function edit(string $id)
    {
        $office = $this->currentOffice();
        $hour = OfficeWorkingHour::where('office_id', $office->id)->findOrFail($id);
        return view('office.working_hours.edit', compact('hour'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'weekday_number' => 'required|integer|between:1,7',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'is_closed' => 'nullable|boolean',
        ]);

        if (! $request->boolean('is_closed') && strtotime($request->close_time) <= strtotime($request->open_time)) {
            return back()->withErrors(['close_time' => 'Close time must be after open time.'])->withInput();
        }

        $office = $this->currentOffice();
        $hour = OfficeWorkingHour::where('office_id', $office->id)->findOrFail($id);

        $exists = OfficeWorkingHour::where('office_id', $office->id)
            ->where('weekday_number', $request->weekday_number)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['weekday_number' => 'This day already has working hours.'])->withInput();
        }

        $hour->weekday_number = $request->weekday_number;
        $hour->open_time = $request->open_time;
        $hour->close_time = $request->close_time;
        $hour->is_closed = $request->boolean('is_closed');
        $hour->save();

        return redirect()->route('office.working-hours.index')->with('success', 'Working hours updated successfully.');
    }

    public function destroy(string $id)
    {
        $office = $this->currentOffice();
        $hour = OfficeWorkingHour::where('office_id', $office->id)->findOrFail($id);
        $hour->delete();

        return redirect()->route('office.working-hours.index')->with('success', 'Working hours deleted successfully.');
    }
}
