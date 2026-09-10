/**
 * ============================================================================
 * Cynaris Solutions - Enterprise Cloud & AI Infrastructure
 * Week 2 Day 3: JavaScript Fundamentals (ES6+, Promises, Async/Await, Fetch)
 * ============================================================================
 * 
 * Curriculum Requirements Covered:
 *  1. 10 Clear ES6+ Functions covering Arrow Functions, Default Parameters,
 *     Rest Parameters, Spread Operators, Destructuring, and Array Methods.
 *  2. Promise-based asynchronous flow with .then() and .catch().
 *  3. Modern async / await asynchronous syntax.
 *  4. Native fetch() calling the public JSONPlaceholder API.
 *  5. Graceful API error handling via try / catch / finally blocks.
 *  6. Runnable/testable in both Node.js (CLI) and browser (Interactive UI).
 *  7. Clear, concise comments explaining core JavaScript concepts.
 * ============================================================================
 */

'use strict';

/* ============================================================================
   PART 1: 10 ES6+ JAVASCRIPT FUNDAMENTAL FUNCTIONS
   ============================================================================ */

/**
 * 1. formatServerNode (Arrow Function & Default Parameters)
 * Concept: Arrow functions provide concise syntax with lexical `this` binding.
 * Default parameters allow fallback values if arguments are undefined or omitted.
 * 
 * @param {string} hostname - Server hostname identifier
 * @param {string} [ip='127.0.0.1'] - Node IP address (defaulted)
 * @param {string} [status='active'] - Operational status (defaulted)
 * @returns {string} Formatted server node descriptor
 */
const formatServerNode = (hostname = 'node-primary', ip = '127.0.0.1', status = 'active') => {
    // Default parameters prevent manual `||` checks which could mishandle falsy values (like 0 or '')
    return `[Node: ${hostname.toUpperCase()}] IP: ${ip} | Status: ${status.toLowerCase()}`;
};

/**
 * 2. calculateClusterCost (Arrow Function, Arithmetic, Default Params, Template Literals)
 * Concept: Template literals (`... ${expression}`) allow multi-line strings
 * and direct variable interpolation without clumsy string concatenation.
 * 
 * @param {number} [baseRate=120] - Base monthly node cost in USD
 * @param {number} [discountRate=0.1] - Enterprise discount percentage (0 to 1)
 * @param {number} [taxRate=0.18] - Regional tax rate (0 to 1)
 * @returns {string} Calculated financial allocation string
 */
const calculateClusterCost = (baseRate = 120, discountRate = 0.1, taxRate = 0.18) => {
    const discountedRate = baseRate * (1 - discountRate);
    const totalCost = discountedRate * (1 + taxRate);
    return `Allocation: $${totalCost.toFixed(2)}/mo (Base: $${baseRate}, Discount: ${(discountRate * 100).toFixed(0)}%, Tax: ${(taxRate * 100).toFixed(0)}%)`;
};

/**
 * 3. aggregateResourceMetrics (Rest Parameters & Array reduce)
 * Concept: Rest parameter syntax (`...metricValues`) collects an arbitrary number
 * of arguments into an authentic Array, replacing legacy `arguments` objects.
 * 
 * @param {...number} metricValues - Variable count of telemetry readings
 * @returns {{ count: number, sum: number, avg: number, min: number, max: number }}
 */
const aggregateResourceMetrics = (...metricValues) => {
    // Handle empty rest arguments safely
    if (metricValues.length === 0) {
        return { count: 0, sum: 0, avg: 0, min: 0, max: 0 };
    }

    const sum = metricValues.reduce((accumulator, currentVal) => accumulator + currentVal, 0);
    const avg = Number((sum / metricValues.length).toFixed(2));
    const min = Math.min(...metricValues); // Using spread operator to pass array elements as arguments
    const max = Math.max(...metricValues);

    return { count: metricValues.length, sum, avg, min, max };
};

