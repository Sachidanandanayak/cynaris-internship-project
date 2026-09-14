<?php
/**
 * Cynaris Solutions Full Stack Development Internship
 * Week 3 Day 1: PHP Fundamentals
 * 
 * Demonstrating the $_GET Superglobal, URL Query Parameters, Safe Defaults & XSS Prevention
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// Safe session start
ensureSessionStarted();

// --------------------------------------------------------------------------
// 1. Reading Query Parameters via $_GET with Safe Default Fallbacks
// --------------------------------------------------------------------------
// Null coalescing operator (??) ensures default values if keys are missing
$rawName   = isset($_GET['name']) ? trim((string)$_GET['name']) : '';
$rawTopic  = isset($_GET['topic']) ? trim((string)$_GET['topic']) : '';
$rawRole   = isset($_GET['role']) ? trim((string)$_GET['role']) : '';
$rawView   = isset($_GET['view']) ? trim((string)$_GET['view']) : 'summary';

// Apply defaults if empty
$userName  = $rawName !== '' ? $rawName : 'Guest Developer';
$userTopic = $rawTopic !== '' ? $rawTopic : 'PHP 8.5 Superglobals';
$userRole  = $rawRole !== '' ? $rawRole : 'Full Stack Intern';
$viewMode  = in_array($rawView, ['summary', 'detailed'], true) ? $rawView : 'summary';

// Detect if custom query parameters were supplied
$hasQueryParams = !empty($_GET);

renderHeader("GET Superglobal Explorer ($_GET)", "get");
?>

<div class="hero-banner" style="margin-bottom: 2rem;">
    <div class="hero-tag">Superglobal: $_GET</div>
    <h1 class="hero-title">URL Query Parameters &amp; $_GET Explorer</h1>
    <p class="hero-subtitle">
        Inspect how PHP reads, validates, and safely sanitizes query parameters from the HTTP request URL.
        Features live parameter binding and instant XSS attack simulation.
    </p>
    <div class="hero-badges">
        <span class="badge badge-blue">Method: GET</span>
        <span class="badge badge-emerald">Safe Defaults: ?? Fallbacks</span>
        <span class="badge badge-amber">Defense: ENT_QUOTES | UTF-8</span>
    </div>
</div>

<!-- ========================================================================
     CURRENT ACTIVE QUERY PARAMETERS SUMMARY
     ======================================================================== -->
<div class="card">
    <div class="section-header" style="margin-bottom: 0.5rem;">
        <h2 class="card-title" style="margin-bottom: 0;">
            <span>🔍</span> Active Request Parameters (URL Query String)
        </h2>
        <span class="badge <?= $hasQueryParams ? 'badge-emerald' : 'badge-blue' ?>">
            <?= $hasQueryParams ? 'Custom Parameters Detected' : 'Default Fallback Values Active' ?>
        </span>
    </div>

    <p class="card-desc">
        Full request URI: <code><?= escapeHtml($_SERVER['REQUEST_URI'] ?? '/get_example.php') ?></code>
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>$_GET Key</th>
                    <th>Raw Value Status</th>
                    <th>Escaped HTML Output (Safe)</th>
                    <th>Fallback Applied?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>name</code></td>
                    <td><?= isset($_GET['name']) ? 'Supplied in URL' : '<em>Missing</em>' ?></td>
                    <td><strong style="color: #67E8F9;"><?= escapeHtml($userName) ?></strong></td>
                    <td><?= $rawName === '' ? 'Yes (Default: Guest Developer)' : 'No (User Provided)' ?></td>
                </tr>
                <tr>
                    <td><code>topic</code></td>
                    <td><?= isset($_GET['topic']) ? 'Supplied in URL' : '<em>Missing</em>' ?></td>
                    <td><strong style="color: #67E8F9;"><?= escapeHtml($userTopic) ?></strong></td>
                    <td><?= $rawTopic === '' ? 'Yes (Default: PHP 8.5 Superglobals)' : 'No (User Provided)' ?></td>
                </tr>
                <tr>
                    <td><code>role</code></td>
                    <td><?= isset($_GET['role']) ? 'Supplied in URL' : '<em>Missing</em>' ?></td>
                    <td><strong style="color: #67E8F9;"><?= escapeHtml($userRole) ?></strong></td>
                    <td><?= $rawRole === '' ? 'Yes (Default: Full Stack Intern)' : 'No (User Provided)' ?></td>
                </tr>
                <tr>
                    <td><code>view</code></td>
                    <td><?= isset($_GET['view']) ? 'Supplied in URL' : '<em>Missing</em>' ?></td>
                    <td><code><?= escapeHtml($viewMode) ?></code></td>
                    <td><?= isset($_GET['view']) ? 'No' : 'Yes (Default: summary)' ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================
     INTERACTIVE PRESET URL LINKS FOR TESTING
     ======================================================================== -->
<div class="card">
    <h3 class="card-title"><span>🧪</span> One-Click Interactive Test Scenarios</h3>
    <p class="card-desc">
        Click any of the preset links below to verify how <code>$_GET</code> handles different URL query payloads:
    </p>

    <div class="preset-list">
        <a href="get_example.php" class="preset-chip">
            <span>🔄</span> Default (No Parameters)
        </a>
        <a href="get_example.php?name=Sachidananda+Nayak&topic=PHP+Fundamentals&role=Full+Stack+Intern" class="preset-chip">
            <span>👤</span> Intern Profile Query
        </a>
        <a href="get_example.php?name=Ada+Lovelace&topic=Algorithm+Design&role=Lead+Architect&view=detailed" class="preset-chip">
            <span>📊</span> Detailed View Mode
        </a>
        <a href="get_example.php?name=Tom+%26+Jerry+%22Pro%22&topic=AT%26T+%27Systems%27&role=QA" class="preset-chip">
            <span>🔣</span> Special Characters (&amp;, &quot;, &#039;)
        </a>
        <a href="get_example.php?name=%3Cscript%3Ealert(%27XSS%27)%3C%2Fscript%3E&topic=%3Cb%3EBold+HTML%3C%2Fb%3E&role=Security+Auditor" class="preset-chip chip-danger" id="link-test-xss">
            <span>🛡️</span> XSS Injection Test (&lt;script&gt;)
        </a>
    </div>

    <!-- XSS Demonstration Banner if script tag is detected in raw parameters -->
    <?php if (str_contains($userName, '<script>') || str_contains($userTopic, '<script>')): ?>
        <div class="alert alert-success" style="margin-top: 1.25rem;">
            <div class="alert-icon">🛡️</div>
            <div>
                <div class="alert-title">XSS Mitigation Verification Successful!</div>
                <p>
                    An active script tag <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code> was submitted in the query parameter.
                    Because <code>escapeHtml()</code> / <code>htmlspecialchars()</code> was used during rendering, the browser displayed the string safely as literal text rather than executing JavaScript!
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================
     LIVE CUSTOM QUERY PARAMETER TEST FORM
     ======================================================================== -->
<div class="code-output-grid">
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title"><span>⚙️</span> Interactive GET Query Builder</h3>
        <p class="card-desc">
            Submit custom parameters via standard HTTP GET. Notice how inputs are appended directly to the URL in the browser address bar.
        </p>

        <form action="get_example.php" method="GET">
            <div class="form-group">
                <label for="get-name" class="form-label">Name Parameter (name):</label>
                <input 
                    type="text" 
                    id="get-name" 
                    name="name" 
                    class="form-control" 
                    value="<?= escapeHtml($rawName) ?>" 
                    placeholder="Enter custom name..."
                >
            </div>

            <div class="form-group">
                <label for="get-topic" class="form-label">Topic Parameter (topic):</label>
                <input 
                    type="text" 
                    id="get-topic" 
                    name="topic" 
                    class="form-control" 
                    value="<?= escapeHtml($rawTopic) ?>" 
                    placeholder="Enter custom topic..."
                >
            </div>

            <div class="form-group">
                <label for="get-role" class="form-label">Role Parameter (role):</label>
                <input 
                    type="text" 
                    id="get-role" 
                    name="role" 
                    class="form-control" 
                    value="<?= escapeHtml($rawRole) ?>" 
                    placeholder="Enter custom role..."
                >
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-sm">
                    <span>🚀</span> Submit GET Request
                </button>
                <a href="get_example.php" class="btn btn-secondary btn-sm">
                    <span>🔄</span> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Educational Guide Card -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title"><span>📖</span> HTTP GET Fundamentals</h3>
        
        <div class="callout" style="margin-top: 0;">
            <div class="callout-title">1. Idempotence &amp; Caching</div>
            <p>
                HTTP GET requests are <strong>idempotent</strong>: calling them multiple times produces no side effects or server mutations. Browsers and CDNs cache GET responses, and users can bookmark or share GET URLs.
            </p>
        </div>

        <div class="callout">
            <div class="callout-title">2. When NOT to use GET</div>
            <p>
                <strong>Never</strong> send sensitive credentials (passwords, API tokens, credit cards) in GET requests. Query parameters remain visible in browser history, server access logs, and HTTP Referer headers.
            </p>
        </div>

        <div class="callout">
            <div class="callout-title">3. Safe Reading Pattern</div>
            <pre style="background: #090E17; padding: 0.6rem; border-radius: 4px; color: #38BDF8; font-size: 0.8rem; margin-top: 0.35rem;"><code>$name = trim((string)($_GET['name'] ?? 'Default'));
echo escapeHtml($name);</code></pre>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
