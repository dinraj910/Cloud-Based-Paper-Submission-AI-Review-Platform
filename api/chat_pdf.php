<?php
/**
 * API endpoint to chat with PDF using AI
 * Uses Groq's free LLM API with Llama model
 */

header('Content-Type: application/json');

// Get the posted data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['question']) || !isset($input['pdfText'])) {
    echo json_encode(['success' => false, 'error' => 'Question and PDF text are required']);
    exit;
}

$question = $input['question'];
$pdfText = $input['pdfText'];
$conversationHistory = $input['history'] ?? [];

// Load API key
include __DIR__ . '/../config/ai.php';

if (empty($GROQ_API_KEY)) {
    echo json_encode([
        'success' => false, 
        'error' => 'AI API key not configured. Please set GROQ_API_KEY in config/ai.php'
    ]);
    exit;
}

// Prepare messages for the AI
$messages = [
    [
        'role' => 'system',
        'content' => "You are a helpful research assistant. You have access to a research paper and can answer questions about it. Here is the content of the paper:\n\n" . substr($pdfText, 0, 12000) . "\n\nPlease answer questions based on this content. If the answer is not in the document, say so clearly."
    ]
];

// Add conversation history
foreach ($conversationHistory as $msg) {
    $messages[] = $msg;
}

// Add current question
$messages[] = [
    'role' => 'user',
    'content' => $question
];

// Call Groq API
$response = callGroqAPI($messages, $GROQ_API_KEY);

if ($response['success']) {
    echo json_encode([
        'success' => true,
        'answer' => $response['answer'],
        'model' => $response['model'] ?? 'llama-3.3-70b-versatile'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $response['error'] ?? 'Failed to get AI response'
    ]);
}

function callGroqAPI($messages, $apiKey) {
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    
    $data = [
        'model' => 'llama-3.3-70b-versatile', // Free Groq model
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1024,
        'top_p' => 1,
        'stream' => false
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'cURL Error: ' . $error];
    }
    
    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        return ['success' => false, 'error' => 'API Error: ' . ($errorData['error']['message'] ?? 'Unknown error')];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return [
            'success' => true,
            'answer' => $result['choices'][0]['message']['content'],
            'model' => $result['model'] ?? null
        ];
    }
    
    return ['success' => false, 'error' => 'Invalid API response'];
}
