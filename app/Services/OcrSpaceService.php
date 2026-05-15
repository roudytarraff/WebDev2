<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrSpaceService
{
    public function readIdDocument(UploadedFile $file): array
    {
        $fullText = $this->extractText($file);
        $bottomText = $this->extractBottomText($file);

        $combinedText = $this->cleanText(
            $this->normalizeArabicDigits($fullText . "\n" . $bottomText)
        );

        if (empty($combinedText)) {
            return [
                'success' => false,
                'message' => 'OCR.space could not read any text from the document.',
                'national_id_number' => null,
                'date_of_birth' => null,
                'raw_text' => '',
            ];
        }

        $dateOfBirth = $this->findDateOfBirth($combinedText);
        $nationalId = $this->findNationalId($combinedText);

        return [
            'success' => $nationalId !== null && $dateOfBirth !== null,
            'message' => ($nationalId !== null && $dateOfBirth !== null)
                ? 'ID document read successfully.'
                : 'OCR could not automatically detect a valid national ID and date of birth. Please upload a clearer, straight, well-lit image where the bottom ID number is sharp.',
            'national_id_number' => $nationalId,
            'date_of_birth' => $dateOfBirth,
            'raw_text' => $combinedText,
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

    public function extractBottomText(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            Log::warning('GD extension is not enabled. Bottom crop OCR was skipped.');
            return '';
        }

        $imagePath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
            return '';
        }

        try {
            if ($mimeType === 'image/png') {
                $source = imagecreatefrompng($imagePath);
            } else {
                $source = imagecreatefromjpeg($imagePath);
            }

            if (! $source) {
                Log::warning('Could not create image for bottom crop OCR.');
                return '';
            }

            $width = imagesx($source);
            $height = imagesy($source);

            /*
             | The Lebanese ID number is usually near the bottom.
             | We crop the bottom part and enlarge it to help OCR detect it.
             */
            $cropY = (int) ($height * 0.45);
            $cropHeight = $height - $cropY;

            $cropped = imagecrop($source, [
                'x' => 0,
                'y' => $cropY,
                'width' => $width,
                'height' => $cropHeight,
            ]);

            if (! $cropped) {
                imagedestroy($source);
                return '';
            }

            $scale = 4;
            $resized = imagecreatetruecolor($width * $scale, $cropHeight * $scale);

            imagecopyresampled(
                $resized,
                $cropped,
                0,
                0,
                0,
                0,
                $width * $scale,
                $cropHeight * $scale,
                $width,
                $cropHeight
            );

            ob_start();
            imagejpeg($resized, null, 95);
            $enhancedImageData = ob_get_clean();

            imagedestroy($source);
            imagedestroy($cropped);
            imagedestroy($resized);

            if (! $enhancedImageData) {
                return '';
            }

            $base64Image = 'data:image/jpeg;base64,' . base64_encode($enhancedImageData);

            return $this->sendToOcrSpace($base64Image, 'bottom_crop');
        } catch (\Throwable $e) {
            Log::error('Bottom crop OCR failed.', [
                'message' => $e->getMessage(),
            ]);

            return '';
        }
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

        if (! $apiKey) {
            Log::error('ID verification OCR API key is missing.');
            return '';
        }

        try {
            $response = Http::asForm()
                ->timeout(90)
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

            if (! $response->successful()) {
                Log::error('OCR.space HTTP request failed.', [
                    'source' => $sourceLabel,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return '';
            }

            $data = $response->json();

            if (! is_array($data)) {
                Log::error('OCR.space returned invalid JSON.', [
                    'source' => $sourceLabel,
                    'body' => $response->body(),
                ]);

                return '';
            }

            if (! empty($data['IsErroredOnProcessing'])) {
                Log::error('OCR.space processing error.', [
                    'source' => $sourceLabel,
                    'response' => $data,
                ]);

                return '';
            }

            $text = '';

            if (! empty($data['ParsedResults']) && is_array($data['ParsedResults'])) {
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

        $candidates = [];

        foreach ($lines as $lineIndex => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $lineWithoutDates = $this->removeDateLikeValues($line);

            /*
             | Allow 7 to 12 digits because OCR may miss leading zeroes.
             | Example:
             | OCR reads: 077317146
             | Correct:   000077317146
             */
            preg_match_all('/(?<!\d)(?:\d[ \t\-\.\٬]*){7,12}(?!\d)/u', $lineWithoutDates, $matches);

            foreach ($matches[0] ?? [] as $match) {
                $number = preg_replace('/\D/u', '', $match);

                if (! $number) {
                    continue;
                }

                if ($this->looksLikeCompactDate($number)) {
                    continue;
                }

                if (preg_match('/^(19|20)\d{2}/', $number)) {
                    continue;
                }

                $repaired = $this->repairPartialLebaneseId($number);

                if (! $repaired || ! $this->isValidNationalIdCandidate($repaired)) {
                    continue;
                }

                $candidates[] = [
                    'number' => $repaired,
                    'original' => $number,
                    'line_index' => $lineIndex,
                    'line' => $line,
                    'length' => strlen($repaired),
                    'starts_with_zero' => str_starts_with($repaired, '0'),
                ];
            }
        }

        if (! empty($candidates)) {
            usort($candidates, function ($a, $b) {
                if ($a['starts_with_zero'] !== $b['starts_with_zero']) {
                    return $b['starts_with_zero'] <=> $a['starts_with_zero'];
                }

                if ($a['length'] !== $b['length']) {
                    return $b['length'] <=> $a['length'];
                }

                return $b['line_index'] <=> $a['line_index'];
            });

            Log::info('National ID candidates detected.', [
                'candidates' => $candidates,
                'selected' => $candidates[0]['number'],
                'original' => $candidates[0]['original'],
            ]);

            return $candidates[0]['number'];
        }

        Log::warning('No valid national ID detected in OCR text.', [
            'text' => $text,
        ]);

        return null;
    }

    private function repairPartialLebaneseId(string $number): ?string
    {
        $number = preg_replace('/\D/u', '', $number);

        if (! $number) {
            return null;
        }

        /*
         | Final project rule:
         | Store Lebanese national ID as exactly 12 digits.
         |
         | Never convert it to integer, because leading zeroes are part of the ID.
         |
         | Example:
         | OCR reads: 077317146
         | Saved as:  000077317146
         */
        if (strlen($number) >= 7 && strlen($number) < 12) {
            return str_pad($number, 12, '0', STR_PAD_LEFT);
        }

        if (strlen($number) === 12) {
            return $number;
        }

        return null;
    }

    private function findDateOfBirth(string $text): ?string
    {
        $text = $this->normalizeArabicDigits($text);
        $text = $this->cleanText($text);

        preg_match('/\b(\d{4})[\s\/\-\.]+(\d{1,2})[\s\/\-\.]+(\d{1,2})\b/u', $text, $matches);

        if (! empty($matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);

            if ($this->isValidDate($year, $month, $day)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        preg_match('/\b(\d{1,2})[\s\/\-\.]+(\d{1,2})[\s\/\-\.]+(\d{4})\b/u', $text, $matches);

        if (! empty($matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];

            if ($this->isValidDate($year, $month, $day)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        preg_match('/\b((?:19|20)\d{2})(\d{2})(\d{2})\b/u', $text, $matches);

        if (! empty($matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];

            if ($this->isValidDate($year, $month, $day)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        Log::warning('No date of birth detected in OCR text.', [
            'text' => $text,
        ]);

        return null;
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
        if (! preg_match('/^((?:19|20)\d{2})(\d{2})(\d{2})$/', $number, $matches)) {
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
        return preg_match('/^\d{12}$/', $number) === 1;
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