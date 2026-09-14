<?php
/**
 * Cynaris Solutions Full Stack Development Internship
 * Week 3 Day 1: PHP Fundamentals
 * 
 * Main Dashboard & Interactive PHP Fundamentals Demonstration
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// Safe session start
ensureSessionStarted();

// --------------------------------------------------------------------------
// 1. FUNDAMENTALS: Variables & Data Types
// --------------------------------------------------------------------------
$companyName = "Cynaris Solutions";               // string
$internshipWeek = 3;                             // integer
$dailyCompletionScore = 98.75;                   // float
$isEnrollmentActive = true;                      // boolean
$mentorAssigned = null;                          // NULL
$coreCompetencies = ["PHP 8.5", "HTTP", "REST"]; // array

// --------------------------------------------------------------------------
// 2. FUNDAMENTALS: Arrays (Indexed & Associative)
// --------------------------------------------------------------------------
$curriculumModules = [
    "HTML5 Structure & Semantics",
    "CSS Responsive Architecture",
    "JavaScript ES6+ & Async/Await",
    "PHP Server-Side Programming",
    "MySQL Relational Databases"
];

$internProfile = [
    "fullName"        => "Sachidananda Nayak",
    "internId"        => "CYN-2026-FS042",
    "role"            => "Full Stack Engineering Intern",
    "assignedProject" => "Cynaris Cloud & AI Telemetry",
    "phpVersion"      => PHP_VERSION,
    "environment"     => PHP_SAPI
];

// --------------------------------------------------------------------------
// 3. FUNDAMENTALS: Conditionals & Modern PHP 8 Match
// --------------------------------------------------------------------------
$evaluationScore = 94;

// Standard if-elseif-else
if ($evaluationScore >= 90) {
    $tierGrade = "Distinction (Tier 1)";
    $tierBadgeClass = "badge-emerald";
} elseif ($evaluationScore >= 75) {
    $tierGrade = "Commended (Tier 2)";
    $tierBadgeClass = "badge-blue";
} else {
    $tierGrade = "Passing (Tier 3)";
    $tierBadgeClass = "badge-amber";
}

// PHP 8 Match Expression
$serverEnvironmentStatus = match (PHP_SAPI) {
    'cli-server' => 'Local PHP Built-in Development Server (Testing Mode)',
    'cli'        => 'Command-Line Interface (Automation Mode)',
    'fpm-fcgi'   => 'FastCGI Process Manager (Production Engine)',
    'apache2handler' => 'Apache Web Server Module',
    default      => 'Unknown SAPI Environment'
};

// --------------------------------------------------------------------------
// 4. FUNDAMENTALS: Loops
// --------------------------------------------------------------------------
// For loop: generates an array of simulated milestones
$milestoneProgress = [];
for ($i = 1; $i <= 5; $i++) {
    $milestoneProgress[] = "Sprint #{$i} completed (" . ($i * 20) . "% benchmark)";
}

// While loop: counter calculation
$retryAttempts = 0;
$maxRetries = 3;
$retryLogs = [];
while ($retryAttempts < $maxRetries) {
    $retryAttempts++;
    $retryLogs[] = "Health check probe #{$retryAttempts} passed";
}

// Do...while loop: guaranteed single execution
$heartbeatCount = 0;
$heartbeatResult = "";
do {
    $heartbeatCount++;
    $heartbeatResult = "System heartbeat verified at count = {$heartbeatCount}";
} while ($heartbeatCount < 1);

// --------------------------------------------------------------------------
// 5. FUNDAMENTALS: Functions
// --------------------------------------------------------------------------
/**
 * Calculates a formatted performance rating string with strict typing.
 * 
 * @param string $candidateName Candidate full name
 * @param float $score Percentage score (0.0 to 100.0)
 * @param string $suffix Optional title suffix
 * @return string Formatted certificate summary
 */
