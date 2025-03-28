<?php
header('Content-Type: application/json');

// Define la clave de API directamente en el código
$apiKey = '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'; // Reemplaza con tu clave de API real

if (!$apiKey) {
    echo json_encode(['reply' => 'Error de configuración: clave de API no encontrada.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || empty(trim($input['message']))) {
    echo json_encode(['reply' => 'Por favor, escribe un mensaje válido.']);
    error_log('Mensaje inválido recibido: ' . json_encode($input));
    exit;
}

$message = trim($input['message']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'gpt-3.5-turbo', // Cambia al modelo GPT-3.5-turbo si no tienes acceso a GPT-4
    'messages' => [['role' => 'user', 'content' => $message]]
]));

$maxRetries = 5; // Aumenta el número de reintentos
$retryDelay = 5; // Aumenta el retraso entre reintentos a 5 segundos
$attempts = 0;

do {
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 429) {
        error_log('HTTP 429: Demasiadas solicitudes. Reintentando en ' . $retryDelay . ' segundos...');
        sleep($retryDelay);
        $attempts++;
    } else {
        break;
    }
} while ($attempts < $maxRetries);

curl_close($ch);

if ($httpCode === 429) {
    echo json_encode(['reply' => 'Demasiadas solicitudes. Por favor, intenta nuevamente más tarde.']);
    exit;
}

if (curl_errno($ch)) {
    error_log('cURL error: ' . curl_error($ch)); // Registrar el error en el log del servidor
    echo json_encode(['reply' => 'Error al conectar con el servidor. Inténtalo más tarde.']);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log('HTTP error: ' . $httpCode . ' - Response: ' . $response); // Registrar el error HTTP
    echo json_encode(['reply' => 'Hubo un problema con la solicitud. Código HTTP: ' . $httpCode]);
    exit;
}

$responseData = json_decode($response, true);

if (isset($responseData['choices'][0]['message']['content'])) {
    $reply = $responseData['choices'][0]['message']['content'];
    echo json_encode(['reply' => $reply]);
} else {
    error_log('API response error: ' . $response); // Registrar la respuesta inesperada
    echo json_encode(['reply' => 'No se pudo obtener una respuesta válida de la API.']);
}
?>
