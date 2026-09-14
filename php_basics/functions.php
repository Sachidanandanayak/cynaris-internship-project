<?php
/**
 * Cynaris Solutions Full Stack Development Internship
 * Week 3 Day 1: PHP Fundamentals
 * 
 * Reusable Helper Functions & Security Utility Library
 * 
 * SECURITY ARCHITECTURE PRINCIPLES:
 * 1. Validation (Input Validation):
 *    - Validates incoming data against expected types, formats, lengths, and constraints.
 *    - Rejects invalid data early before performing business logic or storage.
 * 
 * 2. Sanitization (Input Cleaning):
 *    - Removes or normalizes unwanted characters (e.g. whitespace trimming).
 *    - Note: Sanitization is NOT a substitute for validation or output escaping.
 * 
 * 3. Output Escaping (Context-Specific Defense against XSS):
 *    - Converts special characters into HTML entities (e.g. < becomes &lt;) at the exact
 *      moment user-controlled data is rendered into an HTML document.
 *    - htmlspecialchars() ensures browsers render user input as harmless text instead of executable scripts.
 *    - Note: htmlspecialchars() prevents HTML-body XSS; other contexts (JSON, SQL, shell execution)
 *      require their respective escaping or parameterized APIs (e.g. PDO prepared statements).
 */

declare(strict_types=1);

/**
 * Safely starts a PHP session if one is not already active.
 * Prevents "session_start(): A session had already been started" notices.
 * 
 * @return void
 */
function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Enforce cookie security parameters where applicable
        session_start([
            'cookie_httponly' => true,      // Mitigates access to session cookie via client-side JavaScript
            'cookie_samesite' => 'Lax',     // Mitigates Cross-Site Request Forgery (CSRF)
        ]);
    }
}

/**
 * Safely escapes user-controlled strings for secure HTML output rendering.
 * Prevents Cross-Site Scripting (XSS) by encoding characters such as &, ", ', <, > into entities.
 * 
 * @param mixed $value The string or convertible value to escape
 * @return string The sanitized, HTML-safe entity string
 */
function escapeHtml(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Basic input sanitization: trims extraneous whitespace.
 * 
 * @param mixed $input
 * @return string
 */
function sanitizeInput(mixed $input): string
{
    if (!is_string($input)) {
        return '';
    }
    return trim($input);
}

/**
 * Computes string length safely, falling back to strlen if mbstring is not loaded.
 * 
 * @param string $str
 * @return int
 */
function safeStrLen(string $str): int
{
    return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);
}

/**
 * Validates a person's full name.
 * Requirements: Not empty, 2 to 60 characters, letters, spaces, hyphens, and apostrophes only.
 * 
 * @param string $name
 * @return string|null Error message string on failure, or null if valid.
 */
function validateName(string $name): ?string
{
    if ($name === '') {
        return 'Full name is required.';
    }
    $len = safeStrLen($name);
    if ($len < 2) {
        return 'Full name must be at least 2 characters in length.';
    }
    if ($len > 60) {
        return 'Full name cannot exceed 60 characters.';
    }
    if (!preg_match('/^[a-zA-Z\s\.\'-]+$/u', $name)) {
        return 'Full name can only contain alphabetic letters, spaces, dots, hyphens, and apostrophes.';
    }
    return null;
}

/**
 * Validates an email address format using PHP's native filter_var.
 * 
 * @param string $email
 * @return string|null Error message string on failure, or null if valid.
 */
function validateEmail(string $email): ?string
{
    if ($email === '') {
        return 'Email address is required.';
    }
    if (safeStrLen($email) > 120) {
        return 'Email address cannot exceed 120 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address (e.g. intern@cynaris.com).';
    }
    return null;
}

/**
 * Validates the contact form subject/category.
 * 
 * @param string $subject
 * @param array<string> $allowedCategories
 * @return string|null Error message string on failure, or null if valid.
 */
function validateSubject(string $subject, array $allowedCategories): ?string
{
    if ($subject === '') {
        return 'Please select an inquiry topic.';
    }
    if (!in_array($subject, $allowedCategories, true)) {
        return 'Selected inquiry topic is invalid.';
    }
    return null;
}

/**
 * Validates a user message or feedback text.
 * Requirements: Not empty, minimum 10 characters, maximum 1000 characters.
 * 
 * @param string $message
 * @return string|null Error message string on failure, or null if valid.
 */
