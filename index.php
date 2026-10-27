<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Grab the raw POST string input
$scrambledPayload = file_get_contents('php://input');
$secretKey = "MySuperSecretKey123"; 

// 2. Decode Base64
$encryptedStr = base64_decode($scrambledPayload);

// 3. Reverse the XOR operation
$jsonString = '';
$keyLength = strlen($secretKey);
if ($encryptedStr !== false) {
    for ($i = 0; $i < strlen($encryptedStr); $i++) {
        $jsonString .= $encryptedStr[$i] ^ $secretKey[$i % $keyLength];
    }
}

// 4. Convert back to an associative array
$formData = json_decode($jsonString, true);

if ($formData) {
    echo json_encode(["status" => "success", "message" => "Data safely decoded."]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid payload structure"]);
}
?>
