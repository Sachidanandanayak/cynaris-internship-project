# Cynaris Solutions Full Stack Development Internship

This repository contains tasks, projects, and learning modules for the **Cynaris Solutions Full Stack Development Internship** program.

---

## 📅 Program Structure

| Module | Topic | Branch / Status |
|---|---|---|
| **Week 1 – Day 1** | HTML5 Structure & Semantics | `feature/week-1-day-1` |
| **Week 1 – Day 2** | Responsive Design & Mobile-First Landing Page | `feature/week-1-day-2` |
| **Week 1 – Day 3** | JavaScript ES6+ Basics & Interactive Explorer | `feature/week-1-day-3` |
| **Week 1 – Day 4** | Responsive Design & Media Queries | `feature/week-1-day-4` |
| **Week 1 – Day 5** | Git Workflow (Branching, Rebasing, Squashing) | `feature/week-1-day-5` |
| **Week 2 – Day 1** | CSS Advanced (Themes, Animations, Pseudo-Elements, Sticky Nav) | `feature/week-2-day-1` |
| **Week 2 – Day 2** | Responsive Design (4-Section Landing Page, Grid & Flexbox, 320/768/1024/1440px) | `feature/week-2-day-2` |
| **Week 2 – Day 3** | JavaScript Fundamentals (ES6+, Promises, Async/Await, Fetch, Error Handling) | `feature/week-2-day-3` |
| **Week 2 – Day 4** | DOM Manipulation (Dynamic To-Do Application, Event Delegation, LocalStorage) | `feature/week-2-day-4` |
| **Week 2 – Day 5** | Frontend Mini Project (Responsive Weather App, Async/Await, Fetch API) | `feature/week-2-day-5` |

---

## 🌤️ Week 2 – Day 5: Frontend Mini Project (Responsive Weather Application)

### Project Overview
An enterprise-grade, responsive meteorological telemetry dashboard implemented under `weather_app/` for the **Cynaris Solutions Cloud & AI Platform**. Engineered to demonstrate real-time external REST API integration using modern asynchronous JavaScript primitives: the native `fetch()` API, non-blocking `async`/`await` control flow, and defensive `try`/`catch`/`finally` exception boundaries.

The application delivers live meteorological telemetry, high-precision weather condition translation from WMO (World Meteorological Organization) standards, dynamic ambient background theming reacting to current weather conditions, an extensive atmospheric metrics grid (humidity, wind speed & compass direction, barometric pressure, UV index rating, precipitation, and solar schedule), an extended 5-day predictive forecast strip, and instant temperature unit conversion (°C <-> °F) without redundant network requests.

---

### 🌟 Key Architectural & Implementation Highlights

1. **Native `fetch()` API & Non-Blocking `async`/`await` Flow**:
   - Executes asynchronous network queries to resolve location coordinates via the Open-Meteo Geocoding API (`geocodeCity`) and pulls comprehensive multi-day forecasts (`fetchForecastByCoordinates`).
   - Replaces deeply nested promise chains with clean, linear, synchronous-style `await` statements.

2. **Defensive Error Boundaries & Resilient States**:
   - Implements strict `try`/`catch`/`finally` blocks:
     - **Network/HTTP Errors**: Verifies `response.ok` (status code 200–299) before invoking `.json()`.
     - **Location Not Found**: Catches empty result sets and informs the user with an actionable error alert banner.
     - **Empty Search Protection**: Defensively rejects whitespace-only queries prior to initiating network dispatch.
     - **Guaranteed Cleanup**: Employs `finally` to ensure the loading skeleton is hidden and interactive search buttons are re-enabled regardless of whether requests succeed or reject.

3. **Zero API Key Leakage & Segregated Credential Architecture**:
   - Uses the **Open-Meteo API** as the primary default weather provider: **100% free, requiring ZERO API keys**. This guarantees that no secrets are ever committed to git and that the application runs out-of-the-box on local dev servers and public GitHub Pages deployments.
   - For enterprise environments or third-party providers requiring private keys (e.g., OpenWeatherMap), a documented segregation pattern is provided:
     - `weather_app/config.example.js` documents the key structure.
     - `weather_app/config.js` is strictly ignored by `.gitignore`.
     - An accessible in-app **API Settings Modal** allows users to provide an optional OpenWeatherMap key, stored exclusively within browser `localStorage` and never tracked in source code.

4. **Responsive Glassmorphism & Weather-Reactive Themes**:
   - Built mobile-first using CSS Custom Properties, CSS Grid, and Flexbox, tested across 320px, 375px, 768px, 1024px, and 1440px viewports.
   - Dynamically applies contextual theme classes (`theme-clear-day`, `theme-clear-night`, `theme-clouds`, `theme-rain`, `theme-thunderstorm`, `theme-snow`) to adjust ambient background gradients to match live conditions.

5. **Instant State & Unit Toggling (°C / °F)**:
   - Caches the active raw meteorological payload in an in-memory application state (`appState`).
   - Toggling between Celsius and Fahrenheit instantly recalculates temperature values, high/low ranges, and wind speed units (km/h vs mph) in the DOM without triggering network re-fetches.

