<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get & validate inputs
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$guests   = trim($_POST['guests'] ?? '');
$checkin  = trim($_POST['checkin'] ?? '');
$checkout = trim($_POST['checkout'] ?? '');
$room     = trim($_POST['room'] ?? '');
$message  = trim($_POST['message'] ?? '');

// Basic validation
$errors = [];
if ($name === '')                                       $errors[] = "Name is required";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = "Valid email is required";
if ($guests === '')                                     $errors[] = "Number of guests is required";
if ($checkin === '' || !strtotime($checkin))            $errors[] = "Valid check-in date is required";
if ($checkout === '' || !strtotime($checkout))          $errors[] = "Valid check-out date is required";
if ($room === '')                                       $errors[] = "Room preference is required";

if (!empty($errors)) {
    set_flash('error', implode('. ', $errors));
    header('Location: index.php#contact');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, email, phone, guests, checkin, checkout, room, message)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $phone, $guests, $checkin, $checkout, $room, $message]);
    
    set_flash('success', 'Karibu! Your booking request has been received. Our team will contact you within 24 hours to confirm.');
} catch (PDOException $e) {
    set_flash('error', 'Sorry, something went wrong: ' . $e->getMessage());
}

header('Location: index.php#contact');
exit;
