<?php
header('Content-Type: text/plain');

// Intenta obtener la clave de la API
$apiKey = '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';

if ($apiKey) {
    echo "La clave de API está configurada correctamente: " . substr($apiKey, 0, 5) . "*****";
} else {
    echo "Error: La clave de API no está configurada.";
}
?>