/**
 * 4. mergeConfigurationProfiles (Object Spread Operator & Rest Parameters)
 * Concept: Object spread (`{ ...obj }`) creates shallow copies and merges properties.
 * Later properties cleanly override earlier ones without mutating source objects.
 * 
 * @param {Object} baseConfig - Default configuration profile
 * @param {...Object} overrideLayers - Variable number of overriding configuration layers
 * @returns {Object} Immutably merged configuration profile
 */
const mergeConfigurationProfiles = (baseConfig = {}, ...overrideLayers) => {
    // Merges base config with each subsequent layer immutably using Array.prototype.reduce and object spread
    return overrideLayers.reduce((merged, currentLayer) => {
        return {
            ...merged,
            ...currentLayer,
            // Ensure nested timestamps reflect the latest update
            lastUpdated: new Date().toISOString()
        };
    }, { ...baseConfig });
};

/**
 * 5. cloneAndExtendClusterNodes (Array Spread Operator & Object Spread)
 * Concept: Array spread (`[...array]`) creates shallow copies and combines arrays
 * immutably, avoiding side-effects of Array.prototype.push or mutate methods.
 * 
 * @param {Array<Object>} [primaryNodes=[]] - Existing cluster nodes
 * @param {Array<Object>} [additionalNodes=[]] - New nodes to append
 * @param {Array<string>} [defaultTags=['cloud', 'cynaris']] - Global tags to apply
 * @returns {Array<Object>} New array of combined, tagged node objects
 */
const cloneAndExtendClusterNodes = (
    primaryNodes = [],
    additionalNodes = [],
    defaultTags = ['cloud', 'cynaris']
) => {
    // Combine both node lists immutably using the array spread operator
    const combinedList = [...primaryNodes, ...additionalNodes];

    // Map each node using object and array spread to append default tags
    return combinedList.map((node) => ({
        ...node,
        tags: [...new Set([...defaultTags, ...(node.tags || [])])]
    }));
};

/**
 * 6. extractTelemetrySummary (Destructuring: Nested, Defaults & Aliasing)
 * Concept: Destructuring extracts properties directly into local variables.
 * Supports default values for missing keys and aliasing (`prop: newName`).
 * 
 * @param {Object} [telemetryPacket={}] - Telemetry packet
 * @returns {string} Human-readable summary
 */
const extractTelemetrySummary = (telemetryPacket = {}) => {
    // Nested destructuring with property aliasing and default fallbacks
    const {
        clusterId = 'unknown-cluster',
        region: datacenterRegion = 'us-east-1', // aliased
        specs: { cpuCores = 4, ramGb = 16 } = {}, // nested destructuring with fallback
        metrics: { cpuLoad = 0, memoryUsage = 0 } = {}
    } = telemetryPacket;

    return `Cluster [${clusterId}] (${datacenterRegion}) - Cores: ${cpuCores}, RAM: ${ramGb}GB | CPU Load: ${cpuLoad}%, Mem: ${memoryUsage}%`;
};

/**
 * 7. filterAndTransformServices (Higher-Order Arrow Functions, Destructuring, Method Chaining)
 * Concept: Functional programming pipelines using arrow functions and destructuring
 * inside callback parameters make transformations clean, testable, and declarative.
 * 
 * @param {Array<Object>} [services=[]] - Cloud services collection
 * @param {number} [minRating=4.5] - Rating cutoff threshold
 * @param {string} [category='All'] - Category filter
 * @returns {Array<{ id: string, label: string, costFormatted: string, score: number }>}
 */
const filterAndTransformServices = (services = [], minRating = 4.5, category = 'All') => {
    return services
        // Parameter destructuring directly within the arrow function signature
        .filter(({ rating = 0, category: serviceCat = '' }) => {
            const matchesRating = rating >= minRating;
            const matchesCategory = category === 'All' || serviceCat.toLowerCase() === category.toLowerCase();
            return matchesRating && matchesCategory;
        })
        .map(({ id, name, monthlyPrice = 0, rating }) => ({
            id,
            label: `${name} (${rating}★)`,
            costFormatted: `$${monthlyPrice}/mo`,
            score: rating
        }))
        .sort((a, b) => b.score - a.score);
};

