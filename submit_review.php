<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name        = trim($_POST['name'] ?? '');
$country     = trim($_POST['country'] ?? '');
$rating      = intval($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');

// Validation
$errors = [];
if ($name === '')                          $errors[] = "Your name is required";
if ($rating < 1 || $rating > 5)            $errors[] = "Please select a rating between 1 and 5 stars";
if (strlen($review_text) < 20)             $errors[] = "Review must be at least 20 characters long";

if (!empty($errors)) {
    set_flash('error', implode('. ', $errors));
    header('Location: index.php#reviews');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (name, country, rating, review_text, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$name, $country, $rating, $review_text]);
    
    set_flash('success', 'Asante sana! Thank you for your review. It will appear on our website after admin approval.');
} catch (PDOException $e) {
    set_flash('error', 'Sorry, something went wrong: ' . $e->getMessage());
}

header('Location: index.php#reviews');
exit;
