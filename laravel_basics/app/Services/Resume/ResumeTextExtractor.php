<?php

namespace App\Services\Resume;

use Illuminate\Support\Facades\Log;

class ResumeTextExtractor
{
    /**
     * Extract plain text from a PDF file.
     *
     * @param string $filePath
     * @return string
     * @throws \RuntimeException
     */
    public function extract(string $filePath): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("PDF file not found or unreadable at path: {$filePath}");
        }

        // Validate PDF binary header magic bytes (%PDF)
        $handle = @fopen($filePath, 'rb');
        $header = $handle ? fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }

        if ($header !== '%PDF') {
            throw new \InvalidArgumentException("Invalid PDF document: missing %PDF binary signature.");
        }

        // Tier 1: Try Python extractor (pdfplumber / pdfminer)
        $pythonText = $this->extractViaPython($filePath);
        if (!empty(trim($pythonText))) {
            return $this->sanitizeText($pythonText);
        }

        // Tier 2: Try native PHP stream parsing
        $phpText = $this->extractViaNativePhp($filePath);
        if (!empty(trim($phpText))) {
            return $this->sanitizeText($phpText);
        }

        return '';
    }

    /**
     * Extract PDF text using Python helper script.
     *
     * @param string $filePath
     * @return string
     */
    protected function extractViaPython(string $filePath): string
    {
        $possiblePaths = [
            base_path('scripts/extract_pdf_text.py'),
            base_path('../scripts/extract_pdf_text.py'),
            app_path('../scripts/extract_pdf_text.py'),
        ];

        $scriptPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $scriptPath = realpath($path);
                break;
            }
        }

        if (!$scriptPath) {
            return '';
        }

        $cmd = 'python ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($filePath);
        $stdout = shell_exec($cmd);

        if (empty($stdout)) {
            return '';
        }

        $trimmed = trim($stdout);
        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');

        if ($start !== false && $end !== false) {
            $jsonStr = substr($trimmed, $start, $end - $start + 1);
            $parsed = json_decode($jsonStr, true);
            if (is_array($parsed) && !empty($parsed['text'])) {
                return (string) $parsed['text'];
            }
        }

        return '';
    }

    /**
     * Native PHP fallback for basic text stream extraction from unencrypted PDFs.
     *
     * @param string $filePath
     * @return string
     */
    protected function extractViaNativePhp(string $filePath): string
    {
        $content = @file_get_contents($filePath);
        if ($content === false || empty($content)) {
            return '';
        }

        $extracted = '';

        // Extract text inside parentheses in PDF BT...ET blocks
        if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $content, $matches)) {
            $extracted .= implode(' ', $matches[1]) . ' ';
        }

        // Also check Tj / TJ array text
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $arrayMatches)) {
            foreach ($arrayMatches[1] as $arr) {
                if (preg_match_all('/\((.*?)\)/s', $arr, $sub)) {
                    $extracted .= implode('', $sub[1]) . ' ';
                }
            }
        }

        // Check compressed flate decode streams
        if (empty(trim($extracted)) && function_exists('gzuncompress')) {
            if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $streamMatches)) {
                foreach ($streamMatches[1] as $stream) {
                    $uncompressed = @gzuncompress($stream);
                    if ($uncompressed !== false) {
                        if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $uncompressed, $m)) {
                            $extracted .= implode(' ', $m[1]) . ' ';
                        }
                    }
                }
            }
        }

        return $extracted;
    }

    /**
     * Clean and normalize extracted text string.
     *
     * @param string $text
     * @return string
     */
    protected function sanitizeText(string $text): string
    {
        // Convert encoding to UTF-8
        $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // Replace escape sequences and control characters
        $clean = preg_replace('/[^\P{C}\n\r\t]/u', ' ', $clean);
        // Replace excessive whitespace
        $clean = preg_replace('/[ \t]+/', ' ', $clean);
        $clean = preg_replace('/[\r\n]{3,}/', "\n\n", $clean);
        return trim($clean);
    }
}