/**
 * 8. simulateAsyncHealthPing (Promise Creation, Asynchronous Flow)
 * Concept: A Promise represents an eventual completion or failure of an async task.
 * States: Pending -> Fulfilled (resolved) OR Rejected.
 * 
 * @param {string} [endpoint='api.cynaris.io/health'] - Target ping URL
 * @param {boolean} [shouldSucceed=true] - Simulates successful vs failed ping
 * @param {number} [delayMs=250] - Simulated network latency
 * @returns {Promise<{ endpoint: string, status: string, latencyMs: number, timestamp: string }>}
 */
const simulateAsyncHealthPing = (endpoint = 'api.cynaris.io/health', shouldSucceed = true, delayMs = 250) => {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            if (shouldSucceed) {
                resolve({
                    endpoint,
                    status: 'HEALTHY_200_OK',
                    latencyMs: delayMs,
                    timestamp: new Date().toISOString()
                });
            } else {
                reject(new Error(`Ping timeout or connection refused at [${endpoint}] after ${delayMs}ms`));
            }
        }, delayMs);
    });
};

/**
 * 9. fetchJSONPlaceholderPost (Modern async/await with native fetch)
 * Concept: `async` makes a function return a Promise; `await` pauses execution until
 * the awaited Promise settles. Native fetch() is standard in modern browsers and Node 18+.
 * 
 * @param {number} [postId=1] - ID of the post to query
 * @returns {Promise<{ id: number, title: string, body: string, userId: number }>}
 */
const fetchJSONPlaceholderPost = async (postId = 1) => {
    const url = `https://jsonplaceholder.typicode.com/posts/${postId}`;
    
    // fetch returns a Promise resolving to a Response object
    const response = await fetch(url);

    // HTTP 404/500 does NOT automatically reject fetch; must verify response.ok
    if (!response.ok) {
        throw new Error(`HTTP Error ${response.status}: Failed to fetch post with ID ${postId}`);
    }

    const postData = await response.json();
    return {
        id: postData.id,
        title: postData.title,
        body: postData.body,
        userId: postData.userId
    };
};

/**
 * 10. fetchSafeApiData (Defensive API Fetcher with try/catch/finally Graceful Handling)
 * Concept: Robust error handling wraps async network calls in try/catch/finally.
 * Prevents unhandled rejections and returns a structured status object.
 * 
 * @param {string} [endpointPath='users/1'] - API endpoint path (e.g. 'users/1', 'posts/1', or invalid path)
 * @param {*} [fallback=null] - Default data to return on failure
 * @returns {Promise<{ success: boolean, data: *, error: string|null, status: number|null, durationMs: number }>}
 */
const fetchSafeApiData = async (endpointPath = 'users/1', fallback = null) => {
    const startTime = Date.now();
    let httpStatus = null;

    try {
        const targetUrl = `https://jsonplaceholder.typicode.com/${endpointPath.replace(/^\/+/, '')}`;
        const response = await fetch(targetUrl);
        httpStatus = response.status;

        if (!response.ok) {
            throw new Error(`Endpoint returned status ${response.status} (${response.statusText || 'Error'})`);
        }

        const data = await response.json();
        return {
            success: true,
            data,
            error: null,
            status: httpStatus,
            durationMs: Date.now() - startTime
        };
    } catch (err) {
        // Graceful error capture: returns fallback without throwing an unhandled exception
        const errorMessage = err instanceof Error ? err.message : String(err);
        return {
            success: false,
            data: fallback,
            error: errorMessage,
            status: httpStatus,
            durationMs: Date.now() - startTime
        };
    }
};

/* ============================================================================
   PART 2: ASYNCHRONOUS FLOW DEMONSTRATIONS (.then/.catch vs async/await)
   ============================================================================ */

/**
 * Demonstrates classic Promise consumption using .then(), .catch(), and .finally()
 * @param {boolean} [shouldPass=true] - Toggle to test resolved or rejected path
 * @returns {Promise<{ success: boolean, message: string }>}
 */
