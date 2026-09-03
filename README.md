# Cynaris Solutions Full Stack Development Internship

This repository contains tasks, projects, and learning modules for the **Cynaris Solutions Full Stack Development Internship** program.

---

## 📅 Program Structure

| Module | Topic | Branch / Status |
|---|---|---|
| **Week 1 – Day 1** | HTML5 Structure & Semantics | `feature/week-1-day-1` |
| **Week 1 – Day 2** | Responsive Design & Mobile-First Landing Page | `feature/week-1-day-2` |
| **Week 1 – Day 3** | JavaScript ES6+ Basics & Interactive Explorer | `feature/week-1-day-3` |

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
│   └── style.css       # Mobile-first stylesheet (Flexbox, Grid, Modal, Toast, Cards)
├── js/
│   ├── main.js         # Accessible mobile navigation toggle & interactions
│   └── script.js       # Week 1 Day 3 ES6+ implementation, array pipelines, & event listeners
└── README.md           # Project documentation and specifications
```

---

## 🧪 Testing & Verification Guide

1. **Browser Test**:
   - Open `index.html` in any modern web browser (e.g., Chrome, Edge, Firefox).
2. **Interactive Search & Filtering**:
   - Type `"Kubernetes"` or `"Edge"` in the search input to observe instantaneous card filtering.
   - Click on category filter pills (`Cloud Native`, `Security`, `AI Observability`, etc.) to filter services.
   - Use the **Sort** dropdown to sort by price or rating.
3. **Dynamic Calculations (`reduce`)**:
   - Click **"Provision Service"** on any card.
   - Notice the **Active Allocation ($)**, **Nodes Provisioned**, and **Card Status** update dynamically.
   - An animated confirmation toast will appear in the bottom-right corner.
4. **Viva Reference Modal**:
   - Click the **"ES6+ Viva Inspector"** button in the explorer controls to view side-by-side ES5 vs ES6+ code examples.
5. **Console Inspection**:
   - Open Developer Tools (`F12` or `Ctrl + Shift + I` -> **Console**).
   - Observe the clean, formatted ES6+ console logs showing demonstration outputs for `reduce()`, `filter()`, `map()`, and destructuring with zero errors or warnings.

---

## 💻 Git Workflow & Submission Steps

To commit and push your completed Week 1 Day 3 task to GitHub:

1. **Create and switch to the task branch**:
   ```bash
   git checkout -b feature/week-1-day-3
   ```

2. **Stage the changes**:
   ```bash
   git add .
   ```

3. **Commit with a descriptive message**:
   ```bash
   git commit -m "feat(javascript-es6): implement modern ES6+ services explorer, array methods, and interactive event controller"
   ```

4. **Push to your remote repository**:
   ```bash
   git push -u origin feature/week-1-day-3
   ```

5. **Create a Pull Request (PR)** on GitHub targeting the `main` or develop branch.

