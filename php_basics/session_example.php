<?php
/**
 * Cynaris Solutions Full Stack Development Internship
 * Week 3 Day 1: PHP Fundamentals
 * 
 * Demonstrating the $_SESSION Superglobal, Server-Side State Persistence & Session Reset
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// Safe session start with security settings (HttpOnly, SameSite=Lax)
ensureSessionStarted();

// --------------------------------------------------------------------------
// 1. Session Reset Workflow
// --------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    // 1. Clear session variables array
    $_SESSION = [];

    // 2. Invalidate session cookie in client browser if enabled
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // 3. Destroy session data stored on server
    session_destroy();

    // Redirect with confirmation status
    header('Location: session_example.php?status=cleared');
    exit;
}

// --------------------------------------------------------------------------
// 2. Handle Setting Custom Session State via POST
// --------------------------------------------------------------------------
$flashNotice = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'set_profile') {
        $customName = sanitizeInput($_POST['custom_name'] ?? '');
        $customRole = sanitizeInput($_POST['custom_role'] ?? '');
        $customTrack = sanitizeInput($_POST['custom_track'] ?? '');

        if ($customName !== '') {
            $_SESSION['user_name'] = $customName;
        }
        if ($customRole !== '') {
            $_SESSION['user_role'] = $customRole;
        }
        if ($customTrack !== '') {
            $_SESSION['preferred_track'] = $customTrack;
        }

        $flashNotice = "Session profile successfully updated!";
    } elseif ($_POST['action'] === 'regenerate') {
        // Demonstrate session fixation mitigation via session_regenerate_id
        session_regenerate_id(true);
        $flashNotice = "Session ID successfully regenerated! Old session ID retired.";
    }
}

// --------------------------------------------------------------------------
// 3. Stateful Page Counter & Initialization
// --------------------------------------------------------------------------
if (!isset($_SESSION['session_created_at'])) {
    $_SESSION['session_created_at'] = date('Y-m-d H:i:s T');
}

// Increment visit counter on each request
$_SESSION['visit_counter'] = ($_SESSION['visit_counter'] ?? 0) + 1;

// Default intern name if not set yet
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Guest Intern (Submit form.php to auto-sync)';
}

$activeSessionId = session_id();
$statusParam = $_GET['status'] ?? '';

renderHeader("Session State Explorer ($_SESSION)", "session");
?>

<div class="hero-banner" style="margin-bottom: 2rem;">
    <div class="hero-tag">Superglobal: $_SESSION</div>
    <h1 class="hero-title">Server-Side Session State Persistence</h1>
    <p class="hero-subtitle">
        Understand how PHP maintains user state across stateless HTTP requests using server-side session files 
        and the encrypted <code>PHPSESSID</code> cookie.
    </p>
    <div class="hero-badges">
        <span class="badge badge-blue">Session ID: <?= escapeHtml(substr($activeSessionId, 0, 8)) ?>...</span>
        <span class="badge badge-emerald">Visits: <?= (int)$_SESSION['visit_counter'] ?></span>
        <span class="badge badge-amber">Storage: Server Filesystem</span>
    </div>
</div>

<!-- ========================================================================
     STATUS ALERTS
     ======================================================================== -->
<?php if ($statusParam === 'cleared'): ?>
    <div class="alert alert-info">
        <div class="alert-icon">🧹</div>
        <div>
            <div class="alert-title">Session State Cleared &amp; Destroyed</div>
            <p>
                All server-side session data was wiped, the session cookie expired, and a brand-new session has been initialized.
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if ($flashNotice !== null): ?>
    <div class="alert alert-success">
        <div class="alert-icon">✅</div>
        <div>
            <div class="alert-title"><?= escapeHtml($flashNotice) ?></div>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================
     SESSION METRICS & ACTIVE DATA TABLE
     ======================================================================== -->
<div class="card">
    <div class="section-header" style="margin-bottom: 1rem;">
        <h2 class="card-title" style="margin-bottom: 0;">
            <span>🗂️</span> Active $_SESSION Superglobal Contents
        </h2>
        <div class="btn-group">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="regenerate">
                <button type="submit" class="btn btn-secondary btn-sm" title="Regenerates session ID (prevents fixation attacks)">
                    <span>🔄</span> Regenerate Session ID
                </button>
            </form>
            <a href="session_example.php?action=reset" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to clear the active session?');">
                <span>🗑️</span> Reset / Clear Session
            </a>
        </div>
    </div>

    <p class="card-desc">
        The table below displays all keys and values currently stored in memory on the server for your active session:
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Session Key</th>
                    <th>PHP Type</th>
                    <th>Stored Value (Escaped)</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION as $key => $val): ?>
                    <tr>
                        <td><code>$_SESSION['<?= escapeHtml($key) ?>']</code></td>
                        <td><code><?= escapeHtml(gettype($val)) ?></code></td>
                        <td>
                            <?php if (is_array($val)): ?>
                                <pre style="margin: 0; font-size: 0.75rem; color: #38BDF8;"><?= escapeHtml(json_encode($val, JSON_PRETTY_PRINT)) ?></pre>
                            <?php else: ?>
                                <strong style="color: #67E8F9;"><?= escapeHtml((string)$val) ?></strong>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            <?php
                            echo match($key) {
                                'session_created_at' => 'Timestamp when current session initiated',
                                'visit_counter'      => 'Increments automatically on every page load',
                                'user_name'          => 'Persisted name (synced from form.php or custom setter)',
                                'user_role'          => 'Role designation stored in session',
                                'preferred_track'    => 'Selected technology track',
                                default              => 'User-defined state variable'
                            };
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================
     INTERACTIVE SESSION MODIFIER FORMS
     ======================================================================== -->
<div class="code-output-grid">
    <!-- Modify Session State Form -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title"><span>✏️</span> Update Session Values</h3>
        <p class="card-desc">
            Submit new values to update the active session state in real-time. Notice how the values persist even across different pages.
        </p>

        <form action="session_example.php" method="POST">
            <input type="hidden" name="action" value="set_profile">

            <div class="form-group">
                <label for="custom_name" class="form-label">Intern Name (user_name):</label>
                <input 
                    type="text" 
                    id="custom_name" 
                    name="custom_name" 
                    class="form-control" 
                    value="<?= escapeHtml($_SESSION['user_name'] ?? '') ?>" 
                    placeholder="Enter intern name..."
                    required
                >
            </div>

            <div class="form-group">
                <label for="custom_role" class="form-label">Assigned Role (user_role):</label>
                <input 
                    type="text" 
                    id="custom_role" 
                    name="custom_role" 
                    class="form-control" 
                    value="<?= escapeHtml($_SESSION['user_role'] ?? 'Software Engineer') ?>" 
                    placeholder="e.g. Cloud & AI Intern..."
                >
            </div>

            <div class="form-group">
                <label for="custom_track" class="form-label">Internship Track (preferred_track):</label>
                <?php $currentTrack = $_SESSION['preferred_track'] ?? 'fullstack'; ?>
                <select id="custom_track" name="custom_track" class="form-control">
                    <option value="fullstack" <?= $currentTrack === 'fullstack' ? 'selected' : '' ?>>Full Stack Web Development (PHP/JS)</option>
                    <option value="cloud_devops" <?= $currentTrack === 'cloud_devops' ? 'selected' : '' ?>>Cloud Architecture &amp; DevOps</option>
                    <option value="ai_ml" <?= $currentTrack === 'ai_ml' ? 'selected' : '' ?>>AI &amp; Data Engineering</option>
                    <option value="cybersecurity" <?= $currentTrack === 'cybersecurity' ? 'selected' : '' ?>>Cybersecurity &amp; Application Defense</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-sm">
                    <span>💾</span> Save to Session
                </button>
                <a href="session_example.php" class="btn btn-secondary btn-sm">
                    <span>🔄</span> Refresh Page (+1 Visit)
                </a>
            </div>
        </form>
    </div>

    <!-- Educational Deep-Dive on Sessions vs Cookies -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title"><span>💡</span> Session Architecture &amp; Security</h3>
        
        <div class="callout" style="margin-top: 0;">
            <div class="callout-title">1. How PHP Sessions Function</div>
            <p>
                When <code>session_start()</code> is called, PHP generates a random 32-character session ID (e.g. <code>PHPSESSID</code>) and sends it as an HTTP response cookie. The client browser automatically sends this cookie on subsequent requests.
            </p>
        </div>

        <div class="callout">
            <div class="callout-title">2. Server-Side Storage vs. Cookies</div>
            <p>
                Unlike client cookies where data lives on the user's computer and can be tampered with, <strong>Session data resides exclusively on the server</strong> (e.g. in <code>/tmp/sess_*</code> or Redis). The browser only possesses the session ID token.
            </p>
        </div>

        <div class="callout">
            <div class="callout-title">3. Session Security Best Practices</div>
            <ul style="margin-left: 1.25rem; font-size: 0.85rem; color: var(--text-secondary);">
                <li>Always set <code>cookie_httponly = true</code> so JavaScript cannot read session cookies.</li>
                <li>Set <code>cookie_samesite = 'Lax'</code> to protect against CSRF attacks.</li>
                <li>Call <code>session_regenerate_id(true)</code> on login or privilege escalation to prevent session fixation attacks.</li>
                <li>Wipe <code>$_SESSION = []</code> and call <code>session_destroy()</code> on user logout.</li>
            </ul>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