const demonstratePromiseFlow = (shouldPass = true) => {
    return simulateAsyncHealthPing('edge.cynaris.cloud/healthz', shouldPass, 150)
        .then((result) => {
            // .then() executes upon successful resolution
            return {
                success: true,
                message: `[Promise Resolved]: ${result.endpoint} is ${result.status} in ${result.latencyMs}ms`
            };
        })
        .catch((error) => {
            // .catch() executes if the Promise is rejected or throws
            return {
                success: false,
                message: `[Promise Caught]: Gracefully handled -> ${error.message}`
            };
        });
};

/**
 * Demonstrates async/await consumption with Promise.all for concurrent fetching
 * @param {number} [userId=1] - User ID to query
 * @returns {Promise<{ success: boolean, user?: Object, postsCount?: number, error?: string }>}
 */
const demonstrateAsyncAwaitFlow = async (userId = 1) => {
    try {
        // Run concurrent requests cleanly using Promise.all + await
        const [userRes, postsRes] = await Promise.all([
            fetch(`https://jsonplaceholder.typicode.com/users/${userId}`),
            fetch(`https://jsonplaceholder.typicode.com/posts?userId=${userId}`)
        ]);

        if (!userRes.ok || !postsRes.ok) {
            throw new Error(`Failed to fetch user or posts for userId: ${userId}`);
        }

        const user = await userRes.json();
        const posts = await postsRes.json();

        return {
            success: true,
            user: { id: user.id, name: user.name, email: user.email, company: user.company?.name },
            postsCount: Array.isArray(posts) ? posts.length : 0
        };
    } catch (error) {
        return {
            success: false,
            error: error instanceof Error ? error.message : String(error)
        };
    }
};

/* ============================================================================
   PART 3: TEST SUITE & DEMONSTRATION RUNNER
   ============================================================================ */

/**
 * Executes all 10 fundamental functions with sample data and returns a structured audit
 * @returns {Promise<Array<{ testId: number, name: string, concept: string, result?: any, error?: string, passed: boolean }>>}
 */
