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
    // ---- DATABASE INTEGRATION CONFIGURATION ----
    
    // Replace these values with your actual database credentials
    $db_host = 'YOUR_DATABASE_HOST';       // e.g. 'aws.connect.psdb.cloud' or 'localhost'
    $db_name = 'YOUR_DATABASE_NAME';       // Your database name
    $db_user = 'YOUR_DATABASE_USERNAME';   // Your database user
    $db_pass = 'YOUR_DATABASE_PASSWORD';   // Your database password
    $db_port = '3306';                     // Standard MySQL port

    // Build the connection string (DSN)
    $dsn = "mysql:host=$db_host;dbname=$db_name;port=$db_port;charset=utf8mb4";
    
    // PDO options for security and error tracking
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Connect to the database
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);

        // Extract and fallback fields safely
        $username = isset($formData['username']) ? $formData['username'] : 'N/A';
        $email    = isset($formData['email']) ? $formData['email'] : 'N/A';
        $location = isset($formData['location']) ? $formData['location'] : 'N/A';

        // Write the SQL statement using placeholders to prevent SQL Injection
        $sql = "INSERT INTO form_submissions (username, email, location) VALUES (:username, :email, :location)";
        $stmt = $pdo->prepare($sql);
        
        // Execute and bind variables securely
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':location' => $location
        ]);

        // Success response
        echo json_encode([
            "status" => "success", 
            "message" => "Data safely decoded and stored in the database!"
        ]);

    } catch (\PDOException $e) {
        // Database Error Handle: Don't leak raw credentials or system paths in production
        http_response_code(500);
        echo json_encode([
            "status" => "error", 
            "message" => "Database Connection Failed: " . $e->getMessage()
        ]);
    }
    // --------------------------------------------

} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid payload structure"]);
}
?>