function generateInternSummary(string $candidateName, float $score, string $suffix = "P.E."): string
{
    $formattedScore = number_format($score, 1);
    $status = $score >= 70.0 ? "QUALIFIED" : "PENDING REVIEW";
    return "[{$status}] {$candidateName}, {$suffix} — Assessment Score: {$formattedScore}%";
}

$sampleSummary = generateInternSummary("Sachidananda Nayak", 96.5);

renderHeader("PHP Fundamentals & Language Explorer", "home");
?>

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="hero-tag">Week 3 &bull; Day 1 Deliverable</div>
    <h1 class="hero-title">Core PHP 8.5 Fundamentals & Architecture</h1>
    <p class="hero-subtitle">
        Explore modern PHP server-side primitives, strict typing, control structures, and superglobals.
        Engineered for the Cynaris Solutions Full Stack Development Internship program.
    </p>
    <div class="hero-badges">
        <span class="badge badge-blue">⚡ PHP <?= escapeHtml(PHP_VERSION) ?></span>
        <span class="badge badge-emerald">🛡️ Type Safety: strict_types=1</span>
        <span class="badge badge-amber">🌐 SAPI: <?= escapeHtml(PHP_SAPI) ?></span>
    </div>
</section>

<!-- Quick Navigation Cards to Required Deliverables -->
<section class="quick-nav-grid" aria-label="Deliverable Modules">
    <a href="form.php" class="nav-card">
        <div>
            <div class="nav-card-header">
                <div class="nav-card-icon">📝</div>
                <h2 class="nav-card-title">Form Handling ($_POST)</h2>
            </div>
            <p class="nav-card-desc">
                Production-grade form processing using the Post/Redirect/Get (PRG) pattern, strict input validation, 
                error flashing, and sticky field preservation.
            </p>
        </div>
        <div class="nav-card-action">
            <span>Launch Form Demo</span> &rarr;
        </div>
    </a>

    <a href="get_example.php" class="nav-card">
        <div>
            <div class="nav-card-header">
                <div class="nav-card-icon">🔗</div>
                <h2 class="nav-card-title">GET Explorer ($_GET)</h2>
            </div>
            <p class="nav-card-desc">
                URL query parameter extraction, safe default fallbacks, idempotent request handling, 
                and live Cross-Site Scripting (XSS) defense demos.
            </p>
        </div>
        <div class="nav-card-action">
            <span>Launch GET Explorer</span> &rarr;
        </div>
    </a>

    <a href="session_example.php" class="nav-card">
        <div>
            <div class="nav-card-header">
                <div class="nav-card-icon">🔐</div>
                <h2 class="nav-card-title">Session State ($_SESSION)</h2>
            </div>
            <p class="nav-card-desc">
                Server-side state persistence across HTTP requests: visit counter, active session inspection, 
                secure session cookies, and safe reset flows.
            </p>
        </div>
        <div class="nav-card-action">
            <span>Launch Session Demo</span> &rarr;
        </div>
    </a>
</section>

<!-- ========================================================================
     SECTION 1: Variables & Common Data Types
     ======================================================================== -->
<section class="demo-section">
    <div class="section-header">
        <h2 class="section-title"><span>📦</span> 1. Variables & Common Data Types</h2>
        <span class="section-badge">Scalar & Compound Types</span>
    </div>

    <div class="card">
        <h3 class="card-title">Type Inference & Runtime Introspection</h3>
        <p class="card-desc">
            PHP is dynamically typed with optional strict type declarations (<code>declare(strict_types=1);</code>).
            Variables are prefixed with <code>$</code> and store scalar primitives, compound types, or null.
        </p>

        <div class="code-output-grid">
            <div class="code-panel">
                <div class="panel-header">
                    <span>Source Code (PHP 8.5)</span>
                    <span class="panel-header-badge">Input</span>
                </div>
                <pre><code>$companyName          = "Cynaris Solutions";               // string
