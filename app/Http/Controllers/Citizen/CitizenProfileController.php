<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\VerifyCitizenProfileRequest;
use App\Models\Address;
use App\Models\CitizenProfile;
use App\Services\OcrSpaceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $documentHash = hash_file('sha256', $request->file('id_document')->getRealPath());

        $sameDocumentUsed = CitizenProfile::where('id_document_hash', $documentHash)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($sameDocumentUsed) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_document' => 'This national ID document is already linked to another citizen profile.',
                ]);
        }

        $apiData = $ocrSpaceService->readIdDocument($request->file('id_document'));

        $nationalIdNumber = $this->cleanNationalId($apiData['national_id_number'] ?? null);
        $dateOfBirth = $apiData['date_of_birth'] ?? null;
        $rawText = $apiData['raw_text'] ?? '';

        if (! $nationalIdNumber || ! $dateOfBirth) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_document' => $apiData['message'] ?? 'OCR could not automatically detect a valid national ID number and date of birth.',
                ])
                ->with('ocr_raw_text', $rawText)
                ->with('ocr_detected_id', $nationalIdNumber)
                ->with('ocr_detected_dob', $dateOfBirth);
        }

        $alreadyUsed = CitizenProfile::where('national_id_number', $nationalIdNumber)
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

        DB::transaction(function () use ($request, $user, $nationalIdNumber, $dateOfBirth, $documentHash) {
            $profile = $user->citizenProfile()
                ->with('address')
                ->first();

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
            } elseif ($profile->id_document_path) {
                Storage::disk('public')->delete($profile->id_document_path);
            }

            $profile->address_id = $address->id;
            $profile->national_id_number = $nationalIdNumber;
            $profile->date_of_birth = $dateOfBirth;
            $profile->id_document_path = $path;
            $profile->id_document_hash = $documentHash;
            $profile->verification_status = 'verified';
            $profile->save();

            $user->phone = $request->input('phone_number');
            $user->save();
        });

        return redirect()
            ->route('citizen.profile.show')
            ->with('success', 'Your citizen profile has been verified successfully.');
    }

    public function viewIdDocument()
    {
        $user = Auth::user();

        $profile = $user->citizenProfile()->first();

        if (! $profile || ! $profile->id_document_path) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($profile->id_document_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($profile->id_document_path)
        );
    }

    private function cleanNationalId(?string $nationalId): ?string
    {
        if (! $nationalId) {
            return null;
        }

        $nationalId = strtr($nationalId, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',

            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);

        $nationalId = preg_replace('/\D/u', '', $nationalId);

        if (! $nationalId || strlen($nationalId) < 7 || strlen($nationalId) > 14) {
            return null;
        }

        return $nationalId;
    }
}