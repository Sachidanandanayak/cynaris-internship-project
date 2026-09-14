<?php
/**
 * Cynaris Solutions Full Stack Development Internship
 * Week 3 Day 1: PHP Fundamentals
 * 
 * Form Handling ($_POST), Server-Side Validation, Sticky Inputs & XSS Defense
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// Safe session start
ensureSessionStarted();

// Retrieve flash data passed from process.php via $_SESSION
$errors      = $_SESSION['form_errors'] ?? [];
$formData    = $_SESSION['form_data'] ?? [];
$successData = $_SESSION['form_success'] ?? null;

// Consume flash error and success states so subsequent page reloads start clean
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
unset($_SESSION['form_success']);

renderHeader("Form Handling ($_POST) & Server Validation", "form");
?>

<div class="hero-banner" style="margin-bottom: 2rem;">
    <div class="hero-tag">Superglobal: $_POST</div>
    <h1 class="hero-title">Secure Server-Side Form Handling</h1>
    <p class="hero-subtitle">
        Demonstrates the Post/Redirect/Get (PRG) pattern, strict server-side validation, 
        sticky form inputs on validation failure, and defensive XSS mitigation via output escaping.
    </p>
    <div class="hero-badges">
        <span class="badge badge-blue">Method: POST</span>
        <span class="badge badge-emerald">Pattern: Post/Redirect/Get (PRG)</span>
        <span class="badge badge-amber">Defense: htmlspecialchars()</span>
    </div>
</div>

<!-- ========================================================================
     SUCCESS STATE ALERT (Rendered after successful PRG redirect)
     ======================================================================== -->
<?php if ($successData !== null): ?>
    <div class="alert alert-success" role="alert" id="form-success-banner">
        <div class="alert-icon">✅</div>
        <div>
            <div class="alert-title">Form Submission Successfully Validated &amp; Processed!</div>
            <p style="margin-bottom: 0.5rem;">
                The server successfully received, validated, and sanitized your submission at 
                <strong><?= escapeHtml($successData['submittedAt']) ?></strong>.
            </p>
            <div class="table-responsive" style="margin-top: 0.5rem; background: rgba(0,0,0,0.2);">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Escaped Output Rendered in HTML</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Full Name</strong></td>
                            <td><code><?= escapeHtml($successData['name']) ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Email Address</strong></td>
                            <td><code><?= escapeHtml($successData['email']) ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Topic Category</strong></td>
                            <td><code><?= escapeHtml($successData['topic']) ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Message Payload</strong></td>
                            <td style="white-space: pre-wrap;"><code><?= escapeHtml($successData['message']) ?></code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #A7F3D0;">
                <em>Notice: If an XSS payload like <code>&lt;script&gt;</code> was submitted, it was converted to HTML entities and rendered safely as text without executing.</em>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================
     VALIDATION ERROR SUMMARY BANNER
     ======================================================================== -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert" id="form-error-banner">
        <div class="alert-icon">⚠️</div>
        <div>
            <div class="alert-title">Validation Failed: Please correct the following errors</div>
            <ul style="margin-left: 1.25rem; margin-top: 0.35rem;">
                <?php foreach ($errors as $field => $errMsg): ?>
                    <li><strong><?= escapeHtml(ucfirst($field)) ?>:</strong> <?= escapeHtml($errMsg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================
     FORM INTERFACE & CODE WALKTHROUGH
     ======================================================================== -->
<div class="code-output-grid" style="align-items: start;">
    <!-- Form Card -->
    <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title"><span>📝</span> Contact / Internship Inquiry Form</h2>
        <p class="card-desc">
            All fields undergo server-side validation in <code>process.php</code>.
            If validation fails, previously entered values are safely retained (sticky form).
        </p>

        <!-- Quick Test Buttons / Auto-fill Sandbox -->
        <div style="background: rgba(0,0,0,0.25); padding: 0.85rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--border-subtle);">
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 0.4rem;">
                🧪 Quick Test Preset Fillers (Client-side helper for testing):
            </span>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary btn-sm" id="btn-fill-valid" onclick="fillValidData()">
                    <span>✨</span> Fill Valid Data
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="btn-fill-invalid" onclick="fillInvalidData()">
                    <span>❌</span> Fill Invalid Email
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="btn-fill-xss" onclick="fillXssPayload()" style="color: #FCA5A5; border-color: rgba(239, 68, 68, 0.4);">
                    <span>🛡️</span> Fill XSS Payload (&lt;script&gt;)
                </button>
            </div>
        </div>

        <form action="process.php" method="POST" novalidate id="contact-form">
            <!-- Full Name -->
            <div class="form-group">
                <label for="name" class="form-label">
                    Full Name <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                    value="<?= escapeHtml($formData['name'] ?? '') ?>" 
                    placeholder="e.g. Sachidananda Nayak"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback">
                        <span>&bull;</span> <?= escapeHtml($errors['name']) ?>
                    </div>
                <?php endif; ?>
                <div class="form-help">Must be 2–60 characters; letters, spaces, hyphens, and apostrophes only.</div>
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">
                    Email Address <span class="required">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                    value="<?= escapeHtml($formData['email'] ?? '') ?>" 
                    placeholder="e.g. intern@cynaris.com"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback">
                        <span>&bull;</span> <?= escapeHtml($errors['email']) ?>
                    </div>
                <?php endif; ?>
                <div class="form-help">Validated on server using <code>filter_var($email, FILTER_VALIDATE_EMAIL)</code>.</div>
            </div>

            <!-- Inquiry Topic Category -->
            <div class="form-group">
                <label for="topic" class="form-label">
                    Inquiry Topic <span class="required">*</span>
                </label>
                <?php $currentTopic = $formData['topic'] ?? ''; ?>
                <select 
                    id="topic" 
                    name="topic" 
                    class="form-control <?= isset($errors['topic']) ? 'is-invalid' : '' ?>" 
                    required
                >
                    <option value="">-- Please select an inquiry topic --</option>
                    <option value="internship_query" <?= $currentTopic === 'internship_query' ? 'selected' : '' ?>>Internship Query &amp; Milestone</option>
                    <option value="technical_feedback" <?= $currentTopic === 'technical_feedback' ? 'selected' : '' ?>>Technical Feedback &amp; Review</option>
                    <option value="mentorship_request" <?= $currentTopic === 'mentorship_request' ? 'selected' : '' ?>>Mentorship &amp; Code Review</option>
                    <option value="general_inquiry" <?= $currentTopic === 'general_inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                </select>
                <?php if (isset($errors['topic'])): ?>
                    <div class="invalid-feedback">
                        <span>&bull;</span> <?= escapeHtml($errors['topic']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Message Textarea -->
            <div class="form-group">
                <label for="message" class="form-label">
                    Message / Feedback <span class="required">*</span>
                </label>
                <textarea 
                    id="message" 
                    name="message" 
                    class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" 
                    rows="4" 
                    placeholder="Provide detailed feedback or your inquiry (minimum 10 characters)..."
                    required
                ><?= escapeHtml($formData['message'] ?? '') ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <div class="invalid-feedback">
                        <span>&bull;</span> <?= escapeHtml($errors['message']) ?>
                    </div>
                <?php endif; ?>
                <div class="form-help">Minimum 10 characters, maximum 1000 characters.</div>
            </div>

            <!-- Submit Button & Reset -->
            <div class="btn-group">
                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <span>🚀</span> Submit Form ($_POST)
                </button>
                <a href="form.php" class="btn btn-secondary">
                    <span>🔄</span> Clear Form
                </a>
            </div>
        </form>
    </div>

    <!-- Security & Architecture Explanation Card -->
    <div>
        <div class="card">
            <h3 class="card-title"><span>🛡️</span> Security &amp; PRG Workflow</h3>
            <p class="card-desc">
                Understanding the lifecycle of a secure server-side form submission.
            </p>

            <div class="callout" style="margin-top: 0;">
                <div class="callout-title">1. Post / Redirect / Get (PRG) Pattern</div>
                <p>
                    When a user submits the form via <code>POST</code> to <code>process.php</code>, the server validates the data and immediately redirects back to <code>form.php</code> using an HTTP 302/303 redirect.
                </p>
                <p>
                    <strong>Why?</strong> If a user presses Refresh (F5), the browser re-requests the GET view, preventing accidental duplicate form submissions or duplicate transactions.
                </p>
            </div>

            <div class="callout">
                <div class="callout-title">2. Input Validation vs. Output Escaping</div>
                <p>
                    <strong>Input Validation:</strong> Checks whether submitted data conforms to expected rules (e.g. valid email syntax, string length). Rejects invalid input immediately.
                </p>
                <p>
                    <strong>Output Escaping:</strong> When redisplaying user-submitted values (such as in sticky form fields or success confirmations), <code>htmlspecialchars($val, ENT_QUOTES, 'UTF-8')</code> transforms <code>&lt;script&gt;</code> into <code>&amp;lt;script&amp;gt;</code>.
                </p>
            </div>

            <div class="callout">
                <div class="callout-title">3. Critical Security Principle</div>
                <p style="color: #FCA5A5;">
                    <strong>Crucial:</strong> <code>htmlspecialchars()</code> prevents HTML-body Cross-Site Scripting (XSS). It does <em>not</em> prevent SQL Injection (which requires PDO prepared statements) or Command Injection (which requires avoiding system calls or using <code>escapeshellarg()</code>). Context-aware defenses must always be applied!
                </p>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Client-Side Preset Data Fillers
 * Used exclusively for manual testing ease in the browser
 */
function fillValidData() {
    document.getElementById('name').value = 'Sachidananda Nayak';
    document.getElementById('email').value = 'sachin.nayak@cynaris.com';
    document.getElementById('topic').value = 'internship_query';
    document.getElementById('message').value = 'Completed Week 3 Day 1 PHP fundamentals module with all security verifications.';
}

function fillInvalidData() {
    document.getElementById('name').value = 'A';
    document.getElementById('email').value = 'invalid-email-format';
    document.getElementById('topic').value = '';
    document.getElementById('message').value = 'Short';
}

function fillXssPayload() {
    document.getElementById('name').value = "<script>alert('XSS-Name')<\/script>";
    document.getElementById('email').value = 'security-test@cynaris.com';
    document.getElementById('topic').value = 'technical_feedback';
    document.getElementById('message').value = "<script>alert('XSS-Message')<\/script> Test string with quotes: \" ' & < >";
}
</script>

<?php renderFooter(); ?>
