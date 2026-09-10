/**
 * Automated Verification & Assertion Suite for Week 2 Day 3: JavaScript Fundamentals
 */
'use strict';

const assert = require('assert');
const JS = require('./js_fundamentals.js');

async function runTestSuite() {
    console.log('🧪 Starting Automated Unit & Integration Tests for Week 2 Day 3...\n');

    let passedTests = 0;
    let totalTests = 0;

    const test = (description, fn) => {
        totalTests++;
        try {
            fn();
            console.log(`  ✔ [PASS] ${description}`);
            passedTests++;
        } catch (err) {
            console.error(`  ✖ [FAIL] ${description}`);
            console.error(`    ${err.message}`);
        }
    };

    const asyncTest = async (description, fn) => {
        totalTests++;
        try {
            await fn();
            console.log(`  ✔ [PASS] ${description}`);
            passedTests++;
        } catch (err) {
            console.error(`  ✖ [FAIL] ${description}`);
            console.error(`    ${err.message}`);
        }
    };

    // 1. formatServerNode
    test('formatServerNode should apply default IP and status when omitted', () => {
        const result = JS.formatServerNode('auth-srv');
        assert.strictEqual(result, '[Node: AUTH-SRV] IP: 127.0.0.1 | Status: active');
    });

    test('formatServerNode should accept custom IP and status', () => {
        const result = JS.formatServerNode('db-srv', '10.0.0.5', 'standby');
        assert.strictEqual(result, '[Node: DB-SRV] IP: 10.0.0.5 | Status: standby');
    });

    // 2. calculateClusterCost
    test('calculateClusterCost should compute cost with defaults', () => {
        const result = JS.calculateClusterCost();
        // base 120, discount 0.1 -> 108, tax 0.18 -> 108 * 1.18 = 127.44
        assert.ok(result.includes('$127.44/mo'));
    });

    test('calculateClusterCost should handle custom base rate and tax', () => {
        const result = JS.calculateClusterCost(100, 0, 0.1);
        assert.ok(result.includes('$110.00/mo'));
    });

    // 3. aggregateResourceMetrics
    test('aggregateResourceMetrics should handle empty args safely', () => {
        const result = JS.aggregateResourceMetrics();
        assert.deepStrictEqual(result, { count: 0, sum: 0, avg: 0, min: 0, max: 0 });
    });

    test('aggregateResourceMetrics should calculate metrics with rest params', () => {
        const result = JS.aggregateResourceMetrics(10, 20, 30, 40);
        assert.strictEqual(result.count, 4);
        assert.strictEqual(result.sum, 100);
        assert.strictEqual(result.avg, 25);
        assert.strictEqual(result.min, 10);
        assert.strictEqual(result.max, 40);
    });

    // 4. mergeConfigurationProfiles
    test('mergeConfigurationProfiles should merge objects immutably using spread', () => {
        const base = { host: 'localhost', port: 8080 };
        const layer1 = { port: 3000, secure: true };
        const merged = JS.mergeConfigurationProfiles(base, layer1);
        assert.strictEqual(merged.host, 'localhost');
        assert.strictEqual(merged.port, 3000);
        assert.strictEqual(merged.secure, true);
        assert.strictEqual(base.port, 8080, 'Original base object must not be mutated');
    });

    // 5. cloneAndExtendClusterNodes
    test('cloneAndExtendClusterNodes should combine arrays and apply default tags', () => {
        const primary = [{ id: 'p1', tags: ['prod'] }];
        const additional = [{ id: 'a1' }];
        const combined = JS.cloneAndExtendClusterNodes(primary, additional, ['core']);
        assert.strictEqual(combined.length, 2);
        assert.ok(combined[0].tags.includes('core') && combined[0].tags.includes('prod'));
        assert.ok(combined[1].tags.includes('core'));
    });

    // 6. extractTelemetrySummary
    test('extractTelemetrySummary should extract nested properties with fallback', () => {
        const summary = JS.extractTelemetrySummary({
            clusterId: 'NODE-X',
            metrics: { cpuLoad: 55 }
        });
        assert.ok(summary.includes('NODE-X'));
        assert.ok(summary.includes('us-east-1')); // default region
        assert.ok(summary.includes('55%'));
    });

    // 7. filterAndTransformServices
    test('filterAndTransformServices should filter by rating and map structure', () => {
        const services = [
            { id: '1', name: 'S1', monthlyPrice: 20, rating: 4.8, category: 'AI' },
            { id: '2', name: 'S2', monthlyPrice: 10, rating: 4.1, category: 'AI' },
            { id: '3', name: 'S3', monthlyPrice: 30, rating: 4.9, category: 'Cloud' }
        ];
        const filtered = JS.filterAndTransformServices(services, 4.5, 'AI');
        assert.strictEqual(filtered.length, 1);
        assert.strictEqual(filtered[0].id, '1');
        assert.strictEqual(filtered[0].costFormatted, '$20/mo');
    });

    // 8. simulateAsyncHealthPing
    await asyncTest('simulateAsyncHealthPing should resolve with healthy status', async () => {
        const res = await JS.simulateAsyncHealthPing('test.endpoint', true, 10);
        assert.strictEqual(res.status, 'HEALTHY_200_OK');
    });

    await asyncTest('simulateAsyncHealthPing should reject on failure', async () => {
        let rejected = false;
        try {
            await JS.simulateAsyncHealthPing('test.endpoint', false, 10);
        } catch (err) {
            rejected = true;
            assert.ok(err.message.includes('timeout or connection refused'));
        }
        assert.ok(rejected, 'Promise should have been rejected');
    });

    // 9. fetchJSONPlaceholderPost
    await asyncTest('fetchJSONPlaceholderPost should fetch and parse real post', async () => {
        const post = await JS.fetchJSONPlaceholderPost(1);
        assert.strictEqual(post.id, 1);
        assert.strictEqual(typeof post.title, 'string');
        assert.strictEqual(typeof post.body, 'string');
    });

    // 10. fetchSafeApiData
    await asyncTest('fetchSafeApiData should handle success cleanly', async () => {
        const res = await JS.fetchSafeApiData('posts/2');
        assert.strictEqual(res.success, true);
        assert.strictEqual(res.error, null);
        assert.strictEqual(res.status, 200);
        assert.strictEqual(res.data.id, 2);
    });

    await asyncTest('fetchSafeApiData should handle 404 gracefully with fallback', async () => {
        const fallbackObj = { dummy: 'ok' };
        const res = await JS.fetchSafeApiData('non-existent-route-xyz-404', fallbackObj);
        assert.strictEqual(res.success, false);
        assert.strictEqual(res.status, 404);
        assert.ok(res.error.includes('404'));
        assert.deepStrictEqual(res.data, fallbackObj);
    });

    // Asynchronous Flow Demos
    await asyncTest('demonstratePromiseFlow should handle both resolve and reject', async () => {
        const pRes = await JS.demonstratePromiseFlow(true);
        assert.strictEqual(pRes.success, true);
        assert.ok(pRes.message.includes('[Promise Resolved]'));

        const pRej = await JS.demonstratePromiseFlow(false);
        assert.strictEqual(pRej.success, false);
        assert.ok(pRej.message.includes('[Promise Caught]'));
    });

    await asyncTest('demonstrateAsyncAwaitFlow should execute concurrent fetches', async () => {
        const result = await JS.demonstrateAsyncAwaitFlow(1);
        assert.strictEqual(result.success, true);
        assert.strictEqual(result.user.id, 1);
        assert.ok(result.postsCount > 0);
    });

    console.log(`\n========================================`);
    console.log(` AUDIT SUMMARY: ${passedTests}/${totalTests} Tests Passed`);
    console.log(`========================================\n`);

    if (passedTests !== totalTests) {
        process.exit(1);
    }
}

runTestSuite();
