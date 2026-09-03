/**
 * ============================================================================
 * Cynaris Solutions - Enterprise Cloud & AI Infrastructure
 * Week 1 Day 3: Modern JavaScript (ES6+) Basics & Interactive Services Explorer
 * ============================================================================
 * 
 * Key ES6+ Topics Covered:
 *  1. const and let (block-scoped variable declarations instead of var)
 *  2. Arrow Functions (() => {}) with implicit and explicit returns
 *  3. Template Literals (`... ${expr}`) for dynamic strings and HTML templates
 *  4. Object & Array Destructuring with default values and renaming
 *  5. Spread & Rest Operators (...args, [...array], {...object})
 *  6. Array Methods: filter(), map(), reduce(), find(), forEach()
 *  7. ES5 to ES6+ Refactoring Showcase (Viva-ready comparisons)
 *  8. DOM Event Listeners and interactive UI state management
 * ============================================================================
 */

// Strict mode ensures cleaner code and prevents silent errors
'use strict';

/* ============================================================================
   SECTION 1: ES5 vs ES6+ Refactoring Demonstration
   Viva Highlight: Shows before & after transition from legacy ES5 to modern ES6+
   ============================================================================ */

/**
 * ES5 Legacy Approach (Reference for Viva):
 * -------------------------------------------------------------
 * var LegacyCalculator = function(items) {
 *     var self = this;
 *     this.items = items || [];
 *     this.calculateTotal = function(taxRate) {
 *         var rate = taxRate !== undefined ? taxRate : 0.18;
 *         var subtotal = 0;
 *         for (var i = 0; i < self.items.length; i++) {
 *             subtotal = subtotal + self.items[i].price;
 *         }
 *         return "Total cost: $" + (subtotal + (subtotal * rate)).toFixed(2);
 *     };
 * };
 */

// Modern ES6+ Refactored Implementation:
// - Default parameters replace manual undefined checks
// - Arrow functions maintain lexical `this` context
// - const/let replace function-scoped var to avoid hoisting bugs
// - Array.prototype.reduce() replaces manual for-loop accumulation
// - Template literals replace string concatenation (+)
const createCostCalculator = (items = []) => ({
    items: [...items], // Spread operator creates shallow copy to prevent mutation
    calculateTotal(taxRate = 0.18) {
        // reduce accumulates item prices cleanly in a single functional pass
        const subtotal = this.items.reduce((acc, { monthlyPrice = 0 }) => acc + monthlyPrice, 0);
        const total = subtotal * (1 + taxRate);
        return `Total Estimated Allocation: $${total.toFixed(2)} (incl. ${(taxRate * 100).toFixed(0)}% tax)`;
    }
});

// Refactored helper demonstrating Rest parameters & Destructuring
const formatServiceSummary = (service, ...extraTags) => {
    // Object destructuring extracts specific properties directly
    const { name, category, monthlyPrice, rating = 5.0 } = service;
    const allTags = [category, ...extraTags]; // Array spread combines categories and tags
    
    // Template literal handles multi-variable string interpolation
    return `[${name}] Category: ${category} | Price: $${monthlyPrice}/mo | Rating: ${rating}★ | Tags: ${allTags.join(', ')}`;
};

/* ============================================================================
   SECTION 2: Realistic Dataset (Enterprise Cloud Services)
   ============================================================================ */

