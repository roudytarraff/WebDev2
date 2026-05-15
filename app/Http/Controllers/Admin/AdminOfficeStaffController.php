<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\OfficeStaff;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminOfficeStaffController extends Controller
{
    public function index()
    {
        $staff = OfficeStaff::with(['office.municipality', 'user'])->orderBy('office_id')->get();
        return view('admin.office_staff.index', compact('staff'));
    }

    public function create()
    {
        $offices = Office::where('status', 'active')->orderBy('name')->get();
        $users = User::with(['roles', 'officeStaff.office'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.office_staff.create', compact('offices', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'user_mode' => 'required|in:existing,new',
            'user_id' => 'required_if:user_mode,existing|nullable|exists:users,id',
            'first_name' => 'required_if:user_mode,new|nullable|string|max:255',
            'last_name' => 'required_if:user_mode,new|nullable|string|max:255',
            'email' => 'required_if:user_mode,new|nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:255',
            'password' => 'required_if:user_mode,new|nullable|string|min:6|confirmed',
            'job_title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->user_mode === 'new') {
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->status = 'active';
            $user->save();
            $userId = $user->id;
        } else {
            $userId = $request->user_id;
        }

        $exists = OfficeStaff::where('office_id', $request->office_id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return back()->withErrors(['user_id' => 'This user is already assigned to this office.'])->withInput();
        }

        $staff = new OfficeStaff();
        $staff->office_id = $request->office_id;
        $staff->user_id = $userId;
        $staff->job_title = $request->job_title;
        $staff->status = $request->status;
        $staff->save();

        $role = Role::firstOrCreate(['name' => 'staff']);
        $staff->user->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->route('admin.office-staff.index')->with('success', 'Office staff assigned successfully.');
    }

    public function show(string $id)
    {
        $staff = OfficeStaff::with(['office.municipality', 'user.roles'])->findOrFail($id);
        return view('admin.office_staff.show', compact('staff'));
    }

    public function edit(string $id)
    {
        $staff = OfficeStaff::findOrFail($id);
        $offices = Office::where('status', 'active')->orderBy('name')->get();
        $users = User::with(['roles', 'officeStaff.office'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.office_staff.edit', compact('staff', 'offices', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user_id)],
            'phone' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'job_title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $exists = OfficeStaff::where('office_id', $request->office_id)
            ->where('user_id', $request->user_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['user_id' => 'This user is already assigned to this office.'])->withInput();
        }

        $staff = OfficeStaff::findOrFail($id);
        $user = User::findOrFail($request->user_id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = 'active';

        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $staff->office_id = $request->office_id;
        $staff->user_id = $request->user_id;
        $staff->job_title = $request->job_title;
        $staff->status = $request->status;
        $staff->save();

        return redirect()->route('admin.office-staff.index')->with('success', 'Office staff updated successfully.');
    }

    public function destroy(string $id)
    {
        $staff = OfficeStaff::findOrFail($id);
        $staff->delete();

        return redirect()->route('admin.office-staff.index')->with('success', 'Office staff removed successfully.');
    }

    public function toggleStatus(string $id)
    {
        $staff = OfficeStaff::findOrFail($id);
        $staff->status = $staff->status === 'active' ? 'inactive' : 'active';
        $staff->save();

        return back()->with('success', 'Office staff status changed successfully.');
    }
}
