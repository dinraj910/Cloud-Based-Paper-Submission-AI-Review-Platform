<?php
/**
 * API endpoint to extract text from PDF
 * This extracts text from a PDF URL for AI chatbot analysis
 */

header('Content-Type: application/json');

if (!isset($_GET['url']) || empty($_GET['url'])) {
    echo json_encode(['success' => false, 'error' => 'PDF URL is required']);
    exit;
}

$pdfUrl = $_GET['url'];

// Function to extract text from PDF using pdftotext (if available) or fallback
function extractPdfText($url) {
    // Create a temporary file to store the PDF
    $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
    
    // Download the PDF
    $pdfContent = @file_get_contents($url);
    if ($pdfContent === false) {
        return ['success' => false, 'error' => 'Failed to download PDF'];
    }
    
    file_put_contents($tempFile, $pdfContent);
    
    $text = '';
    
    // Try using pdftotext command-line tool (most reliable)
    if (shell_exec('which pdftotext')) {
        $outputFile = $tempFile . '.txt';
        shell_exec("pdftotext " . escapeshellarg($tempFile) . " " . escapeshellarg($outputFile) . " 2>&1");
        
        if (file_exists($outputFile)) {
            $text = file_get_contents($outputFile);
            unlink($outputFile);
        }
    }
    
    // If pdftotext didn't work or produced empty text, use fallback
    if (empty(trim($text))) {
        $text = extractTextBasic($tempFile);
    }
    
    // Clean up
    unlink($tempFile);
    
    if (empty(trim($text))) {
        return ['success' => false, 'error' => 'Could not extract text from PDF. The PDF may be image-based or encrypted.'];
    }
    
    // Limit text to ~15K characters to avoid token limits
    if (strlen($text) > 15000) {
        $text = substr($text, 0, 15000) . '... [Text truncated for analysis]';
    }
    
    return ['success' => true, 'text' => $text, 'length' => strlen($text)];
}

// Basic PDF text extraction (fallback method)
function extractTextBasic($filename) {
    $content = file_get_contents($filename);
    $text = '';
    
    // Extract text between obj and endobj markers
    if (preg_match_all("/\(([^)]+)\)/", $content, $matches)) {
        $text = implode(' ', $matches[1]);
    }
    
    // Also try to extract from stream objects
    if (preg_match_all("/stream\s*(.+?)\s*endstream/s", $content, $matches)) {
        foreach ($matches[1] as $stream) {
            // Try to decompress if it's a compressed stream
            $decompressed = @gzuncompress($stream);
            if ($decompressed !== false) {
                if (preg_match_all("/\(([^)]+)\)/", $decompressed, $textMatches)) {
                    $text .= ' ' . implode(' ', $textMatches[1]);
                }
            }
        }
    }
    
    // Clean up the text
    $text = str_replace(['\\n', '\\r', '\\t'], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    
    return trim($text);
}

// Extract the PDF text
$result = extractPdfText($pdfUrl);

echo json_encode($result);