const SERVICES_DATA = [
    {
        id: 'srv-01',
        name: 'Multi-Cloud Kubernetes Engine',
        category: 'Cloud Native',
        monthlyPrice: 349,
        rating: 4.9,
        uptime: 99.99,
        isProvisioned: true,
        icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>`,
        description: 'Automated container orchestration and microservice clusters across AWS, Azure, and Google Cloud with auto-healing nodes.',
        specs: { cpu: '16 vCPU', ram: '64 GB', region: 'Global Edge' },
        tags: ['Containers', 'K8s', 'Zero-Downtime']
    },
    {
        id: 'srv-02',
        name: 'Zero-Trust IAM & Shield',
        category: 'Security',
        monthlyPrice: 289,
        rating: 4.8,
        uptime: 99.98,
        isProvisioned: true,
        icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
        description: 'Strict identity governance, automated TLS rotation, mutual auth, and continuous perimeter threat intelligence.',
        specs: { cpu: '8 vCPU', ram: '32 GB', region: 'Multi-Region' },
        tags: ['SOC2', 'HIPAA', 'Encryption']
    },
    {
        id: 'srv-03',
        name: 'AI Predictive Telemetry Core',
        category: 'AI Observability',
        monthlyPrice: 429,
        rating: 5.0,
        uptime: 99.95,
        isProvisioned: false,
        icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>`,
        description: 'Deep neural log parsing, anomaly forecasting, and autonomous remediation pipelines preventing unplanned outages.',
        specs: { cpu: '32 vCPU', ram: '128 GB', region: 'US-East & EU-Central' },
        tags: ['AI Ops', 'Real-time', 'Auto-Healing']
    },
    {
        id: 'srv-04',
        name: 'GitOps Continuous CI/CD Hub',
        category: 'DevOps',
        monthlyPrice: 199,
        rating: 4.7,
        uptime: 99.92,
        isProvisioned: true,
        icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>`,
        description: 'Declarative pipelines with automated canary releases, integrated vulnerability scans, and rollbacks in under 30s.',
        specs: { cpu: '8 vCPU', ram: '16 GB', region: 'Global' },
        tags: ['GitOps', 'Canary', 'Fast-Deploy']
    },
    {
        id: 'srv-05',
        name: 'Ultra-Low Latency CDN Node',
        category: 'Edge Computing',
        monthlyPrice: 159,
        rating: 4.9,
        uptime: 99.99,
        isProvisioned: false,
        icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>`,
        description: 'Geo-distributed caching with anycast routing across 120+ points of presence for instant dynamic content delivery.',
        specs: { cpu: '4 vCPU', ram: '16 GB', region: '120+ Edge Locations' },
        tags: ['Edge CDN', '<15ms', 'Anycast']
    },
    {
        id: 'srv-06',
        name: 'Elastic Serverless Gateway',
        category: 'Cloud Native',
        monthlyPrice: 219,
        rating: 4.6,
        uptime: 99.90,
        isProvisioned: false,
        icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>`,
        description: 'Instant event-driven compute scaling from 0 to 100,000 requests per second with micro-billing efficiency.',
        specs: { cpu: 'Dynamic', ram: 'Dynamic', region: 'Global Mesh' },
        tags: ['Serverless', 'Auto-Scale', 'Cost-Optimized']
    }
];

/* ============================================================================
   SECTION 3: ES6+ Array Methods Processing Engine
   Implements filter(), map(), and reduce() with pure functional patterns
   ============================================================================ */

/**
 * State object holding active UI filters
 * Using `let` for state values that update during user interaction
 */
const appState = {
    services: [...SERVICES_DATA], // Deep copy initial dataset
    selectedCategory: 'All',
    searchQuery: '',
    sortBy: 'default' // 'default', 'price-asc', 'price-desc', 'rating'
};

/**
 * Filter Services using Array.prototype.filter()
 * Demonstrates: Arrow function, object destructuring in parameter, string methods
 */
const filterServices = (services, { category, searchQuery }) => {
    return services.filter(service => {
        // Destructure properties from the current service item
        const { name, category: serviceCategory, description } = service;
        
        const matchesCategory = category === 'All' || serviceCategory.toLowerCase() === category.toLowerCase();
        
        const query = searchQuery.trim().toLowerCase();
        const matchesQuery = query === '' || 
            name.toLowerCase().includes(query) || 
            description.toLowerCase().includes(query);
            
        return matchesCategory && matchesQuery;
    });
};

/**
 * Sort Services using Array.prototype.sort() and Spread operator
 * Spread operator ensures we don't mutate the original array in place
 */
const sortServices = (services, sortBy) => {
    const sorted = [...services];
    
    switch (sortBy) {
        case 'price-asc':
            return sorted.sort((a, b) => a.monthlyPrice - b.monthlyPrice);
        case 'price-desc':
            return sorted.sort((a, b) => b.monthlyPrice - a.monthlyPrice);
        case 'rating':
            return sorted.sort((a, b) => b.rating - a.rating);
        default:
            return sorted;
    }
};

/**
 * Calculate Metrics using Array.prototype.reduce()
 * Demonstrates: Accumulator object pattern to calculate count, sum, and averages in one pass
 */
const calculateMetrics = (services) => {
    // If empty list, return safe defaults
    if (services.length === 0) {
        return {
            totalServices: 0,
            provisionedCount: 0,
            totalMonthlySpend: 0,
            avgUptime: '0.00%'
        };
    }

    const { provisionedCount, totalMonthlySpend, sumUptime } = services.reduce(
        (acc, { isProvisioned, monthlyPrice, uptime }) => {
            if (isProvisioned) {
                acc.provisionedCount += 1;
                acc.totalMonthlySpend += monthlyPrice;
            }
            acc.sumUptime += uptime;
            return acc;
        },
        { provisionedCount: 0, totalMonthlySpend: 0, sumUptime: 0 } // Initial accumulator
    );

    const avgUptime = (sumUptime / services.length).toFixed(2);

    return {
        totalServices: services.length,
        provisionedCount,
        totalMonthlySpend,
        avgUptime: `${avgUptime}%`
    };
};

/* ============================================================================
   SECTION 4: DOM Rendering Functions (Template Literals & Destructuring)
   ============================================================================ */

/**
 * Generate star rating markup using Array.from and template literals
 */
const renderStars = (rating) => {
    const fullStars = Math.floor(rating);
    const starArray = Array.from({ length: 5 }, (_, idx) => idx < fullStars ? '★' : '☆');
    return `<span class="service-stars" aria-label="${rating} out of 5 stars">${starArray.join('')}</span>`;
};

/**
 * Render single service card markup using ES6+ Template Literals & Destructuring
 */
const createServiceCardHTML = (service) => {
    // Destructuring with default values and nested destructuring
    const {
        id,
        name,
        category,
        monthlyPrice,
        rating,
        uptime,
        isProvisioned,
        icon,
        description,
        specs: { cpu = 'N/A', ram = 'N/A', region = 'Global' } = {},
        tags = []
    } = service;

    const annualPrice = monthlyPrice * 12;
    const statusClass = isProvisioned ? 'status-active' : 'status-idle';
    const statusText = isProvisioned ? 'Provisioned' : 'Standby';
    const actionButtonText = isProvisioned ? 'Deprovision' : 'Provision Service';
    const actionButtonClass = isProvisioned ? 'btn-danger-outline' : 'btn-primary-sm';

    // Template literal returns clean, formatted HTML markup
    return `
        <article class="service-item-card ${isProvisioned ? 'is-provisioned' : ''}" data-service-id="${id}">
            <div class="service-card-header">
                <div class="service-icon-box" aria-hidden="true">
                    ${icon}
                </div>
                <div class="service-badges">
                    <span class="service-category-badge">${category}</span>
                    <span class="service-status-pill ${statusClass}">
                        <span class="status-dot"></span>
                        ${statusText}
                    </span>
                </div>
            </div>

            <h3 class="service-card-title">${name}</h3>
            <p class="service-card-desc">${description}</p>

            <div class="service-specs-list">
                <span class="spec-tag"><strong class="spec-label">Compute:</strong> ${cpu}</span>
                <span class="spec-tag"><strong class="spec-label">Memory:</strong> ${ram}</span>
                <span class="spec-tag"><strong class="spec-label">Region:</strong> ${region}</span>
            </div>

            <div class="service-tags-list">
                ${tags.map(tag => `<span class="service-tag">#${tag}</span>`).join('')}
            </div>

            <div class="service-card-footer">
                <div class="service-pricing">
                    <div class="price-value">$${monthlyPrice}<span class="price-period">/mo</span></div>
                    <div class="price-annual">$${annualPrice}/yr estimated</div>
                </div>
                <div class="service-sla-rating">
                    <span class="sla-text">SLA: <strong>${uptime}%</strong></span>
                    <div class="rating-box">
                        ${renderStars(rating)}
                        <span class="rating-val">${rating.toFixed(1)}</span>
                    </div>
                </div>
            </div>

            <div class="service-card-actions">
                <button type="button" 
                        class="btn ${actionButtonClass} service-action-btn" 
                        data-action="toggle-provision" 
                        data-service-id="${id}"
                        aria-pressed="${isProvisioned}">
                    ${actionButtonText}
                </button>
            </div>
        </article>
    `;
};

/**
 * Render all service cards using Array.prototype.map() and join('')
 */
const renderServicesGrid = (services, container) => {
    if (!container) return;

    if (services.length === 0) {
        container.innerHTML = `
            <div class="empty-state-card">
                <div class="empty-state-icon" aria-hidden="true">🔍</div>
                <h4 class="empty-state-title">No matching cloud services found</h4>
                <p class="empty-state-desc">Try clearing your search query or selecting another category filter.</p>
                <button type="button" class="btn btn-secondary-sm" id="btn-reset-filters">Reset All Filters</button>
            </div>
        `;
        return;
    }

    // Array.prototype.map() transforms each service object into an HTML string
    container.innerHTML = services.map(createServiceCardHTML).join('');
};

/**
 * Render dynamic Category Filter Buttons using Set, map(), and template literals
 */
const renderCategoryFilters = (services, activeCategory, container) => {
    if (!container) return;

    // Set guarantees unique category list, spread operator converts back to Array
    const categories = ['All', ...new Set(services.map(({ category }) => category))];

    container.innerHTML = categories.map(cat => {
        const isActive = cat.toLowerCase() === activeCategory.toLowerCase();
        return `
            <button type="button" 
                    class="filter-pill ${isActive ? 'is-active' : ''}" 
                    data-category="${cat}"
                    aria-pressed="${isActive}">
                ${cat}
            </button>
        `;
    }).join('');
};

/**
 * Update Metric Summary Ribbon in real time
 */
const updateMetricsDisplay = (metrics) => {
    const { totalServices, provisionedCount, totalMonthlySpend, avgUptime } = metrics;

    const countElem = document.querySelector('#stat-total-services');
    const provisionedElem = document.querySelector('#stat-provisioned-count');
    const spendElem = document.querySelector('#stat-monthly-spend');
    const uptimeElem = document.querySelector('#stat-avg-uptime');

    if (countElem) countElem.textContent = `${totalServices}`;
    if (provisionedElem) provisionedElem.textContent = `${provisionedCount}`;
    if (spendElem) spendElem.textContent = `$${totalMonthlySpend.toLocaleString()}`;
    if (uptimeElem) uptimeElem.textContent = avgUptime;
};

/**
 * Display a temporary Toast feedback notification
 */
const showToast = (message, type = 'info') => {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${type === 'success' ? '✓' : 'ℹ'}</span>
        <span class="toast-text">${message}</span>
    `;

    toastContainer.appendChild(toast);

    // Auto-remove toast after animation completes
    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

/* ============================================================================
   SECTION 5: Interactive Event Listeners & Controller
   ============================================================================ */

/**
 * Main Controller that coordinates state changes, calculations, and UI rendering
 */
const updateUI = () => {
    const { services, selectedCategory, searchQuery, sortBy } = appState;

    const gridContainer = document.querySelector('#services-grid');
    const categoryContainer = document.querySelector('#category-filters');

    // 1. Filter using ES6+ filter()
    const filtered = filterServices(services, { category: selectedCategory, searchQuery });

    // 2. Sort filtered results
    const sorted = sortServices(filtered, sortBy);

    // 3. Compute metrics using ES6+ reduce()
    const metrics = calculateMetrics(sorted);

    // 4. Render updated DOM
    renderServicesGrid(sorted, gridContainer);
    renderCategoryFilters(services, selectedCategory, categoryContainer);
    updateMetricsDisplay(metrics);
};

/**
 * Setup All Interactive Event Listeners
 */
const initEventListeners = () => {
    const searchInput = document.querySelector('#service-search');
    const categoryContainer = document.querySelector('#category-filters');
    const sortSelect = document.querySelector('#service-sort');
    const gridContainer = document.querySelector('#services-grid');
    const vivaDemoBtn = document.querySelector('#btn-show-viva-demo');
    const vivaModal = document.querySelector('#viva-modal');
    const vivaCloseBtn = document.querySelector('#btn-close-viva');

    // 1. Search input filter listener (real-time input event)
    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            appState.searchQuery = event.target.value;
            updateUI();
        });
    }

    // 2. Category filter pills (Event delegation on container)
    if (categoryContainer) {
        categoryContainer.addEventListener('click', (event) => {
            const button = event.target.closest('.filter-pill');
            if (!button) return;

            const { category } = button.dataset;
            if (category) {
                appState.selectedCategory = category;
                updateUI();
            }
        });
    }

    // 3. Sort Select dropdown listener
    if (sortSelect) {
        sortSelect.addEventListener('change', (event) => {
            appState.sortBy = event.target.value;
            updateUI();
        });
    }

    // 4. Service card actions (Event delegation for toggle provision)
    if (gridContainer) {
        gridContainer.addEventListener('click', (event) => {
            // Check for reset button in empty state
            if (event.target.id === 'btn-reset-filters') {
                appState.searchQuery = '';
                appState.selectedCategory = 'All';
                appState.sortBy = 'default';
                if (searchInput) searchInput.value = '';
                if (sortSelect) sortSelect.value = 'default';
                updateUI();
                showToast('Filters reset to default view', 'info');
                return;
            }

            // Check for provision toggle button
            const actionBtn = event.target.closest('[data-action="toggle-provision"]');
            if (!actionBtn) return;

            const { serviceId } = actionBtn.dataset;
            
            // Find target service using Array.prototype.find()
            const targetService = appState.services.find(s => s.id === serviceId);
            if (targetService) {
                targetService.isProvisioned = !targetService.isProvisioned;
                
                const actionVerb = targetService.isProvisioned ? 'provisioned' : 'deprovisioned';
                showToast(`${targetService.name} is now ${actionVerb}!`, targetService.isProvisioned ? 'success' : 'info');
                
                updateUI();
            }
        });
    }

    // 5. Viva / ES6+ Refactor Inspector Modal handlers
    if (vivaDemoBtn && vivaModal) {
        vivaDemoBtn.addEventListener('click', () => {
            vivaModal.classList.add('is-open');
            vivaModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        });
    }

    const closeModal = () => {
        if (!vivaModal) return;
        vivaModal.classList.remove('is-open');
        vivaModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    };

    if (vivaCloseBtn) {
        vivaCloseBtn.addEventListener('click', closeModal);
    }

    if (vivaModal) {
        vivaModal.addEventListener('click', (e) => {
            if (e.target === vivaModal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && vivaModal && vivaModal.classList.contains('is-open')) {
            closeModal();
        }
    });
};