6. **HTML5 Geolocation & Popular City Chips**:
   - Integrates `navigator.geolocation.getCurrentPosition` with defensive error handling for permission denial, device timeouts, and positioning unavailability.
   - Provides quick-launch chips for major global cities (London, Tokyo, New York, Paris, Mumbai, Sydney).

7. **Browser Storage Persistence**:
   - Serializes user preferences (last searched city, preferred temperature unit) to `localStorage` under `cynaris_weather_prefs_v1`.
   - Automatically restores the user's active session upon subsequent visits or page refreshes.

---

### 🔒 API Key Security & Configuration Approach

To adhere to security best practices and prevent accidental credential exposure in public repositories:
- **Default (Keyless)**: Open-Meteo is active by default. No registration or API keys are required.
- **Custom Keys (Optional)**:
  1. If you wish to use OpenWeatherMap, copy `weather_app/config.example.js` to `weather_app/config.js`:
     ```bash
     cp weather_app/config.example.js weather_app/config.js
     ```
  2. Open `weather_app/config.js` and input your private key:
     ```javascript
     const WEATHER_CONFIG = {
         provider: 'openweathermap',
         openWeatherMap: {
             apiKey: 'YOUR_ACTUAL_API_KEY_HERE',
             baseUrl: 'https://api.openweathermap.org/data/2.5'
         }
     };
     ```
  3. Verify that `weather_app/config.js` is ignored by `.gitignore` (`git status` must not track `config.js`).
  4. Alternatively, click the **Settings icon (⚙)** in the top navigation bar of the Weather App to enter your key directly into the browser's sandboxed `localStorage`.

---

### 🚀 Setup & Local Execution Instructions

#### Prerequisites
- Modern web browser (Google Chrome, Microsoft Edge, Firefox, or Safari).
- Python 3.x or Node.js (for serving static files locally).

#### Running the Application Locally
1. Clone the repository and navigate to the project directory:
   ```bash
   git clone https://github.com/Sachidanandanayak/cynaris-internship-project.git
   cd cynaris-internship-project
   ```
2. Start a local HTTP server:
   - **Using Python 3**:
     ```bash
     python -m http.server 8080
     ```
   - **Using Node.js (npx serve)**:
     ```bash
     npx serve -l 8080
     ```
3. Open your browser and navigate to:
   ```text
   http://localhost:8080/weather_app/index.html
   ```
4. Access via Project Navigation:
   - Open `http://localhost:8080/index.html` and click **"Weather App"** in the top navigation.
   - Switch seamlessly between the **Main Platform**, **To-Do App**, and **Weather App**.

---

### 🌐 Eventual GitHub Pages Live Demo

When merged to `main` and published via GitHub Pages, the application will be publicly accessible at:
- **Main Platform**: `https://sachidanandanayak.github.io/cynaris-internship-project/`
- **To-Do Application**: `https://sachidanandanayak.github.io/cynaris-internship-project/todo_app/`
- **Weather Application**: `https://sachidanandanayak.github.io/cynaris-internship-project/weather_app/`

*(Because Open-Meteo requires no backend proxy and zero API keys, the live deployment functions immediately and reliably without any serverless functions or exposed secrets.)*

---

### 📸 Automated Browser Verification & Screenshots

| Viewport / State | Resolution | Description | Screenshot Preview |
|---|---|---|---|
| **Desktop Initial Load** | `1280 x 800` | Default London telemetry with overview card, 6-metric grid, and 5-day forecast | [`weather_app_desktop_london.png`](screenshots/weather_app_desktop_london.png) |
| **Desktop Active Search** | `1280 x 800` | Tokyo telemetry loaded with Fahrenheit unit conversion (°F) and dynamic clouds theme | [`weather_app_desktop_tokyo.png`](screenshots/weather_app_desktop_tokyo.png) |
| **Defensive Error Banner** | `1280 x 800` | Graceful error state when querying non-existent location `NonExistentCityXYZ999` | [`weather_app_desktop_error.png`](screenshots/weather_app_desktop_error.png) |
| **Mobile Standard Viewport**| `375 x 667` | Mobile responsive layout (iPhone SE / standard mobile) with zero horizontal overflow | [`weather_app_mobile_375px.png`](screenshots/weather_app_mobile_375px.png) |
| **Ultra-Compact Mobile** | `320 x 640` | Narrow mobile layout (320px) with single-column responsive stacking | [`weather_app_mobile_320px.png`](screenshots/weather_app_mobile_320px.png) |

---

---

## 📝 Week 2 – Day 4: DOM Manipulation (Dynamic To-Do Application)

### Project Overview
A modern, accessible, and responsive task coordination application implemented under `todo_app/` for the **Cynaris Solutions Cloud & AI Platform**. Designed to demonstrate production DOM manipulation methodologies without third-party frameworks: programmatic node lifecycle management (`querySelector`, `createElement`, `appendChild`, `removeChild`, and `classList`), scalable event delegation across dynamic elements, bidirectional synchronization with `localStorage`, filtering controls (All, Active, Completed), and WCAG 2.1 AA accessibility.

