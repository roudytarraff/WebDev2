<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrSpaceService
{
    public function readIdDocument(UploadedFile $file): array
    {
        set_time_limit(120);

        /*
         |--------------------------------------------------------------------------
         | One OCR request only
         |--------------------------------------------------------------------------
         | We use only the full image OCR request to avoid the 60-second timeout.
         |--------------------------------------------------------------------------
         */

        $text = $this->extractText($file);

        $text = $this->cleanText(
            $this->normalizeArabicDigits($text)
        );

        if (empty($text)) {
            return [
                'success' => false,
                'message' => 'OCR.space could not read any text from the document.',
                'national_id_number' => null,
                'date_of_birth' => null,
                'raw_text' => '',
            ];
        }

        $dateOfBirth = $this->findDateOfBirth($text);
        $nationalId = $this->findNationalId($text);

        return [
            'success' => $nationalId !== null && $dateOfBirth !== null,
            'message' => ($nationalId !== null && $dateOfBirth !== null)
                ? 'ID document read successfully.'
                : 'OCR could not automatically detect a valid national ID and date of birth. Please upload a clearer, straight, well-lit image where the bottom ID number and birth date are sharp.',
            'national_id_number' => $nationalId,
            'date_of_birth' => $dateOfBirth,
            'raw_text' => $text,
        ];
    }

    public function extractText(UploadedFile $file): string
    {
        $imageData = file_get_contents($file->getRealPath());

        if ($imageData === false) {
            Log::error('Could not read uploaded ID document file.');
            return '';
        }

        $base64Image = 'data:' . $file->getMimeType() . ';base64,' . base64_encode($imageData);

        return $this->sendToOcrSpace($base64Image, 'full_document');
    }

    private function sendToOcrSpace(string $base64Image, string $sourceLabel): string
    {
        $demoMode = config('services.id_verification.demo_mode', false);
        $apiKey = config('services.id_verification.key');
        $apiUrl = config('services.id_verification.url', 'https://api.ocr.space/parse/image');

        if ($demoMode) {
            Log::warning('ID verification demo mode is enabled. Real OCR request was skipped.');
            return '';
        }

        if (!$apiKey) {
            Log::error('ID verification OCR API key is missing.');
            return '';
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(10)
                ->timeout(30)
                ->post($apiUrl, [
                    'apikey' => $apiKey,
                    'language' => 'auto',
                    'OCREngine' => '3',
                    'scale' => 'true',
                    'detectOrientation' => 'true',
                    'isTable' => 'false',
                    'isOverlayRequired' => 'false',
                    'base64Image' => $base64Image,
                ]);

            Log::info('OCR.space raw response', [
                'source' => $sourceLabel,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::error('OCR.space HTTP request failed.', [
                    'source' => $sourceLabel,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return '';
            }

            $data = $response->json();

            if (!is_array($data)) {
                Log::error('OCR.space returned invalid JSON.', [
                    'source' => $sourceLabel,
                    'body' => $response->body(),
                ]);

                return '';
            }

            if (!empty($data['IsErroredOnProcessing'])) {
                Log::error('OCR.space processing error.', [
                    'source' => $sourceLabel,
                    'response' => $data,
                ]);

                return '';
            }

            $text = '';

            if (!empty($data['ParsedResults']) && is_array($data['ParsedResults'])) {
                foreach ($data['ParsedResults'] as $result) {
                    $text .= "\n" . ($result['ParsedText'] ?? '');
                }
            }

            $text = $this->normalizeArabicDigits($text);
            $text = $this->cleanText($text);

            Log::info('OCR.space extracted text.', [
                'source' => $sourceLabel,
                'text' => $text,
            ]);

            return $text;
        } catch (\Throwable $e) {
            Log::error('OCR.space exception.', [
                'source' => $sourceLabel,
                'message' => $e->getMessage(),
            ]);

            return '';
        }
    }

    public function findNationalId(string $text): ?string
    {
        $text = $this->normalizeArabicDigits($text);
        $text = $this->cleanText($text);

        $lines = preg_split('/\n/u', $text) ?: [];
        $exactCandidates = [];
        $partialCandidates = [];

        foreach ($lines as $lineIndex => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $lineWithoutDates = $this->removeDateLikeValues($line);

            /*
             |--------------------------------------------------------------------------
             | Exact ID candidate: 10 or 11 digits
             |--------------------------------------------------------------------------
             */

            preg_match_all(
                '/(?<!\d)(?:\d[ \t\-\.\٬]*){10,11}(?!\d)/u',
                $lineWithoutDates,
                $matchesExact
            );

            foreach ($matchesExact[0] ?? [] as $match) {
                $number = preg_replace('/\D/u', '', $match);

                if (!$this->isValidNationalIdCandidate($number)) {
                    continue;
                }

                if ($this->looksLikeCompactDate($number)) {
                    continue;
                }

                if (preg_match('/^(19|20)\d{2}/', $number)) {
                    continue;
                }

                $exactCandidates[] = [
                    'number' => $number,
                    'line_index' => $lineIndex,
                    'line' => $line,
                ];
            }

            /*
             |--------------------------------------------------------------------------
             | Partial ID candidate
             |--------------------------------------------------------------------------
             | OCR sometimes drops leading zeros from Lebanese ID numbers.
             |--------------------------------------------------------------------------
             */

            preg_match_all(
                '/(?<!\d)(?:\d[ \t\-\.\٬]*){7,10}(?!\d)/u',
                $lineWithoutDates,
                $matchesPartial
            );

            foreach ($matchesPartial[0] ?? [] as $match) {
                $number = preg_replace('/\D/u', '', $match);

                if (!$number) {
                    continue;
                }

                if ($this->looksLikeCompactDate($number)) {
                    continue;
                }

                if (preg_match('/^(19|20)\d{2}/', $number)) {
                    continue;
                }

                $repaired = $this->repairPartialLebaneseId($number);

                if ($repaired && $this->isValidNationalIdCandidate($repaired)) {
                    $partialCandidates[] = [
                        'number' => $repaired,
                        'original' => $number,
                        'line_index' => $lineIndex,
                        'line' => $line,
                    ];
                }
            }
        }

        if (!empty($exactCandidates)) {
            usort($exactCandidates, function ($a, $b) {
                $aStartsWithZero = str_starts_with($a['number'], '0') ? 1 : 0;
                $bStartsWithZero = str_starts_with($b['number'], '0') ? 1 : 0;

                if ($aStartsWithZero !== $bStartsWithZero) {
                    return $bStartsWithZero <=> $aStartsWithZero;
                }

                return $b['line_index'] <=> $a['line_index'];
            });

            Log::info('National ID exact candidates detected.', [
                'candidates' => $exactCandidates,
                'selected' => $exactCandidates[0]['number'],
            ]);

            return $exactCandidates[0]['number'];
        }

        if (!empty($partialCandidates)) {
            usort($partialCandidates, function ($a, $b) {
                return $b['line_index'] <=> $a['line_index'];
            });

            Log::warning('National ID repaired from partial OCR result.', [
                'candidates' => $partialCandidates,
                'selected' => $partialCandidates[0]['number'],
                'original' => $partialCandidates[0]['original'],
            ]);

            return $partialCandidates[0]['number'];
        }

        Log::warning('No valid national ID detected in OCR text.', [
            'text' => $text,
        ]);

        return null;
    }

    private function repairPartialLebaneseId(string $number): ?string
    {
    $number = preg_replace('/\D/u', '', $number);

    if (!$number) {
        return null;
    }

    /*
     | If OCR already gives 10, 11, or 12 digits, keep it.
     */
    if (strlen($number) >= 10 && strlen($number) <= 12) {
        return $number;
    }

    /*
     | OCR.space often removes leading zeros.
     | Your case:
     | OCR result: 77317146
     | Correct ID: 000077317146
     */
    if (strlen($number) >= 7 && strlen($number) <= 9) {
        return str_pad($number, 12, '0', STR_PAD_LEFT);
    }

    return null;
    }
    
    private function findDateOfBirth(string $text): ?string
    {
        $text = $this->normalizeArabicDigits($text);
        $text = $this->cleanText($text);

        $candidates = [];

        /*
         |--------------------------------------------------------------------------
         | 1. Dates near Arabic birth labels
         |--------------------------------------------------------------------------
         | Examples:
         | تاريخ الولادة: 2005/11/10
         | الولادة: 2005/09/07
         |--------------------------------------------------------------------------
         */

        preg_match_all(
            '/(?:تاريخ\s*الولادة|الولادة|تولد|مواليد)[^\d]{0,40}((?:19|20)\d{2})[\s\/\-\.]+(\d{1,2})[\s\/\-\.]+(\d{1,2})/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $date = $this->buildValidDate($match[1], $match[2], $match[3]);

            if ($date) {
                $candidates[] = [
                    'date' => $date,
                    'score' => 100,
                    'source' => 'arabic_label_yyyy_mm_dd',
                ];
            }
        }

        /*
         |--------------------------------------------------------------------------
         | 2. Generic YYYY/MM/DD
         |--------------------------------------------------------------------------
         */

        preg_match_all(
            '/\b((?:19|20)\d{2})[\s\/\-\.]+(\d{1,2})[\s\/\-\.]+(\d{1,2})\b/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $date = $this->buildValidDate($match[1], $match[2], $match[3]);

            if ($date) {
                $candidates[] = [
                    'date' => $date,
                    'score' => 80,
                    'source' => 'yyyy_mm_dd',
                ];
            }
        }

        /*
         |--------------------------------------------------------------------------
         | 3. Generic DD/MM/YYYY
         |--------------------------------------------------------------------------
         */

        preg_match_all(
            '/\b(\d{1,2})[\s\/\-\.]+(\d{1,2})[\s\/\-\.]+((?:19|20)\d{2})\b/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $date = $this->buildValidDate($match[3], $match[2], $match[1]);

            if ($date) {
                $candidates[] = [
                    'date' => $date,
                    'score' => 60,
                    'source' => 'dd_mm_yyyy',
                ];
            }
        }

        /*
         |--------------------------------------------------------------------------
         | 4. Compact YYYYMMDD
         |--------------------------------------------------------------------------
         */

        preg_match_all(
            '/\b((?:19|20)\d{2})(\d{2})(\d{2})\b/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $date = $this->buildValidDate($match[1], $match[2], $match[3]);

            if ($date) {
                $candidates[] = [
                    'date' => $date,
                    'score' => 40,
                    'source' => 'compact_yyyymmdd',
                ];
            }
        }

        if (empty($candidates)) {
            Log::warning('No date of birth detected in OCR text.', [
                'text' => $text,
            ]);

            return null;
        }

        usort($candidates, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        Log::info('Date of birth candidates detected.', [
            'candidates' => $candidates,
            'selected' => $candidates[0]['date'],
        ]);

        return $candidates[0]['date'];
    }

    private function buildValidDate(string $year, string $month, string $day): ?string
    {
        $year = trim($year);
        $month = trim($month);
        $day = trim($day);

        /*
         |--------------------------------------------------------------------------
         | Fix common OCR year mistake
         |--------------------------------------------------------------------------
         | Sometimes OCR reads 2005 as 1005.
         |--------------------------------------------------------------------------
         */

        if (strlen($year) === 4 && str_starts_with($year, '1')) {
            $possibleYear = '2' . substr($year, 1);

            if ((int) $possibleYear <= (int) date('Y')) {
                $year = $possibleYear;
            }
        }

        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);

        if (!$this->isValidDate($year, $month, $day)) {
            return null;
        }

        return "{$year}-{$month}-{$day}";
    }

    private function removeDateLikeValues(string $text): string
    {
        $text = preg_replace('/\b\d{4}[\s\/\-\.]+\d{1,2}[\s\/\-\.]+\d{1,2}\b/u', ' ', $text);
        $text = preg_replace('/\b\d{1,2}[\s\/\-\.]+\d{1,2}[\s\/\-\.]+\d{4}\b/u', ' ', $text);
        $text = preg_replace('/\b(?:19|20)\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])\b/u', ' ', $text);

        return trim($text ?? '');
    }

    private function looksLikeCompactDate(string $number): bool
    {
        if (!preg_match('/^((?:19|20)\d{2})(\d{2})(\d{2})$/', $number, $matches)) {
            return false;
        }

        return $this->isValidDate($matches[1], $matches[2], $matches[3]);
    }

    private function isValidDate(string $year, string $month, string $day): bool
    {
        $yearInt = (int) $year;

        if ($yearInt < 1900 || $yearInt > (int) date('Y')) {
            return false;
        }

        return checkdate((int) $month, (int) $day, $yearInt);
    }

    private function isValidNationalIdCandidate(string $number): bool
    {
        return preg_match('/^\d{10,12}$/', $number) === 1;
    }

    private function normalizeArabicDigits(string $text): string
    {
        return strtr($text, [
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

            '٬' => '',
            '٫' => '.',
            '／' => '/',
            '⁄' => '/',
            '∕' => '/',
        ]);
    }

    private function cleanText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[^\S\n]+/u', ' ', $text);
        $text = preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}]/u', '', $text);

        return trim($text);
    }
}