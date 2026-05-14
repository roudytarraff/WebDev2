<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;

class OfficeBaseController extends Controller
{
    protected function currentOffice(): Office
    {
        if (Auth::user()->isAdmin()) {
            return Office::firstOrFail();
        }

        $staff = Auth::user()->officeStaff()
            ->where('status', 'active')
            ->with('office')
            ->first();

        if (! $staff) {
            abort(403, 'Your staff account is not assigned to an active office yet. Please ask the admin to assign you from Admin > Office Staff.');
        }

        return $staff->office;
    }
}
