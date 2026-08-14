<?php
// config/db.php - Database Configuration & Utilities

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'learnhub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

// API Helper Functions
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// PDF Upload Helper
function handlePdfUpload($file, $targetSubfolder) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error or no file provided.'];
    }

    $allowedMimeTypes = ['application/pdf'];
    $fileMimeType = mime_content_type($file['tmp_name']);
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileExt !== 'pdf' || !in_array($fileMimeType, $allowedMimeTypes)) {
        return ['success' => false, 'error' => 'Only PDF files are allowed.'];
    }

    // Limit size to 50MB
    if ($file['size'] > 50 * 1024 * 1024) {
        return ['success' => false, 'error' => 'File size exceeds 50MB limit.'];
    }

    $uploadBaseDir = __DIR__ . '/../uploads/' . trim($targetSubfolder, '/') . '/';
    if (!is_dir($uploadBaseDir)) {
        mkdir($uploadBaseDir, 0777, true);
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $targetFilePath = $uploadBaseDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        $publicUrl = 'uploads/' . trim($targetSubfolder, '/') . '/' . $filename;
        return ['success' => true, 'url' => $publicUrl, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }
}
