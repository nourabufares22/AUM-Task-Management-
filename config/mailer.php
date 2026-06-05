<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function create_mailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = getenv('MAIL_HOST')     ?: 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('MAIL_USERNAME') ?: '';
    $mail->Password   = getenv('MAIL_PASSWORD') ?: '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)(getenv('MAIL_PORT') ?: 587);
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(
        getenv('MAIL_FROM')      ?: (getenv('MAIL_USERNAME') ?: 'noreply@aum.edu.jo'),
        getenv('MAIL_FROM_NAME') ?: 'AUM Maintenance System'
    );
    return $mail;
}

function base_url(): string {
    $url = getenv('APP_URL') ?: '';
    if ($url) return rtrim($url, '/');
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path   = rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/');
    return $scheme . '://' . $host . $path;
}