### 🌟 Key Architectural & DOM Implementation Highlights

1. **Targeting & Caching (`querySelector` / `querySelectorAll`)**:
   - Accurately captures static UI components (`#todo-form`, `#todo-input`, `#todo-list`, `#filter-controls`, `#stat-total`, etc.) into cached constants, avoiding redundant DOM tree lookups.

2. **Dynamic In-Memory Node Creation (`createElement` & `appendChild`)**:
   - Constructs `<li>` containers, custom accessible checkboxes, text labels, and SVG delete buttons entirely in memory.
   - Sets attributes (`type`, `classList`, `aria-label`, `dataset`) prior to mounting to the live document via `appendChild`.

3. **Safe Element Dismounting (`removeChild`)**:
   - Uses `todoList.removeChild(itemElement)` combined with exit CSS transitions (`.removing`) to guarantee clean garbage collection and avoid phantom layout shifts.

4. **Class-Driven State Manipulation (`classList`)**:
   - Toggles task completion (`classList.toggle('completed')`) and filter tab selection (`classList.toggle('active')`) declaratively, separating business state from visual presentation.

5. **Event Delegation on Parent Container (`#todo-list`)**:
   - Instead of binding separate listeners to every new task, a single click listener on `#todo-list` utilizes `event.target.closest('.todo-checkbox')` and `event.target.closest('.todo-delete-btn')`.
   - Dynamically inserted tasks automatically respond to interactions with zero listener memory leaks.

6. **State Persistence (`localStorage`)**:
   - Tasks are serialized into JSON under the key `'cynaris_todos_v1'` upon every addition, completion toggle, deletion, or bulk clearing.
   - On page reload (`DOMContentLoaded`), saved tasks are automatically parsed, reconstructed, and rendered in their exact saved order.

---

### 🧪 Manual & Browser Verification Workflow

Navigate to `todo_app/index.html` (or click "To-Do App" in the top navigation):
1. **Adding a Task**: Type a task in the input field and press Enter or click "Add Task". Verify the item is created via `createElement` and mounted with `appendChild`.
2. **Completing a Task**: Click the checkbox or task text. Verify `classList.toggle('completed')` applies strike-through styling and updates the active item counter.
3. **Deleting a Task**: Click the trash icon. Verify the item dismounts via `removeChild` and counters decrement.
4. **Refreshing the Page**: Reload the browser window (`F5` or `Ctrl+R`). Verify that all tasks restore automatically from `localStorage`.
5. **Console Inspection**: Open DevTools Console (`F12`). Verify zero errors or unhandled exceptions.

---

## ⚡ Week 2 – Day 3: JavaScript Fundamentals (ES6+, Promises, Async/Await, Fetch)

### Project Overview
An isomorphic, production-grade JavaScript architecture implemented for the **Cynaris Solutions Cloud & AI Infrastructure Platform** through `js_fundamentals.js`. Built in strict adherence to Cynaris Day 3 curriculum specifications, demonstrating modern ES6+ functional primitives (arrow functions, default parameters, rest parameters, spread operators, nested destructuring), asynchronous flow control (classic Promises with `.then()`/`.catch()` and modern `async`/`await`), live public REST API integration with `fetch()` targeting JSONPlaceholder, and defensive error handling via `try`/`catch`/`finally`.

The module is engineered to run seamlessly across both execution environments:
1. **Command-Line Interface (Node.js v18+)**: Execute directly via `node js_fundamentals.js` or run the automated test suite `node test_suite.js`.
2. **Interactive Browser UI**: Fully integrated into `index.html` under Section 2.9 (`#fundamentals`), equipped with a live telemetry console, status pills, and interactive test buttons.

---

### 🌟 10 Core ES6+ Functions & Concept Breakdown

