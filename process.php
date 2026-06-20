<?php
header("Content-Type: application/json; charset=UTF-8");

// Database credentials
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = 'root'; // MAMP default password
$dbname = 'portfolio_db';

try {
    // Try to connect with MAMP default password 'root'
    $conn = new PDO("mysql:host=$host;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    try {
        // Fallback: Try with empty password (common for XAMPP or custom setups)
        $password = '';
        $conn = new PDO("mysql:host=$host;port=$port", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "เชื่อมต่อฐานข้อมูลล้มเหลว (Database Connection Failed): " . $e2->getMessage()
        ]);
        exit;
    }
}

// 1. Auto-create database if not exists
try {
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE `$dbname`");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "ไม่สามารถสร้างฐานข้อมูลได้ (Database Creation Failed): " . $e->getMessage()
    ]);
    exit;
}

// 2. Auto-create table if not exists
try {
    $table_sql = "
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $conn->exec($table_sql);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "ไม่สามารถสร้างตารางในฐานข้อมูลได้ (Table Creation Failed): " . $e->getMessage()
    ]);
    exit;
}

// Determine Action
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    // Action: Fetch messages
    try {
        $stmt = $conn->prepare("SELECT id, name, email, subject, message, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "data" => $messages
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "ดึงข้อมูลล้มเหลว (Fetch Failed): " . $e->getMessage()
        ]);
    }
    exit;
} elseif ($request_method === 'POST') {
    // Action: Save message

    // Read input data (support both raw JSON and normal POST data)
    $input_data = json_decode(file_get_contents('php://input'), true);

    $name = isset($input_data['name']) ? trim($input_data['name']) : (isset($_POST['name']) ? trim($_POST['name']) : '');
    $email = isset($input_data['email']) ? trim($input_data['email']) : (isset($_POST['email']) ? trim($_POST['email']) : '');
    $subject = isset($input_data['subject']) ? trim($input_data['subject']) : (isset($_POST['subject']) ? trim($_POST['subject']) : '');
    $message = isset($input_data['message']) ? trim($input_data['message']) : (isset($_POST['message']) ? trim($_POST['message']) : '');

    // Server-side validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง (Please fill in all fields.)"
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "รูปแบบอีเมลไม่ถูกต้อง (Invalid email format.)"
        ]);
        exit;
    }

    // Insert into database
    try {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message
        ]);

        echo json_encode([
            "status" => "success",
            "message" => "ส่งข้อความสำเร็จและบันทึกลงฐานข้อมูลแล้ว! (Message sent and saved to database.)"
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "บันทึกข้อมูลไม่สำเร็จ (Insert Failed): " . $e->getMessage()
        ]);
    }
    exit;
} else {
    // Request method not supported
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Method Not Allowed"
    ]);
    exit;
}
