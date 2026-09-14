<?php
/**
 * Cynaris Solutions Full Stack Development Internship
 * Week 3 Day 1: PHP Fundamentals
 * 
 * Server-Side Form Request Processor (PRG Pattern)
 * 
 * ARCHITECTURE NOTE:
 * This script processes POST submissions independently from form presentation.
 * Following the Post/Redirect/Get (PRG) pattern prevents accidental duplicate form submissions
 * if the user reloads the browser, improving both security and user experience.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// Safe session start
ensureSessionStarted();

// Only accept POST requests; redirect direct GET requests back to the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.php');
    exit;
}

// Perform server-side validation and data extraction
$validationResult = validateContactForm($_POST);

if (!$validationResult['isValid']) {
    // Validation Failed:
    // Store error messages and previous input values in session to preserve state
    $_SESSION['form_errors'] = $validationResult['errors'];
    $_SESSION['form_data']   = $validationResult['data'];
    unset($_SESSION['form_success']);

    // Redirect back to form.php (PRG pattern)
    header('Location: form.php');
    exit;
}

// Validation Succeeded:
// Clear any existing error state
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);

// Store submission summary in session
$_SESSION['form_success'] = [
    'name'        => $validationResult['data']['name'],
    'email'       => $validationResult['data']['email'],
    'topic'       => $validationResult['data']['topic'],
    'message'     => $validationResult['data']['message'],
    'submittedAt' => date('Y-m-d H:i:s T')
];

// Set global user session name for demonstration across session_example.php
$_SESSION['user_name'] = $validationResult['data']['name'];

// Redirect back to form.php to display success alert (PRG pattern)
header('Location: form.php');
exit;