const runAllFundamentals = async () => {
    const results = [];

    // Test 1: formatServerNode (Arrow & Default Params)
    try {
        const res1 = formatServerNode('edge-gateway-01');
        results.push({
            testId: 1,
            name: 'formatServerNode',
            concept: 'Arrow Functions & Default Parameters',
            result: res1,
            passed: typeof res1 === 'string' && res1.includes('127.0.0.1')
        });
    } catch (err) {
        results.push({ testId: 1, name: 'formatServerNode', concept: 'Arrow & Defaults', error: err.message, passed: false });
    }

    // Test 2: calculateClusterCost (Template Literals & Math)
    try {
        const res2 = calculateClusterCost(200, 0.15, 0.18);
        results.push({
            testId: 2,
            name: 'calculateClusterCost',
            concept: 'Template Literals & Arithmetic',
            result: res2,
            passed: res2.includes('$200.60/mo')
        });
    } catch (err) {
        results.push({ testId: 2, name: 'calculateClusterCost', concept: 'Template Literals', error: err.message, passed: false });
    }

    // Test 3: aggregateResourceMetrics (Rest Parameters & reduce)
    try {
        const res3 = aggregateResourceMetrics(45, 60, 85, 90, 70);
        results.push({
            testId: 3,
            name: 'aggregateResourceMetrics',
            concept: 'Rest Parameters (...args) & reduce()',
            result: res3,
            passed: res3.count === 5 && res3.sum === 350 && res3.avg === 70 && res3.min === 45 && res3.max === 90
        });
    } catch (err) {
        results.push({ testId: 3, name: 'aggregateResourceMetrics', concept: 'Rest Parameters', error: err.message, passed: false });
    }

    // Test 4: mergeConfigurationProfiles (Object Spread Operator)
    try {
        const base = { env: 'production', timeoutMs: 5000, debug: false };
        const override = { timeoutMs: 10000, region: 'us-west-2' };
        const res4 = mergeConfigurationProfiles(base, override);
        results.push({
            testId: 4,
            name: 'mergeConfigurationProfiles',
            concept: 'Object Spread Operator ({...obj})',
            result: res4,
            passed: res4.timeoutMs === 10000 && res4.region === 'us-west-2' && res4.debug === false
        });
    } catch (err) {
        results.push({ testId: 4, name: 'mergeConfigurationProfiles', concept: 'Object Spread', error: err.message, passed: false });
    }

    // Test 5: cloneAndExtendClusterNodes (Array Spread Operator)
    try {
        const primary = [{ id: 'n1', role: 'master' }];
        const additional = [{ id: 'n2', role: 'worker', tags: ['k8s'] }];
        const res5 = cloneAndExtendClusterNodes(primary, additional, ['cynaris-cloud']);
        results.push({
            testId: 5,
            name: 'cloneAndExtendClusterNodes',
            concept: 'Array Spread Operator ([...arr])',
            result: res5,
            passed: res5.length === 2 && res5[1].tags.includes('k8s') && res5[0].tags.includes('cynaris-cloud')
        });
    } catch (err) {
        results.push({ testId: 5, name: 'cloneAndExtendClusterNodes', concept: 'Array Spread', error: err.message, passed: false });
    }

    // Test 6: extractTelemetrySummary (Destructuring)
    try {
        const packet = {
            clusterId: 'CYN-PROD-ALPHA',
            region: 'ap-south-1',
            specs: { cpuCores: 16, ramGb: 64 },
            metrics: { cpuLoad: 42.5, memoryUsage: 68.2 }
        };
        const res6 = extractTelemetrySummary(packet);
        results.push({
            testId: 6,
            name: 'extractTelemetrySummary',
            concept: 'Nested Destructuring & Aliasing',
            result: res6,
            passed: res6.includes('CYN-PROD-ALPHA') && res6.includes('ap-south-1') && res6.includes('42.5%')
        });
    } catch (err) {
        results.push({ testId: 6, name: 'extractTelemetrySummary', concept: 'Destructuring', error: err.message, passed: false });
    }

    // Test 7: filterAndTransformServices (Method Chaining & Functional Pipeline)
    try {
        const sampleServices = [
            { id: 's1', name: 'Cloud CDN', category: 'Edge', monthlyPrice: 89, rating: 4.8 },
            { id: 's2', name: 'Legacy Proxy', category: 'Edge', monthlyPrice: 30, rating: 3.9 },
            { id: 's3', name: 'Zero Trust Guard', category: 'Security', monthlyPrice: 150, rating: 4.9 }
        ];
        const res7 = filterAndTransformServices(sampleServices, 4.5, 'All');
        results.push({
            testId: 7,
            name: 'filterAndTransformServices',
            concept: 'Higher-Order Functions (.filter / .map)',
            result: res7,
            passed: res7.length === 2 && res7[0].name === undefined && res7[0].score === 4.9
        });
    } catch (err) {
        results.push({ testId: 7, name: 'filterAndTransformServices', concept: 'Higher Order Functions', error: err.message, passed: false });
    }

    // Test 8: simulateAsyncHealthPing (Promise Resolution & Rejection)
    try {
        const res8Resolved = await simulateAsyncHealthPing('api.cynaris.io/health', true, 50);
        let caughtError = false;
        try {
            await simulateAsyncHealthPing('api.cynaris.io/down', false, 50);
        } catch {
            caughtError = true;
        }
        results.push({
            testId: 8,
            name: 'simulateAsyncHealthPing',
            concept: 'Promise Creation & .then() / .catch()',
            result: { resolved: res8Resolved.status, caughtError },
            passed: res8Resolved.status === 'HEALTHY_200_OK' && caughtError === true
        });
    } catch (err) {
        results.push({ testId: 8, name: 'simulateAsyncHealthPing', concept: 'Promises', error: err.message, passed: false });
    }

    // Test 9: fetchJSONPlaceholderPost (async/await & fetch)
    try {
        const res9 = await fetchJSONPlaceholderPost(1);
        results.push({
            testId: 9,
            name: 'fetchJSONPlaceholderPost',
            concept: 'async / await with JSONPlaceholder API',
            result: { id: res9.id, title: res9.title.slice(0, 30) + '...' },
            passed: res9.id === 1 && typeof res9.title === 'string'
        });
    } catch (err) {
        results.push({ testId: 9, name: 'fetchJSONPlaceholderPost', concept: 'async / await & fetch', error: err.message, passed: false });
    }

    // Test 10: fetchSafeApiData (Defensive try/catch handling)
    try {
        // Test valid call
        const validRes = await fetchSafeApiData('posts/1');
        // Test 404 failing call gracefully handled
        const invalidRes = await fetchSafeApiData('non-existent-endpoint-404', { fallback: true });
        results.push({
            testId: 10,
            name: 'fetchSafeApiData',
            concept: 'Graceful API Error Handling (try / catch)',
            result: {
                validCallSuccess: validRes.success,
                invalidCallGraceful: !invalidRes.success && invalidRes.data?.fallback === true
            },
            passed: validRes.success === true && invalidRes.success === false && invalidRes.data?.fallback === true
        });
    } catch (err) {
        results.push({ testId: 10, name: 'fetchSafeApiData', concept: 'try / catch Error Handling', error: err.message, passed: false });
    }

    return results;
};

