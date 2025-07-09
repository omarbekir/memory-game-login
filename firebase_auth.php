<?php
// Set the content type to JSON so the AIR app understands the response.
header('Content-Type: application/json');

// Get the API key from the request URL (e.g., your-server.com/firebase_auth.php?key=AIzaSy...)
$apiKey = isset($_GET['key']) ? $_GET['key'] : '';

if (empty($apiKey)) {
    // Stop if the API key wasn't sent from the app.
    http_response_code(400);
    echo json_encode(['error' => 'API key is missing from proxy request.']);
    exit;
}

// The official Google API URL.
$googleApiUrl = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . $apiKey;

// Get the raw POST data sent from the AIR application.
$postData = file_get_contents('php://input');

// Use cURL to forward the request to Google's server.
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);

// Execute the request.
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Send Google's exact response and status code back to your AIR app.
if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Proxy could not connect to Google API: ' . curl_error($ch)]);
} else {
    http_response_code($httpcode);
    echo $response;
}
?>