/* ============================================================================
   SECTION 6: Initialization & Console Demonstration
   ============================================================================ */

/**
 * Initialize application once DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Render initial view
    updateUI();
    initEventListeners();

    // Log modern ES6+ demonstrations to the browser console for developer inspection
    console.log('%c Cynaris Solutions - Week 1 Day 3: JavaScript ES6+ Active ', 'background: #2563eb; color: #fff; font-weight: bold; padding: 4px 8px; border-radius: 4px;');
    
    // Demonstrate reduce() calculation in console
    const calculator = createCostCalculator(SERVICES_DATA);
    console.log('📌 ES6+ Cost Calculator Demo:', calculator.calculateTotal(0.18));

    // Demonstrate formatServiceSummary with Rest & Spread
    console.log('📌 ES6+ Destructuring & Rest Param Demo:');
    console.log(formatServiceSummary(SERVICES_DATA[0], 'Tier-1', 'Critical-Infra'));
    
    // Demonstrate filter() & map() outputs
    const cloudNativeNames = SERVICES_DATA
        .filter(({ category }) => category === 'Cloud Native')
        .map(({ name, monthlyPrice }) => `${name} ($${monthlyPrice}/mo)`);
    console.log('📌 ES6+ Filter & Map Demo (Cloud Native Services):', cloudNativeNames);
});
