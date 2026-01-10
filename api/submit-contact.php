<?php
/**
 * Contact Form Handler for peterebenhoch.com
 * Sends contact form submissions via Mailbox.org SMTP
 */

// Load dependencies
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get allowed origin from environment
$allowedOrigin = $_ENV['ALLOWED_ORIGIN'] ?? 'https://peterebenhoch.com';
header("Access-Control-Allow-Origin: $allowedOrigin");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Simple rate limiting (IP-based)
session_start();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$now = time();
$hourAgo = $now - 3600;

// Initialize rate limit tracking
if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = [];
}

// Clean old entries
$_SESSION['rate_limit'] = array_filter($_SESSION['rate_limit'], function($timestamp) use ($hourAgo) {
    return $timestamp > $hourAgo;
});

// Check rate limit
$maxRequests = (int)($_ENV['MAX_REQUESTS_PER_HOUR'] ?? 5);
if (count($_SESSION['rate_limit']) >= $maxRequests) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.']);
    exit;
}

// Add current request to rate limit tracking
$_SESSION['rate_limit'][] = $now;

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// Validate email
if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Validate interests
if (!isset($data['interests']) || !is_array($data['interests']) || empty($data['interests'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Please select at least one interest']);
    exit;
}

// Sanitize data
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$interests = array_map('htmlspecialchars', $data['interests']);

// Map interest codes to readable names
$interestNames = [
    'transformational-governance' => 'Transformational Governance',
    'information-security-data-sovereignty' => 'Information Security & Data Sovereignty',
    'digital-law-counseling' => 'Digital Law Counseling',
    'keep-me-posted' => 'Keep me posted about website updates',
    'contact-me-directly' => 'Contact me directly'
];

$readableInterests = array_map(function($code) use ($interestNames) {
    return $interestNames[$code] ?? $code;
}, $interests);

// Prepare email content
$emailBody = "New contact form submission from peterebenhoch.com\n\n";
$emailBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$emailBody .= "Email: " . $email . "\n\n";
$emailBody .= "Interests:\n";
foreach ($readableInterests as $interest) {
    $emailBody .= "  • " . $interest . "\n";
}
$emailBody .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$emailBody .= "Submitted: " . date('Y-m-d H:i:s T') . "\n";
$emailBody .= "IP Address: " . $ip . "\n";
$emailBody .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USERNAME'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)$_ENV['SMTP_PORT'];
    $mail->CharSet    = 'UTF-8';
    
    // Recipients
    $mail->setFrom(
        $_ENV['SMTP_FROM_EMAIL'], 
        $_ENV['SMTP_FROM_NAME']
    );
    $mail->addAddress($_ENV['TO_EMAIL']);
    $mail->addReplyTo($email);
    
    // Content
    $mail->isHTML(false);
    $mail->Subject = 'New Contact Form Submission - peterebenhoch.com';
    $mail->Body    = $emailBody;
    
    // Send email
    $mail->send();
    
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you! Your message has been submitted successfully.'
    ]);
    
} catch (Exception $e) {
    // Log error (you can enhance this with proper logging)
    error_log("Contact form error: " . $mail->ErrorInfo);
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send your message. Please try again later or contact us directly at pe@peterebenhoch.com'
    ]);
}
