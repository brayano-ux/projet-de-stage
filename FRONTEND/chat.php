<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$apiKey = '1oeIvHIUXjBpZYUtbuWxwTJTWb5uaHFH'; // 🔑 Remplace ici

// Récupère les messages envoyés par le front
$body = json_decode(file_get_contents('php://input'), true);
$messages = $body['messages'] ?? [];

// Message système pour personnaliser le comportement
array_unshift($messages, [
    'role'    => 'system',
    'content' => 'Tu es un assistant service client professionnel et sympathique. Réponds en français.'
]);

// Appel à l'API Mistral
$payload = json_encode([
    'model'    => 'mistral-small-latest', // ou mistral-large-latest
    'messages' => $messages,
    'max_tokens' => 500
]);

$ch = curl_init('https://api.mistral.ai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$data  = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? 'Erreur API.';

echo json_encode(['reply' => $reply]);