/* ============================================================================
   PART 4: BROWSER INTERACTION & DOM CONTROLLER
   ============================================================================ */

/**
 * Wires up interactive buttons in index.html to show live test execution
 */
const initBrowserInteractions = () => {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    const consoleOutput = document.getElementById('fundamentals-console-output');
    const apiPreviewCard = document.getElementById('fundamentals-api-preview');
    const statusPill = document.getElementById('fundamentals-status-pill');

    const logToConsole = (title, data, isError = false) => {
        if (!consoleOutput) return;
        const timestamp = new Date().toLocaleTimeString();
        const formattedData = typeof data === 'object' ? JSON.stringify(data, null, 2) : String(data);
        const logEntry = document.createElement('div');
        logEntry.className = `console-log-entry ${isError ? 'error' : 'success'}`;
        logEntry.innerHTML = `
            <div class="console-entry-header">
                <span class="console-entry-time">[${timestamp}]</span>
                <span class="console-entry-title">${title}</span>
            </div>
            <pre class="console-entry-body"><code>${formattedData}</code></pre>
        `;
        consoleOutput.prepend(logEntry);
    };

    const updateStatus = (text, type = 'ready') => {
        if (!statusPill) return;
        statusPill.textContent = text;
        statusPill.className = `fundamentals-status-pill ${type}`;
    };

    // Button: Run All 10 ES6+ Functions
    const btnRunAll = document.getElementById('btn-run-all-fundamentals');
    if (btnRunAll) {
        btnRunAll.addEventListener('click', async () => {
            updateStatus('Executing All 10 Functions...', 'running');
            btnRunAll.disabled = true;
            try {
                const results = await runAllFundamentals();
                const passCount = results.filter(r => r.passed).length;
                logToConsole(`Run All Fundamentals Audit (${passCount}/${results.length} Passed)`, results);
                updateStatus(`${passCount}/${results.length} Passed`, passCount === results.length ? 'success' : 'warning');
            } catch (err) {
                logToConsole('Execution Error', err.message, true);
                updateStatus('Error', 'error');
            } finally {
                btnRunAll.disabled = false;
            }
        });
    }

    // Button: Test Promise Flow (.then/.catch)
    const btnTestPromise = document.getElementById('btn-test-promise-flow');
    if (btnTestPromise) {
        btnTestPromise.addEventListener('click', async () => {
            updateStatus('Testing Promise Flow...', 'running');
            try {
                // First demonstrate resolved path
                const resolvedRes = await demonstratePromiseFlow(true);
                logToConsole('Promise Flow: Resolved (.then)', resolvedRes);

                // Next demonstrate rejected path handled gracefully with .catch()
                const rejectedRes = await demonstratePromiseFlow(false);
                logToConsole('Promise Flow: Rejected Handled (.catch)', rejectedRes, true);

                updateStatus('Promise Flow Tested', 'success');
            } catch (err) {
                logToConsole('Promise Test Error', err.message, true);
                updateStatus('Error', 'error');
            }
        });
    }

    // Button: Fetch JSONPlaceholder Post (async/await)
    const btnFetchPost = document.getElementById('btn-fetch-post');
    if (btnFetchPost) {
        btnFetchPost.addEventListener('click', async () => {
            updateStatus('Fetching JSONPlaceholder Post...', 'running');
            try {
                const randomId = Math.floor(Math.random() * 10) + 1;
                const post = await fetchJSONPlaceholderPost(randomId);
                logToConsole(`fetch() Success: JSONPlaceholder /posts/${randomId}`, post);

                if (apiPreviewCard) {
                    apiPreviewCard.innerHTML = `
                        <div class="api-card-header">
                            <span class="api-tag">POST #${post.id}</span>
                            <span class="api-user-tag">User ID: ${post.userId}</span>
                        </div>
                        <h4 class="api-card-title">${post.title}</h4>
                        <p class="api-card-body">${post.body}</p>
                    `;
                }
                updateStatus(`Fetched Post #${post.id}`, 'success');
            } catch (err) {
                logToConsole('Fetch Post Error', err.message, true);
                updateStatus('Fetch Failed', 'error');
            }
        });
    }

    // Button: Fetch JSONPlaceholder User (async/await concurrency)
    const btnFetchUser = document.getElementById('btn-fetch-user');
    if (btnFetchUser) {
        btnFetchUser.addEventListener('click', async () => {
            updateStatus('Fetching User & Posts (Concurrency)...', 'running');
            try {
                const randomUserId = Math.floor(Math.random() * 5) + 1;
                const result = await demonstrateAsyncAwaitFlow(randomUserId);
                logToConsole(`Concurrent async/await: User #${randomUserId}`, result);

                if (apiPreviewCard && result.user) {
                    apiPreviewCard.innerHTML = `
                        <div class="api-card-header">
                            <span class="api-tag">USER #${result.user.id}</span>
                            <span class="api-user-tag">${result.postsCount} Linked Posts</span>
                        </div>
                        <h4 class="api-card-title">${result.user.name}</h4>
                        <p class="api-card-body"><strong>Email:</strong> ${result.user.email}<br><strong>Company:</strong> ${result.user.company || 'Enterprise'}</p>
                    `;
                }
                updateStatus(`Fetched User #${randomUserId}`, 'success');
            } catch (err) {
                logToConsole('Fetch User Error', err.message, true);
                updateStatus('Error', 'error');
            }
        });
    }

    // Button: Trigger Simulated API Error (try/catch graceful recovery)
    const btnSimulateError = document.getElementById('btn-simulate-error');
    if (btnSimulateError) {
        btnSimulateError.addEventListener('click', async () => {
            updateStatus('Simulating 404 API Endpoint...', 'running');
            try {
                const res = await fetchSafeApiData('invalid-endpoint-triggering-404', {
                    fallback: 'System recovered safely using default fallback profile'
                });
                logToConsole('Graceful Error Handling Demo (try/catch caught 404)', res, true);

                if (apiPreviewCard) {
                    apiPreviewCard.innerHTML = `
                        <div class="api-card-header">
                            <span class="api-tag" style="background: rgba(239, 68, 68, 0.2); color: #f87171;">Simulated 404 Handled</span>
                            <span class="api-user-tag">Status: ${res.status || 'N/A'}</span>
                        </div>
                        <h4 class="api-card-title" style="color: #f87171;">${res.error}</h4>
                        <p class="api-card-body"><strong>Graceful Fallback Data:</strong> ${JSON.stringify(res.data)}</p>
                    `;
                }
                updateStatus('Error Caught Gracefully', 'warning');
            } catch (err) {
                logToConsole('Unexpected Critical Crash', err.message, true);
            }
        });
    }

    // Button: Clear Console
    const btnClear = document.getElementById('btn-clear-fundamentals-console');
    if (btnClear && consoleOutput) {
        btnClear.addEventListener('click', () => {
            consoleOutput.innerHTML = '<div class="console-empty-state">Console cleared. Ready for execution.</div>';
            updateStatus('Idle', 'ready');
        });
    }
};