| # | Function Name | ES6+ Concepts Demonstrated | Description & Implementation Logic |
|---|---|---|---|
| **01** | `formatServerNode(hostname, ip, status)` | **Arrow Functions & Default Parameters** | Concise arrow syntax with lexical scope. Evaluates defaults (`ip = '127.0.0.1'`, `status = 'active'`) when arguments are omitted, eliminating bug-prone `\|\|` checks. |
| **02** | `calculateClusterCost(baseRate, discountRate, taxRate)` | **Template Literals & Arithmetic** | Multi-line string interpolation (`${expression}`) and arithmetic computation preventing `NaN` and formatting currency strings cleanly. |
| **03** | `aggregateResourceMetrics(...metricValues)` | **Rest Parameters (`...args`) & `reduce()`** | Gathers variable-length arguments into a authentic JavaScript Array. Safely computes count, sum, average, min, and max using `reduce()` and array spread. |
| **04** | `mergeConfigurationProfiles(baseConfig, ...overrideLayers)` | **Object Spread Operator (`{ ...obj }`)** | Immutably merges base server configuration with multiple override layers without mutating inputs. |
| **05** | `cloneAndExtendClusterNodes(primaryNodes, additionalNodes, defaultTags)` | **Array Spread Operator (`[...arr]`)** | Combines node collections immutably and utilizes object spread to inject unique default tags via `new Set()`. |
| **06** | `extractTelemetrySummary(telemetryPacket)` | **Nested Destructuring, Defaults & Aliasing** | Extracts nested properties (`region: datacenterRegion = 'us-east-1'`, `metrics: { cpuLoad = 0 }`) directly into local variables with safe defaults. |
| **07** | `filterAndTransformServices(services, minRating, category)` | **Higher-Order Arrow Functions & Chaining** | Pure functional data pipeline chaining `.filter()`, `.map()`, and `.sort()` with parameter destructuring directly inside the callback signature. |
| **08** | `simulateAsyncHealthPing(endpoint, shouldSucceed, delayMs)` | **Promise Creation & Flow Control** | Instantiates `new Promise((resolve, reject) => ...)` with latency simulation. Demonstrates transition between pending, fulfilled, and rejected states. |
| **09** | `fetchJSONPlaceholderPost(postId)` | **`async` / `await` with Native `fetch()`** | Calls JSONPlaceholder REST API (`https://jsonplaceholder.typicode.com/posts/{id}`). Validates `response.ok` before JSON parsing to handle HTTP 4xx/5xx errors properly. |
| **10** | `fetchSafeApiData(endpointPath, fallback)` | **Defensive Error Handling (`try` / `catch`)** | Wraps async network calls in `try/catch/finally`. Gracefully handles 404s and network dropouts, returning normalized `{ success, data, error, status }` without crashing the application. |

---

### 🔄 Asynchronous Flow: Promises vs. Async/Await

The architecture showcases both asynchronous paradigms side-by-side:

```javascript
// 1. Classic Promise Chain (.then / .catch)
simulateAsyncHealthPing('edge.cynaris.cloud/healthz', true, 150)
    .then((result) => console.log('[Resolved]:', result))
    .catch((error) => console.error('[Caught]:', error.message))
    .finally(() => console.log('Ping operation settled.'));

// 2. Modern async / await with Promise.all (Concurrency)
const [userRes, postsRes] = await Promise.all([
    fetch('https://jsonplaceholder.typicode.com/users/1'),
    fetch('https://jsonplaceholder.typicode.com/posts?userId=1')
]);
const user = await userRes.json();
const posts = await postsRes.json();
```

---

### 🛡️ Graceful Error Handling Pattern

Native `fetch()` only rejects on network failures (not on HTTP 404 or 500 status codes). To ensure robust error handling, `fetchSafeApiData()` implements defensive isolation:

```javascript
try {
    const response = await fetch(targetUrl);
    if (!response.ok) {
        throw new Error(`Endpoint returned status ${response.status} (${response.statusText})`);
    }
    const data = await response.json();
    return { success: true, data, error: null, status: response.status };
} catch (err) {
    // Return predictable fallback structure without throwing unhandled exceptions
    return { success: false, data: fallback, error: err.message, status: response?.status ?? null };
}
```

---

### 🧪 Verification & Testing Guide

#### 1. CLI Execution (Node.js v18+)
Run the standalone runner or the complete assertion test suite in terminal:

```bash
# Run the built-in CLI test suite
node js_fundamentals.js

# Run the 17-test automated unit assertion suite
node test_suite.js
```

**Audit Output:**
```text
==============================================================================
 CYNARIS SOLUTIONS - WEEK 2 DAY 3: JAVASCRIPT FUNDAMENTALS
 ES6+, Promises, async/await, JSONPlaceholder Fetch & Error Handling
==============================================================================
[✔ PASS] Function #1: formatServerNode
[✔ PASS] Function #2: calculateClusterCost
[✔ PASS] Function #3: aggregateResourceMetrics
[✔ PASS] Function #4: mergeConfigurationProfiles
[✔ PASS] Function #5: cloneAndExtendClusterNodes
[✔ PASS] Function #6: extractTelemetrySummary
[✔ PASS] Function #7: filterAndTransformServices
[✔ PASS] Function #8: simulateAsyncHealthPing
[✔ PASS] Function #9: fetchJSONPlaceholderPost
[✔ PASS] Function #10: fetchSafeApiData

>> Demonstrating Promise Flow (.then / .catch):
   [Promise Resolved]: edge.cynaris.cloud/healthz is HEALTHY_200_OK in 150ms
   [Promise Caught]: Gracefully handled -> Ping timeout or connection refused

>> Demonstrating async/await with JSONPlaceholder API:
   Fetched Post #1 Title: "sunt aut facere repellat provident occaecati excepturi optio reprehenderit"

>> Demonstrating Graceful try/catch Error Handling:
   Handled 404 Status: 404 | Caught: "Endpoint returned status 404 (Not Found)" | Fallback: "Safe Fallback Data"

==============================================================================
 SUMMARY: 10/10 Functions Passed. Status: ALL TESTS PASSED
==============================================================================
```

