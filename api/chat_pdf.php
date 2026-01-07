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

if (empty($GROQ_API_KEY) || $GROQ_API_KEY === 'YOUR_GROQ_API_KEY_HERE') {
    echo json_encode([
        'success' => false, 
        'error' => 'AI API key not configured. Please set GROQ_API_KEY in config/ai.php. Get a free key at https://console.groq.com/keys'
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

/**
 * Call Groq API with the messages
 */
function callGroqAPI($messages, $apiKey) {
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    
    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1024,
        'top_p' => 1,
        'stream' => false
    ];
    
    $options = [
        'http' => [
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            'method' => 'POST',
            'content' => json_encode($data),
            'timeout' => 30
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        return [
            'success' => false,
            'error' => 'Failed to connect to AI service. Please check your API key and network connection.'
        ];
    }
    
    $response = json_decode($result, true);
    
    if (isset($response['choices'][0]['message']['content'])) {
        return [
            'success' => true,
            'answer' => $response['choices'][0]['message']['content'],
            'model' => $response['model'] ?? 'unknown'
        ];
    } else {
        return [
            'success' => false,
            'error' => $response['error']['message'] ?? 'Unknown error from AI service'
        ];
    }
}