$internshipWeek       = 3;                             // integer
$dailyCompletionScore = 98.75;                         // float
$isEnrollmentActive   = true;                          // boolean
$mentorAssigned       = null;                          // NULL
$coreCompetencies     = ["PHP 8.5", "HTTP", "REST"];   // array</code></pre>
            </div>

            <div class="output-panel">
                <div class="panel-header">
                    <span>Live Output &amp; Type Evaluation</span>
                    <span class="panel-header-badge">Evaluated</span>
                </div>
                <div class="output-content">
                    <table class="data-table" style="background: transparent;">
                        <thead>
                            <tr>
                                <th>Variable</th>
                                <th>PHP Type</th>
                                <th>Evaluated Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>$companyName</code></td>
                                <td><?= escapeHtml(gettype($companyName)) ?></td>
                                <td><?= escapeHtml($companyName) ?></td>
                            </tr>
                            <tr>
                                <td><code>$internshipWeek</code></td>
                                <td><?= escapeHtml(gettype($internshipWeek)) ?></td>
                                <td><?= escapeHtml((string)$internshipWeek) ?></td>
                            </tr>
                            <tr>
                                <td><code>$dailyCompletionScore</code></td>
                                <td><?= escapeHtml(gettype($dailyCompletionScore)) ?></td>
                                <td><?= escapeHtml((string)$dailyCompletionScore) ?>%</td>
                            </tr>
                            <tr>
                                <td><code>$isEnrollmentActive</code></td>
                                <td><?= escapeHtml(gettype($isEnrollmentActive)) ?></td>
                                <td><?= $isEnrollmentActive ? 'true (1)' : 'false (0)' ?></td>
                            </tr>
                            <tr>
                                <td><code>$mentorAssigned</code></td>
                                <td><?= escapeHtml(gettype($mentorAssigned)) ?></td>
                                <td><em>null</em></td>
                            </tr>
                            <tr>
                                <td><code>$coreCompetencies</code></td>
                                <td><?= escapeHtml(gettype($coreCompetencies)) ?></td>
                                <td><?= escapeHtml(implode(', ', $coreCompetencies)) ?> (<?= count($coreCompetencies) ?> items)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================
     SECTION 2: Arrays (Indexed & Associative)
     ======================================================================== -->
<section class="demo-section">
    <div class="section-header">
        <h2 class="section-title"><span>📚</span> 2. Indexed &amp; Associative Arrays</h2>
        <span class="section-badge">Collection Structures</span>
    </div>

    <div class="card">
        <h3 class="card-title">Indexed Arrays vs. Key-Value Associative Hashmaps</h3>
        <p class="card-desc">
            PHP arrays are ordered maps capable of acting as lists (indexed by integers starting at 0) 
            or associative tables (keyed by descriptive strings).
        </p>

        <div class="code-output-grid">
            <div class="code-panel">
                <div class="panel-header">
                    <span>Source Code (Indexed &amp; Associative)</span>
                    <span class="panel-header-badge">PHP</span>
                </div>
                <pre><code>// Indexed Array (Zero-based sequential keys)
$curriculumModules = [
    "HTML5 Structure & Semantics",
    "CSS Responsive Architecture",
    "JavaScript ES6+ & Async/Await",
    "PHP Server-Side Programming",
    "MySQL Relational Databases"
];

// Associative Array (Custom string keys)
$internProfile = [
    "fullName"        => "Sachidananda Nayak",
    "internId"        => "CYN-2026-FS042",
    "role"            => "Full Stack Engineering Intern",
    "assignedProject" => "Cynaris Cloud & AI Telemetry",
    "phpVersion"      => PHP_VERSION
];</code></pre>
            </div>

            <div class="output-panel">
                <div class="panel-header">
                    <span>Live Output Traversal</span>
                    <span class="panel-header-badge">Foreach Render</span>
                </div>
                <div class="output-content">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.5rem;">Indexed Array ($curriculumModules):</strong>
                    <ol style="margin-left: 1.25rem; margin-bottom: 1rem; color: #CBD5E1;">
                        <?php foreach ($curriculumModules as $index => $module): ?>
                            <li><code>[<?= $index ?>]</code> &rarr; <?= escapeHtml($module) ?></li>
                        <?php endforeach; ?>
                    </ol>

                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.5rem;">Associative Array ($internProfile):</strong>
                    <table class="data-table">
                        <thead>
                            <tr><th>Key</th><th>Mapped Value</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($internProfile as $key => $val): ?>
                                <tr>
                                    <td><code><?= escapeHtml($key) ?></code></td>
                                    <td><?= escapeHtml($val) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================
     SECTION 3: Conditionals
     ======================================================================== -->