#### 2. Interactive Browser Testing
Open `index.html` in any modern browser or local server and navigate to `#fundamentals`:
- **"Run All 10 ES6+ Functions"**: Evaluates all 10 functions and streams structured JSON directly to the in-page terminal.
- **"Test Promise (.then / .catch)"**: Tests resolved and caught paths with latency simulation.
- **"Fetch JSONPlaceholder Post"**: Retrieves dynamic REST post data and displays it inside the live preview card.
- **"Fetch User (Concurrency)"**: Fetches user profile and linked posts concurrently via `Promise.all`.
- **"Trigger Error (Graceful try/catch)"**: Simulates a 404 route, verifies that the application captures the error gracefully and displays the fallback state.

---

## 🚀 Week 2 – Day 2: Responsive Design & Mobile-First Landing Page

### Project Overview
An enterprise-grade, mobile-first responsive architecture implemented for the **Cynaris Solutions Cloud & AI Infrastructure Platform**. Built in strict adherence to Cynaris curriculum specifications across mobile (`320px`), tablet (`768px`), desktop (`1024px`), and ultrawide (`1440px`) display tiers using pure CSS Grid for multi-dimensional layouts, Flexbox for one-dimensional components, fluid clamp typography, WCAG 2.1 AA accessible touch targets, and zero horizontal scrolling at any viewport.

All previously completed **Week 1 Day 3** ES6+ interactive features, **Week 1 Day 4** media queries, and **Week 2 Day 1** CSS advanced themes and keyframe animations remain fully preserved and operational.

---

### 🌟 4 Core Semantic Landing Page Sections

The landing page is architected around four primary semantic sections encapsulated within an accessible `<header>`, `<main id="main-content">`, and `<footer>`:

1. **Section 1: Hero Section (`#hero`)**
   - **Semantic Tag**: `<section class="hero" id="hero" aria-labelledby="hero-title">`
   - **Value Proposition**: Fluid heading (`clamp(1.85rem, 5.5vw + 0.5rem, 3.5rem)`) with glowing cyan accent text and enterprise platform badge.
   - **CTA Button Group**: Built using **Flexbox** (`display: flex; gap: var(--spacing-4);`). Stacks vertically as full-width touch buttons on mobile (< 480px) and seamlessly aligns horizontally on tablets and desktops.
   - **Metrics Highlights Ribbon**: 3-statistic key performance indicator bar (`99.99% Uptime`, `< 15ms Latency`, `4.8x Velocity`) that transitions from single-column mobile stack to balanced 3-column tablet/desktop grid.

2. **Section 2: Features Section (`#features`)**
   - **Semantic Tag**: `<section class="features section" id="features" aria-labelledby="features-title">`
   - **CSS Grid Layout**: Built using pure **CSS Grid** (`.features-grid`):
     - `320px` (Mobile): `grid-template-columns: 1fr;`
     - `768px` (Tablet): `grid-template-columns: repeat(2, 1fr); gap: var(--spacing-6);`
     - `1024px` & `1440px` (Desktop/Ultrawide): `grid-template-columns: repeat(3, 1fr); gap: var(--spacing-8);`
   - **6 Capability Cards**:
     1. *Multi-Cloud Orchestration* (Cloud Native)
     2. *Zero-Trust Cybersecurity* (Security First)
     3. *Automated CI/CD Pipelines* (DevOps)
     4. *Real-Time Telemetry & AI* (AI Observability)
     5. *Global Edge CDN Network* (Edge Computing)
     6. *Adaptive Elastic Autoscaling* (Cost Efficiency)
   - **Card Design**: Includes accessible SVG iconography, header tags, glowing hover borders (`::before`), and subtle radial corner aura (`::after`).

3. **Section 3: Testimonials Section (`#testimonials`)**
   - **Semantic Tag**: `<section class="testimonials section" id="testimonials" aria-labelledby="testimonials-title">`
   - **CSS Grid Layout**: Built using pure **CSS Grid** (`.testimonials-grid`):
     - `320px` (Mobile): `grid-template-columns: 1fr;`
     - `768px` (Tablet): `grid-template-columns: repeat(2, 1fr);`
     - `1024px` & `1440px` (Desktop/Ultrawide): `grid-template-columns: repeat(3, 1fr);`
   - **Client Cards**: Verified reviews with accessible 5-star rating (`aria-label="5 out of 5 stars rating"`), decorative quotation marks (`::before`), client blockquotes, and author avatar cards.

4. **Section 4: Semantic Footer (`#contact`)**
   - **Semantic Tag**: `<footer class="site-footer" id="contact" aria-labelledby="footer-heading">`
   - **Hybrid Layout (Grid + Flexbox)**:
     - **Top Section (`.footer-top`)**: CSS Grid with brand mission statement on the left and 3 navigation columns (Platform, Company, Stay Updated) on the right.
     - **Newsletter Form**: Flexbox row with work email input (`flex-grow: 1`) and accessible "Subscribe" button.
     - **Bottom Bar (`.footer-bottom`)**: Flexbox alignment distributing copyright and social media links with accessible SVG icons.