// Auto-initialize browser interactions when DOM is ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBrowserInteractions);
    } else {
        initBrowserInteractions();
    }
}

/* ============================================================================
   PART 5: NODE.JS CLI TEST EXECUTION
   ============================================================================ */

/**
 * Runs automated command-line execution when executed in Node.js
 */
const runCliRunnerIfMain = async () => {
    if (typeof process !== 'undefined' && process.argv && process.argv[1]) {
        const isRunDirectly = process.argv[1].replace(/\\/g, '/').endsWith('js_fundamentals.js');
        if (isRunDirectly) {
            console.log('\n' + '='.repeat(78));
            console.log(' CYNARIS SOLUTIONS - WEEK 2 DAY 3: JAVASCRIPT FUNDAMENTALS');
            console.log(' ES6+, Promises, async/await, JSONPlaceholder Fetch & Error Handling');
            console.log('='.repeat(78) + '\n');

            const results = await runAllFundamentals();
            let allPassed = true;

            results.forEach(({ testId, name, concept, result, error, passed }) => {
                const mark = passed ? '✔ PASS' : '✖ FAIL';
                console.log(`[${mark}] Function #${testId}: ${name}`);
                console.log(`       Concept : ${concept}`);
                if (passed) {
                    console.log(`       Output  : ${JSON.stringify(result)}`);
                } else {
                    console.log(`       Error   : ${error}`);
                    allPassed = false;
                }
                console.log('-'.repeat(78));
            });

            console.log('\n>> Demonstrating Promise Flow (.then / .catch):');
            const pPass = await demonstratePromiseFlow(true);
            console.log(`   ${pPass.message}`);
            const pFail = await demonstratePromiseFlow(false);
            console.log(`   ${pFail.message}`);

            console.log('\n>> Demonstrating async/await with JSONPlaceholder API:');
            const post = await fetchJSONPlaceholderPost(1);
            console.log(`   Fetched Post #1 Title: "${post.title}"`);

            console.log('\n>> Demonstrating Graceful try/catch Error Handling:');
            const safeRes = await fetchSafeApiData('invalid-endpoint-triggering-404', { fallback: 'Safe Fallback Data' });
            console.log(`   Handled 404 Status: ${safeRes.status} | Caught: "${safeRes.error}" | Fallback: "${safeRes.data.fallback}"`);

            console.log('\n' + '='.repeat(78));
            console.log(` SUMMARY: ${results.filter(r => r.passed).length}/${results.length} Functions Passed. Status: ${allPassed ? 'ALL TESTS PASSED' : 'TESTS FAILED'}`);
            console.log('='.repeat(78) + '\n');
        }
    }
};

runCliRunnerIfMain();

/* ============================================================================
   PART 6: UNIVERSAL EXPORTS (Node.js CommonJS & Browser Window)
   ============================================================================ */

const JSFundamentals = {
    // 10 Core Functions
    formatServerNode,
    calculateClusterCost,
    aggregateResourceMetrics,
    mergeConfigurationProfiles,
    cloneAndExtendClusterNodes,
    extractTelemetrySummary,
    filterAndTransformServices,
    simulateAsyncHealthPing,
    fetchJSONPlaceholderPost,
    fetchSafeApiData,
    // Flow Demonstrations & Suite Runners
    demonstratePromiseFlow,
    demonstrateAsyncAwaitFlow,
    runAllFundamentals
};

if (typeof module !== 'undefined' && module.exports) {
    module.exports = JSFundamentals;
}

if (typeof window !== 'undefined') {
    window.JSFundamentals = JSFundamentals;
}