<section class="demo-section">
    <div class="section-header">
        <h2 class="section-title"><span>🔀</span> 3. Control Structures &amp; Conditionals</h2>
        <span class="section-badge">if-elseif-else &amp; match</span>
    </div>

    <div class="card">
        <h3 class="card-title">Branching Logic &amp; PHP 8 <code>match</code> Expressions</h3>
        <p class="card-desc">
            PHP supports classic <code>if/elseif/else</code> and <code>switch</code> statements, as well as 
            modern, concise, strictly typed <code>match</code> expressions introduced in PHP 8.
        </p>

        <div class="code-output-grid">
            <div class="code-panel">
                <div class="panel-header">
                    <span>Source Code (Conditionals)</span>
                    <span class="panel-header-badge">Logic</span>
                </div>
                <pre><code>$evaluationScore = <?= $evaluationScore ?>;

// if-elseif-else Branching
if ($evaluationScore >= 90) {
    $tierGrade = "Distinction (Tier 1)";
} elseif ($evaluationScore >= 75) {
    $tierGrade = "Commended (Tier 2)";
} else {
    $tierGrade = "Passing (Tier 3)";
}

// PHP 8 Match Expression
$serverStatus = match (PHP_SAPI) {
    'cli-server' => 'Local Dev Server',
    'cli'        => 'CLI Automation',
    'fpm-fcgi'   => 'PHP-FPM Production',
    default      => 'Standard Gateway'
};</code></pre>
            </div>

            <div class="output-panel">
                <div class="panel-header">
                    <span>Condition Evaluation Result</span>
                    <span class="panel-header-badge">Live State</span>
                </div>
                <div class="output-content" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem;">Score Evaluated:</span>
                        <strong style="font-size: 1.2rem; color: #F8FAFC;"> <?= $evaluationScore ?> / 100</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem;">Assigned Grade Tier:</span>
                        <div style="margin-top: 0.25rem;">
                            <span class="badge <?= $tierBadgeClass ?>"><?= escapeHtml($tierGrade) ?></span>
                        </div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.8rem;">PHP 8 Match Result for SAPI ('<?= escapeHtml(PHP_SAPI) ?>'):</span>
                        <div style="color: #67E8F9; margin-top: 0.25rem; font-weight: 600;">
                            <?= escapeHtml($serverEnvironmentStatus) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================
     SECTION 4: Loops
     ======================================================================== -->
<section class="demo-section">
    <div class="section-header">
        <h2 class="section-title"><span>🔁</span> 4. Iteration &amp; Loops</h2>
        <span class="section-badge">for, while, do-while, foreach</span>
    </div>

    <div class="card">
        <h3 class="card-title">Four Canonical Iteration Constructs</h3>
        <p class="card-desc">
            PHP provides four loop mechanisms suited for counter iteration, condition monitoring, 
            and container traversal.
        </p>

        <div class="code-output-grid">
            <div class="code-panel">
                <div class="panel-header">
                    <span>Source Code (Loop Primitives)</span>
                    <span class="panel-header-badge">PHP</span>
                </div>
                <pre><code>// 1. FOR loop: Known iterations
for ($i = 1; $i <= 5; $i++) {
    $milestoneProgress[] = "Sprint #{$i}...";
}

// 2. WHILE loop: Condition evaluated first
while ($retryAttempts < $maxRetries) {
    $retryAttempts++;
}

// 3. DO-WHILE loop: Guaranteed single pass
do {
    $heartbeatCount++;
} while ($heartbeatCount < 1);