> [!NOTE]
> **Preserved Modules**: The **Interactive ES6+ Service Explorer** (Section 2.5) and **CSS Keyframe Telemetry Diagnostics** (Section 2.6) remain intact between Features and Testimonials, ensuring complete continuity across all internship learning modules.

---

### 📐 Flexbox vs CSS Grid Architectural Separation

| Component | Technology | Rationale & Layout Behavior |
|---|---|---|
| **Site Navigation (`.nav-container`, `.nav-list`)** | **Flexbox** | One-dimensional item flow, dynamic space distribution (`justify-content: space-between`), and drawer transformation on mobile. |
| **Hero CTA Group (`.hero-cta-group`)** | **Flexbox** | Direction switching (`column` on mobile -> `row` on tablet/desktop) with consistent gap spacing. |
| **Features Section (`.features-grid`)** | **CSS Grid** | Two-dimensional rigid alignment ensuring equal height cards across 1, 2, and 3 columns. |
| **Testimonials Section (`.testimonials-grid`)** | **CSS Grid** | Multi-card grid distributing client quote cards uniformly without JavaScript masonry. |
| **Hero Stats Ribbon (`.hero-stats`)** | **CSS Grid** | Uniform proportional column allocation (`repeat(3, 1fr)`) with glassmorphic backing. |
| **Footer Navigation (`.footer-links-grid`)** | **CSS Grid** | Clean 2-to-3 column distribution for footer links across viewports. |
| **Footer Bottom Bar (`.footer-bottom`)** | **Flexbox** | One-dimensional row spacing copyright on the left and social media links on the right. |

---

### 📱 Responsive Breakpoints Hierarchy Matrix

| Breakpoint | Viewport Width | Target Devices | Key Layout & Structural Adjustments |
|---|---|---|---|
| **Base Default** | `< 320px` | Ultra-Compact Handhelds | Single-column cards, collapsible mobile hamburger menu, fluid padding (`1rem`). |
| **Mobile S** | `320px` (`@media (min-width: 20rem)`) | Small Smartphones (iPhone SE, Galaxy Fold) | Refined fluid heading scale (`clamp()`), safe gutters, zero horizontal overflow (`scrollWidth === clientWidth`). |
| **Large Mobile** | `480px` (`@media (min-width: 30rem)`) | Large Smartphones & Phablets | 2-column metrics ribbon, horizontal CTA button group, auto-width inspector trigger. |
| **Tablet** | `768px` (`@media (min-width: 48rem)`) | Tablets (iPad Portrait, Tablets) | **2-column Features Grid**, **2-column Testimonials Grid**, 3-column Hero Stats, horizontal search & sort controls. |
| **Desktop / Laptop** | `1024px` (`@media (min-width: 64rem)`) | Laptops & Desktop Displays | **Navigation transitions from drawer to horizontal Flex navbar**; hamburger hidden (`display: none`); **3-column Features Grid**; **3-column Testimonials Grid**. |
| **Standard Desktop** | `1200px` (`@media (min-width: 75rem)`) | Full HD Monitors | Max-width constraint (`75rem` / `1200px`), expanded gutters (`var(--spacing-8)`), 2-column parent footer layout. |
| **Ultrawide / 4K** | `1440px` (`@media (min-width: 90rem)`) | Ultrawide & High-DPI Displays | Max container width constraint (`80rem` / `1280px` centered), generous card padding (`var(--spacing-8)`), high-resolution asset fidelity. |

---

### 🧪 Automated Chrome DevTools Testing & Audit Results

Automated headless Chrome DevTools audit executed via Chrome DevTools Protocol (CDP) with device metric overrides confirms 100% responsive compliance:

| Emulated Viewport | Viewport Width | `scrollWidth` | `clientWidth` | Horizontal Overflow? | Features Grid | Nav Toggle Visible? |
|---|---|---|---|---|---|---|
| **320px (Mobile S)** | `320px` | `320px` | `320px` | **NO (False)** | 1 Column | **YES (True)** |
| **768px (Tablet)** | `768px` | `768px` | `768px` | **NO (False)** | 2 Columns | **YES (True)** |
| **1024px (Laptop)** | `1024px` | `1007px` | `1007px` | **NO (False)** | 3 Columns | **NO (Hidden)** |
| **1440px (Ultrawide)** | `1440px` | `1423px` | `1423px` | **NO (False)** | 3 Columns | **NO (Hidden)** |

*Audit JSON artifact recorded in `screenshots/audit_results.json`.*

---

### 📸 Screenshot Evidence Directory

All responsive verification screenshots are captured and cataloged under the `screenshots/` directory:

1. **`screenshots/320px_mobile.png`**: Mobile S baseline view showing fluid single-column hero and stats.
2. **`screenshots/320px_mobile_drawer_open.png`**: Mobile navigation drawer active with animated hamburger close icon, vertical link stack, theme switcher, and full-width CTA.
3. **`screenshots/768px_tablet.png`**: Tablet layout showing horizontal CTA buttons, 3-column stats, and 2-column features grid.
4. **`screenshots/1024px_desktop.png`**: Desktop layout showing horizontal Flexbox navbar, theme toggle, and hero.
5. **`screenshots/features_grid_3col.png`**: Features section demonstrating 3-column CSS Grid with 6 capability cards.
6. **`screenshots/testimonials_grid_3col.png`**: Testimonials section demonstrating 3-column CSS Grid with star ratings and quotes.
7. **`screenshots/footer_responsive.png`**: Semantic footer displaying multi-column grid, newsletter input, and social links.
8. **`screenshots/1440px_ultrawide.png`**: Ultrawide viewport showcasing centered container constraints and balanced margins.

