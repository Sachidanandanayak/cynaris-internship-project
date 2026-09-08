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
| **Week 2 – Day 1** | CSS Advanced (Themes, Animations, Pseudo-Elements, Sticky Nav) | `feature/week-2-day-1` |

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
An enterprise-grade, mobile-first responsive design architecture implemented for the **Cynaris Solutions Cloud & AI Platform**. Fully optimized across mobile (`320px`), tablet (`768px`), and desktop (`1200px`) devices using modern CSS media queries, scalable relative units (`rem`, `em`, `%`, `vw`/`vh`, `clamp()`), WCAG-compliant touch targets, zero horizontal overflow, and accessible mobile drawer navigation while preserving all Week 1 Day 3 JavaScript ES6+ interactive features.

### 🌟 Key Responsive Features & Architecture

1. **Mobile-First CSS Architecture**:
   - **Base Styles (Default)**: Written for small screen mobile viewports (starting at `320px`) outside any media queries.
   - **Progressive Enhancement**: Uses `min-width` media queries to layer layout complexity as the screen expands.
   - **No Desktop Overrides Needed**: Mobile devices download and evaluate the lightweight base rules without overriding desktop styles.

2. **Responsive Breakpoints Hierarchy**:

   | Breakpoint | Target Devices | Key Layout Behaviors |
   |---|---|---|
   | **Base (`>= 320px`)** | Small Smartphones | 1-column cards, full-width touch buttons, collapsible drawer navigation, stacked metric cards |
   | **`min-width: 30rem` (`480px`)** | Large Smartphones & Phablets | 2-column metrics summary ribbon, inline CTA buttons, auto-width inspector trigger |
   | **`min-width: 48rem` (`768px`)** | Tablets & Small Screens | 2-column features/services/testimonials grid, 4-column metric ribbon, 3-column stats ribbon, 3-column footer links |
   | **`min-width: 64rem` (`1024px`)** | Laptops & Small Desktops | Navigation transitions from hamburger drawer to horizontal flex navbar; hamburger hidden (`display: none`) |
   | **`min-width: 75rem` (`1200px`)** | Standard Desktop Displays | 3-column features, services, and testimonials grid; 2-column parent footer layout (brand + links grid); expanded container padding |
   | **`min-width: 90rem` (`1440px`)** | Large & Ultrawide Displays | Max container width constraint (`80rem` / `1280px`), enhanced padding, and high-DPI scaling |

3. **Relative & Scalable Units Strategy**:
   - **`rem` (Root EM)**: Used for typography, margins, paddings, border radii, and touch targets (`min-height: 2.75rem` / 44px) so elements respect user browser accessibility font-size preferences.
   - **`em`**: Used for contextual component spacing (badge padding, tag margins, icon spacing) scaling relative to the element's local font size.
   - **`%`**: Used for fluid grid columns (`1fr`, `repeat(2, 1fr)`, `repeat(3, 1fr)`), flexible image widths, and container scaling.
   - **`vw` / `vh`**: Viewport units used for modal maximum dimensions (`max-height: 88vh`, `max-width: min(94vw, 56.25rem)`) and backdrop filters.
   - **`clamp(min, preferred, max)`**: Fluid typography and fluid spacing allowing smooth continuous scaling without rigid jumps:
     - Hero title: `clamp(1.85rem, 5.5vw + 0.5rem, 3.5rem)`
     - Section titles: `clamp(1.5rem, 3.5vw + 0.5rem, 2.25rem)`
     - Container gutters: `clamp(1rem, 4vw, 2rem)`

4. **Zero Horizontal Overflow at 320px**:
   - Universal `box-sizing: border-box`.
   - Card headers, tags, and footers implement `flex-wrap: wrap`.
   - Grid and flex items enforce `min-width: 0` to prevent text expansion overflows.
   - `overflow-wrap: break-word` and `word-break: break-word` applied to long headings and URLs.
   - Code preview snippets utilize `overflow-x: auto; max-width: 100%;`.
   - Viewport meta tag properly configured: `<meta name="viewport" content="width=device-width, initial-scale=1.0">`.

5. **Accessible Mobile Navigation (`js/main.js`)**:
   - Animated hamburger button with accessible ARIA attributes (`aria-controls`, `aria-expanded`).
   - Smooth slide-down drawer menu on mobile viewports (< `1024px`).
   - Closes automatically on `Escape` key press (WCAG 2.1), backdrop/outside click, and navigation link click.
   - Auto-resets on window resize when crossing into desktop breakpoint.

