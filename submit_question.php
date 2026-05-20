<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if ($name === '')                                $errors[] = "Name is required";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = "Valid email is required";
if (strlen($message) < 10)                       $errors[] = "Message must be at least 10 characters";

if (!empty($errors)) {
    set_flash('error', implode('. ', $errors));
    header('Location: index.php#questions');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO questions (name, email, subject, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $subject, $message]);
    
    set_flash('success', 'Thank you! Your message has been sent. We will get back to you within 24 hours.');
} catch (PDOException $e) {
    set_flash('error', 'Sorry, something went wrong: ' . $e->getMessage());
}

header('Location: index.php#questions');
exit;