function validateMessage(string $message): ?string
{
    if ($message === '') {
        return 'Message is required.';
    }
    $len = safeStrLen($message);
    if ($len < 10) {
        return 'Message must be at least 10 characters long to provide sufficient detail.';
    }
    if ($len > 1000) {
        return 'Message cannot exceed 1000 characters.';
    }
    return null;
}

/**
 * Comprehensive server-side validator for the contact form.
 * 
 * @param array<string, mixed> $post The raw $_POST payload
 * @return array{isValid: bool, errors: array<string, string>, data: array<string, string>}
 */
function validateContactForm(array $post): array
{
    $allowedTopics = ['internship_query', 'technical_feedback', 'mentorship_request', 'general_inquiry'];

    $data = [
        'name'    => sanitizeInput($post['name'] ?? ''),
        'email'   => sanitizeInput($post['email'] ?? ''),
        'topic'   => sanitizeInput($post['topic'] ?? ''),
        'message' => sanitizeInput($post['message'] ?? '')
    ];

    $errors = [];

    $nameError = validateName($data['name']);
    if ($nameError !== null) {
        $errors['name'] = $nameError;
    }

    $emailError = validateEmail($data['email']);
    if ($emailError !== null) {
        $errors['email'] = $emailError;
    }

    $topicError = validateSubject($data['topic'], $allowedTopics);
    if ($topicError !== null) {
        $errors['topic'] = $topicError;
    }

    $messageError = validateMessage($data['message']);
    if ($messageError !== null) {
        $errors['message'] = $messageError;
    }

    return [
        'isValid' => empty($errors),
        'errors'  => $errors,
        'data'    => $data
    ];
}

/**
 * Renders the standardized site navigation bar.
 * 
 * @param string $activePage Active page identifier for highlighting nav item
 * @return void
 */
function renderNavigation(string $activePage): void
{
    $navItems = [
        'home'    => ['url' => 'index.php', 'label' => 'PHP Fundamentals', 'icon' => '⚡'],
        'form'    => ['url' => 'form.php', 'label' => 'Form Handling ($_POST)', 'icon' => '📝'],
        'get'     => ['url' => 'get_example.php', 'label' => 'GET Explorer ($_GET)', 'icon' => '🔗'],
        'session' => ['url' => 'session_example.php', 'label' => 'Session State ($_SESSION)', 'icon' => '🔐']
    ];
    ?>
    <nav class="main-navbar" aria-label="Module Navigation">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <span class="brand-badge">PHP 8.5</span>
                <span class="brand-title">Cynaris <span>PHP Basics</span></span>
            </a>
            <ul class="nav-links">
                <?php foreach ($navItems as $key => $item): ?>
                    <li>
                        <a href="<?= escapeHtml($item['url']) ?>" class="nav-link <?= $activePage === $key ? 'active' : '' ?>">
                            <span class="nav-icon"><?= $item['icon'] ?></span>
                            <span><?= escapeHtml($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
    <?php
}

/**
 * Renders the HTML document <head> and top header layout.
 * 
 * @param string $title Page title
 * @param string $activePage Active page key
 * @return void
 */
function renderHeader(string $title, string $activePage): void
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Cynaris Solutions Full Stack Internship - Week 3 Day 1: PHP Fundamentals practical demonstration.">
        <title><?= escapeHtml($title) ?> | Cynaris Solutions</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="site-header">
            <?php renderNavigation($activePage); ?>
        </header>
        <main class="page-container">
    <?php
}

/**
 * Renders the standardized layout footer.
 * 
 * @return void
 */
function renderFooter(): void
{
    ?>
        </main>
        <footer class="site-footer">
            <div class="footer-container">
                <div class="footer-col">
                    <h4>Cynaris Solutions Internship</h4>
                    <p>Week 3 Day 1: Core PHP Fundamentals, Superglobals & Server-Side Security Architecture.</p>
                </div>
                <div class="footer-col">
                    <h4>Runtime Environment</h4>
                    <p>Engine: <strong>PHP <?= escapeHtml(PHP_VERSION) ?></strong> (<?= escapeHtml(PHP_SAPI) ?>)</p>
                    <p>Server Time: <strong><?= date('Y-m-d H:i:s T') ?></strong></p>
                </div>
                <div class="footer-col">
                    <h4>Security Primitives</h4>
                    <p>Defense-in-depth: Strict Validation &bull; <code>htmlspecialchars()</code> Escaping &bull; HttpOnly Sessions</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Cynaris Solutions Full Stack Internship &bull; All Rights Reserved.</p>
            </div>
        </footer>
    </body>
    </html>
    <?php
}