6. **Preserved JavaScript ES6+ Components**:
   - Real-time service search, category pills, dynamic sorting, and provisioning calculations (`reduce()`) remain 100% operational on all screen sizes.
   - ES6+ Viva Inspector Modal is fully responsive with scrollable dual-column comparison on tablet/desktop and single-column on mobile.

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
   - **`filter()`**: Dynamic real-time search matching name/description and category filtering (All, Cloud Native, Security, AI Observability, DevOps, Edge Computing).
   - **`map()`**: Transforms data into accessible HTML card markup and generates unique category filter buttons with `new Set()`.
   - **`reduce()`**: Computes multi-metric aggregates in a single pass (total matching services, provisioned node count, active monthly budget spend, average SLA uptime %).

3. **Destructuring & Modern Syntax**:
   - **Object Destructuring**: `const { id, name, category, monthlyPrice, rating, uptime, specs } = service;`
   - **Nested & Default Destructuring**: `specs: { cpu = 'N/A', ram = 'N/A' } = {}`
   - **Array Destructuring**: `const [firstHighlight, ...rest] = tags;`
   - **Spread Operator**: `[...SERVICES_DATA]` for immutable sorting and state updates.

4. **Interactive UI Component & Event Listeners**:
   - **Real-Time Search Bar**: Triggers dynamic filtering on the `input` event.
   - **Category Filter Pills**: Interactive category filtering via event delegation.
   - **Sort Dropdown**: Sort by featured, price (asc/desc), or customer rating on `change` event.
   - **Dynamic Provisioning Toggle**: Allows interactive node provisioning/deprovisioning on each card with animated toast feedback and instantaneous metric recalculation.
   - **Zero Console Errors**: Clean, modular, strict-mode (`'use strict'`) architecture.

---

## 📁 Repository Structure

```text
Cynaris-Internship/
├── index.html          # Semantic Landing Page + Interactive ES6+ Services Explorer
├── css/
│   ├── style.css             # Week 1 mobile-first stylesheet (Flexbox, Grid, Media Queries)
│   └── advanced_styles.css   # Week 2 Day 1: Themes, Keyframe Animations, Pseudo-Elements, Sticky Nav
├── js/
│   ├── main.js         # Accessible mobile navigation toggle & window resize handler
│   └── script.js       # Week 1 Day 3 ES6+ implementation, array pipelines, & event listeners
└── README.md           # Project documentation and specifications
```

---

## 🧪 Testing & Verification Guide

1. **Responsive Viewport Testing (Chrome DevTools)**:
   - **320px (Mobile Portrait)**:
     - Verify layout stacks into a single column with zero horizontal scrolling (`scrollWidth === clientWidth`).
     - Click the mobile hamburger menu button to confirm the drawer opens, locks body scroll, and toggles `aria-expanded="true"`.
     - Click a navigation link or press `Escape` to confirm the menu closes smoothly.
     - Type in the search input and click category pills; verify cards filter dynamically.
     - Click **"Provision Service"** on any card; verify the toast notification displays and metric counters update.
   - **768px (Tablet Viewport)**:
     - Verify features, services, and testimonials display in a balanced 2-column grid.
     - Verify the metric ribbon displays 4 columns and the hero stats ribbon displays 3 columns.
     - Confirm search input and sort dropdown align horizontally.
   - **1200px (Desktop Viewport)**:
     - Verify the mobile hamburger button is completely hidden (`display: none`).
     - Verify horizontal flexbox navigation bar is visible with styled hover states.
     - Confirm features, services, and testimonials display in 3 columns.
     - Click **"ES6+ Viva Inspector"** to verify the dual-column comparison modal.

2. **Console Inspection**:
   - Open Developer Tools (`F12` or `Ctrl + Shift + I` -> **Console**).
   - Verify zero errors, zero 404s, and clean initialization logs for both Day 4 Responsive Navigation and Day 3 ES6+ explorer.

---

## 🎓 Viva-Ready Responsive Design Concepts

1. **Why is Mobile-First CSS preferred over Desktop-First?**
   - Writing mobile styles first ensures constrained devices download and parse only what they need without overriding bulky multi-column desktop rules. It enforces content prioritization and cleaner, scalable CSS using `min-width` queries.
2. **Why use `rem` instead of `px`?**
   - `px` is an absolute unit that ignores user accessibility settings in their browser or operating system. `rem` scales proportionally with the root font size, ensuring full accessibility and consistency across responsive layouts.
3. **What causes horizontal overflow at 320px and how do you prevent it?**
   - Caused by fixed widths (e.g. `width: 500px`), unconstrained images/SVGs, padding without `box-sizing: border-box`, or long unbroken words. Prevented with `box-sizing: border-box`, `max-width: 100%`, `min-width: 0` on flex items, and `overflow-wrap: break-word`.