// 4. FOREACH loop: Traversing collections
foreach ($curriculumModules as $k => $item) { ... }</code></pre>
            </div>

            <div class="output-panel">
                <div class="panel-header">
                    <span>Loop Results Breakdown</span>
                    <span class="panel-header-badge">Results</span>
                </div>
                <div class="output-content">
                    <div style="margin-bottom: 0.75rem;">
                        <span style="color: #94A3B8; font-size: 0.8rem;">FOR Loop (5 Iterations):</span>
                        <ul style="margin-left: 1.25rem; font-size: 0.82rem; color: #CBD5E1;">
                            <?php foreach ($milestoneProgress as $milestone): ?>
                                <li><?= escapeHtml($milestone) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div style="margin-bottom: 0.75rem;">
                        <span style="color: #94A3B8; font-size: 0.8rem;">WHILE Loop Logs:</span>
                        <div style="font-size: 0.82rem; color: #34D399;">
                            <?= escapeHtml(implode(' &bull; ', $retryLogs)) ?>
                        </div>
                    </div>

                    <div>
                        <span style="color: #94A3B8; font-size: 0.8rem;">DO...WHILE Execution:</span>
                        <div style="font-size: 0.82rem; color: #FBBF24;">
                            <?= escapeHtml($heartbeatResult) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================
     SECTION 5: Functions
     ======================================================================== -->
<section class="demo-section">
    <div class="section-header">
        <h2 class="section-title"><span>🧩</span> 5. Functions &amp; Type Safety</h2>
        <span class="section-badge">Strict Typing &amp; Return Types</span>
    </div>

    <div class="card">
        <h3 class="card-title">Encapsulation, Default Arguments &amp; Return Type Hints</h3>
        <p class="card-desc">
            Modern PHP functions leverage strict scalar type declarations for arguments and return values, 
            producing robust, self-documenting code.
        </p>

        <div class="code-output-grid">
            <div class="code-panel">
                <div class="panel-header">
                    <span>Source Code (Typed Function)</span>
                    <span class="panel-header-badge">Declaration</span>
                </div>
                <pre><code>declare(strict_types=1);

/**
 * Calculates a formatted performance rating string.
 */
function generateInternSummary(
    string $candidateName, 
    float $score, 
    string $suffix = "P.E."
): string {
    $formattedScore = number_format($score, 1);
    $status = $score >= 70.0 ? "QUALIFIED" : "PENDING REVIEW";
    return "[{$status}] {$candidateName}, {$suffix} — Score: {$formattedScore}%";
}

// Invocation
$summary = generateInternSummary("Sachidananda Nayak", 96.5);</code></pre>
            </div>

            <div class="output-panel">
                <div class="panel-header">
                    <span>Function Execution Return</span>
                    <span class="panel-header-badge">Invocation</span>
                </div>
                <div class="output-content" style="display: flex; flex-direction: column; justify-content: center;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.35rem;">Output Result:</span>
                    <code style="background: rgba(0,0,0,0.4); padding: 0.85rem; border-radius: 6px; color: #38BDF8; font-size: 0.9rem; border: 1px solid var(--border-subtle);">
                        <?= escapeHtml($sampleSummary) ?>
                    </code>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.75rem;">
                        Ensures type enforcement: passing an invalid type triggers a <code>TypeError</code> exception at runtime.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Security Principle Overview Callout -->
<div class="callout">
    <div class="callout-title">🛡️ Defense-in-Depth Security Framework</div>
    <p>
        <strong>1. Validation:</strong> Guarantees input conforms strictly to expected business rules (type, format, length, range) before any processing.
    </p>
    <p>
        <strong>2. Sanitization:</strong> Normalizes incoming raw strings (e.g. trimming whitespace).
    </p>
    <p>
        <strong>3. Output Escaping:</strong> Encodes characters like <code>&lt;</code>, <code>&gt;</code>, <code>&quot;</code>, and <code>&amp;</code> into HTML entities using <code>htmlspecialchars($val, ENT_QUOTES, 'UTF-8')</code> right before rendering into HTML.
    </p>
</div>

<?php renderFooter(); ?>
