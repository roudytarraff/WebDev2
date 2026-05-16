<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\VerifyCitizenProfileRequest;
use App\Models\Address;
use App\Models\CitizenProfile;
use App\Services\OcrSpaceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CitizenProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $profile = $user->citizenProfile()
            ->with('address')
            ->first();

        return view('citizen.profile.show', compact('user', 'profile'));
    }

    public function edit()
    {
        $user = Auth::user();

        $profile = $user->citizenProfile()
            ->with('address')
            ->first();

        return view('citizen.profile.edit', compact('user', 'profile'));
    }

    public function update(VerifyCitizenProfileRequest $request, OcrSpaceService $ocrSpaceService)
    {
        $user = Auth::user();

        $apiData = $ocrSpaceService->readIdDocument($request->file('id_document'));

        $nationalIdNumber = $apiData['national_id_number'] ?? null;
        $dateOfBirth = $apiData['date_of_birth'] ?? null;
        $rawText = $apiData['raw_text'] ?? '';

        if (! $nationalIdNumber || ! $dateOfBirth) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_document' => 'OCR could not automatically detect a valid national ID and date of birth. Please upload a clearer, straight, well-lit image where the bottom ID number is sharp.',
                ])
                ->with('ocr_raw_text', $rawText)
                ->with('ocr_detected_id', $nationalIdNumber)
                ->with('ocr_detected_dob', $dateOfBirth);
        }

        $alreadyUsed = CitizenProfile::where('national_id_number', (string) $nationalIdNumber)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($alreadyUsed) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_document' => 'This national ID number is already linked to another citizen profile.',
                ])
                ->with('ocr_raw_text', $rawText)
                ->with('ocr_detected_id', $nationalIdNumber)
                ->with('ocr_detected_dob', $dateOfBirth);
        }

        DB::transaction(function () use ($request, $user, $nationalIdNumber, $dateOfBirth) {
            $profile = $user->citizenProfile;

            $address = $profile?->address ?: new Address();

            $address->address_line_1 = $request->input('address_line_1');
            $address->address_line_2 = $request->input('address_line_2');
            $address->city = $request->input('city');
            $address->region = $request->input('region');
            $address->postal_code = $request->input('postal_code');
            $address->country = $request->input('country');
            $address->save();

            $path = $request->file('id_document')->store('id-documents', 'public');

            if (! $profile) {
                $profile = new CitizenProfile();
                $profile->user_id = $user->id;
            }

            $profile->address_id = $address->id;

            /*
             | Save as string to preserve leading zeroes.
             */
            $profile->national_id_number = (string) $nationalIdNumber;

            $profile->date_of_birth = $dateOfBirth;
            $profile->id_document_path = $path;
            $profile->verification_status = 'verified';
            $profile->save();

            $user->phone = $request->input('phone_number');
            $user->save();
        });

        return redirect()
            ->route('citizen.profile.show')
            ->with('success', 'Your citizen profile has been verified successfully.');
    }
}