---

## 🎨 Week 2 – Day 1: CSS Advanced

### Project Overview
Advanced CSS architecture implementing enterprise visual and interactive polish for the **Cynaris Solutions Cloud & AI Infrastructure Platform** through `css/advanced_styles.css`. Demonstrates custom property theme tokens, pure CSS `:has()` theme switching, zero-overhead CSS keyframe animations (spinners & progress bars), multi-layered decorative pseudo-elements (`::before` / `::after`), smooth scroll anchor offsetting, and sticky glassmorphic navigation.

### 🌟 Key Advanced CSS Features
1. **Dark/Light Theme Switcher using CSS Custom Properties**:
   - Implemented using token overrides under `:root:has(#theme-toggle:checked)`, `[data-theme="light"]`, and `body.light-theme`.
   - Smooth CSS transitions (`background-color`, `color`, `border-color`, `box-shadow`) applied across all cards, dialogs, inputs, and buttons.
   - Accessible toggle switch positioned right in the sticky navigation header with animated sliding thumb and sun/moon iconography.
   - 100% functional via pure CSS `:has()`, with progressive `localStorage` persistence for preference retention across page refreshes.

2. **CSS-Only Animated Loading Spinner & Multi-Stage Progress Bar**:
   - **Concentric Dual-Ring Spinner**: Engineered with `@keyframes spinnerClockwise` and `@keyframes spinnerCounterClockwise` with gradient trails and a glowing pulsating core (`@keyframes spinnerPulse`) running at a silky 120 FPS native frame rate without JavaScript.
   - **Barber-Pole Striped Progress Bar**: Multi-gradient striped progression track (`@keyframes progressStripesMove`) with dynamic ambient pulse (`@keyframes progressPulseGlow`).
   - **Zero-Trust Scanner Beam**: Indeterminate continuous ping-pong telemetry scanner (`@keyframes indeterminateScan`).
   - **Sticky Nav Scroll Progress Bar**: Sleek telemetry reading progress indicator pinned to the bottom of the sticky header.

3. **Decorative UI using `::before` & `::after` Pseudo-Elements**:
   - **Section Headers**: Radiant ambient glow behind section tags (`::before`) and dynamic gradient accent underline bars (`::after`) that expand on hover.
   - **Feature & Service Cards**: Top glowing gradient border beam on hover (`::before`) and ambient radial corner aura (`::after`).
   - **Primary Buttons**: High-velocity diagonal light shimmer sweep across the button surface on hover (`::before`).
   - **Testimonial Cards**: Oversized typographic quotation mark watermark (`::before`) and gradient bottom accent divider (`::after`).
   - **Navigation Links**: Center-out expanding underline on hover and focus (`::after`).

4. **Smooth Scrolling & Sticky Navigation (CSS Only)**:
   - Sticky navbar pinned with `position: sticky; top: 0; z-index: 1000;` and frosted glassmorphism (`backdrop-filter: blur(12px)`).
   - Anchor jump clipping prevention using `scroll-padding-top: calc(var(--header-height) + 1.25rem);` on `html` so smooth scrolls to `#features`, `#services`, `#telemetry`, `#testimonials`, and `#contact` land perfectly below the sticky header.

---

## ⚡ Week 1 – Day 3: JavaScript ES6+ Basics & Interactive Explorer

### Project Overview
An enterprise-grade JavaScript ES6+ implementation powering the **Cynaris Solutions Cloud Services & Cost Explorer**. Demonstrates modern ES6+ paradigms, functional programming patterns (`filter`, `map`, `reduce`), event-driven DOM interaction, dynamic template literals, destructuring, and a complete ES5 legacy to ES6+ refactoring showcase.

### 🌟 Key ES6+ Features & Implementation Breakdown
1. **ES5 to ES6+ Refactoring**:
   - Replaced `var` with block-scoped `const` and `let`.
   - Replaced imperative `for` loops with pure `reduce()` aggregations.
   - Replaced string concatenation (`+`) with dynamic multi-line **Template Literals** (`${expression}`).
   - Replaced legacy `function()` syntax and manual `var self = this;` with **Arrow Functions** preserving lexical `this`.
   - Replaced manual `arguments` and array slicing with **Rest parameters** (`...extraTags`) and **Spread syntax** (`[...items]`).
   - Integrated live **ES6+ Viva Inspector Modal** directly into the UI for before/after comparison.

2. **Array Methods on Realistic Dataset (`SERVICES_DATA`)**:
   - **`filter()`**: Dynamic real-time search matching name/description and category filtering.
   - **`map()`**: Transforms data into accessible HTML card markup and generates unique category filter buttons with `new Set()`.
   - **`reduce()`**: Computes multi-metric aggregates in a single pass (total matching services, provisioned node count, active monthly budget spend, average SLA uptime %).

3. **Destructuring & Modern Syntax**:
   - **Object Destructuring**: `const { id, name, category, monthlyPrice, rating, uptime, specs } = service;`
   - **Nested & Default Destructuring**: `specs: { cpu = 'N/A', ram = 'N/A' } = {}`
   - **Array Destructuring**: `const [firstHighlight, ...rest] = tags;`
   - **Spread Operator**: `[...SERVICES_DATA]` for immutable sorting and state updates.

---

## 📁 Repository Structure

```text
Cynaris-Internship/
├── index.html                   # Semantic Landing Page (4 Core Sections + JS Fundamentals + To-Do Nav)
├── js_fundamentals.js           # Week 2 Day 3: 10 ES6+ Functions, Promises, Async/Await, Fetch, CLI Runner
├── test_suite.js                # Week 2 Day 3: Automated 17-Test Assertion & Integration Suite
├── todo_app/                    # Week 2 Day 4: Dynamic To-Do List Application
│   ├── index.html               # Semantic accessible To-Do interface & concept reference grid
│   ├── style.css                # Responsive mobile-first stylesheet & dark/glassmorphic theme
│   └── script.js                # DOM manipulation, event delegation, & localStorage engine
├── weather_app/                 # Week 2 Day 5: Frontend Mini Project (Weather App)
│   ├── index.html               # Semantic accessible weather interface & telemetry metrics
│   ├── style.css                # Mobile-first stylesheet & weather-reactive dynamic themes
│   ├── script.js                # Asynchronous fetch(), async/await, and error handling engine
│   └── config.example.js        # Optional API key segregation template & documentation
├── css/
│   ├── style.css                # Mobile-first stylesheet (Flexbox, CSS Grid, 320/768/1024/1440px Breakpoints)
│   └── advanced_styles.css      # Week 2 Day 1: Themes, Keyframe Animations, Pseudo-Elements, Sticky Nav
├── js/
│   ├── js_fundamentals.js       # Module mirror / re-export for js/ directory compatibility
│   ├── main.js                  # Accessible mobile navigation toggle & window resize handler
│   └── script.js                # Week 1 Day 3 ES6+ implementation, array pipelines, & event listeners
├── screenshots/                 # Automated DevTools verification screenshots & audit report
│   ├── 320px_mobile.png
│   ├── 320px_mobile_drawer_open.png
│   ├── 768px_tablet.png
│   ├── 1024px_desktop.png
│   ├── 1440px_ultrawide.png
│   ├── features_grid_3col.png
│   ├── testimonials_grid_3col.png
│   ├── footer_responsive.png
│   └── audit_results.json
├── scripts/
│   └── capture_screenshots.py  # Headless Chrome DevTools Protocol automation & testing suite
└── README.md                    # Comprehensive internship documentation & specification
```

---

## 🎓 Viva-Ready Responsive Design Concepts

1. **Why is Mobile-First CSS preferred over Desktop-First?**
   - Mobile-first architecture writes lightweight, baseline CSS for constrained devices first, then progressively layers layout complexity using `min-width` media queries. Constrained mobile browsers avoid downloading and parsing complex multi-column overrides, leading to faster First Contentful Paint (FCP) and superior performance.

2. **When should you choose Flexbox vs CSS Grid?**
   - **Flexbox** is designed for **one-dimensional** layouts (either a row OR a column). It excels at content-driven sizing, distributing space along a single axis, and component-level layouts such as navigation bars, form input groups, and button clusters.
   - **CSS Grid** is designed for **two-dimensional** layouts (rows AND columns simultaneously). It excels at page-level scaffolding, uniform card grids (like Features and Testimonials), and strict grid track alignments.

3. **What causes horizontal overflow at 320px and how is it prevented?**
   - Horizontal overflow occurs when elements exceed the viewport width, caused by fixed pixel widths (e.g. `width: 500px`), unconstrained media elements, box model padding without `box-sizing: border-box`, or long unbroken strings.
   - It is eliminated by:
     1. Applying universal `box-sizing: border-box` to all elements.
     2. Setting `max-width: 100%` and fluid column rules (`1fr`, `repeat(auto-fit, minmax(...))`).
     3. Using `overflow-wrap: break-word` and `word-break: break-word` on headings and URLs.
     4. Enforcing `min-width: 0` on flex and grid children.
     5. Correctly configuring `<meta name="viewport" content="width=device-width, initial-scale=1.0">`.

4. **Why use relative units (`rem`, `clamp()`) instead of absolute units (`px`)?**
   - Absolute `px` values do not respect user operating system or browser font size preferences (e.g. users requiring 20px root text for accessibility).
   - `rem` scales proportionally relative to the root font size, guaranteeing accessibility. `clamp(min, preferred, max)` enables smooth, continuous fluid typography and gutter scaling without sudden layout shifts at breakpoint boundaries.
