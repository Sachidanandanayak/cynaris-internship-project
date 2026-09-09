/**
 * ============================================================================
 * Cynaris Solutions - Enterprise Cloud & AI Infrastructure
 * Week 2 Day 2: Responsive Mobile Navigation & Accessibility Controller
 * ============================================================================
 * 
 * Features:
 *  - Mobile hamburger toggle with animated icon transformation
 *  - Accessible ARIA state synchronization (aria-expanded, aria-controls)
 *  - Keyboard accessibility (closes on ESC key press)
 *  - Click outside detection & auto-closing when navigation links are clicked
 *  - Automatic state cleanup on window resize past mobile breakpoint (>= 1024px)
 * ============================================================================
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.getElementById('primary-navigation');
    const navLinks = document.querySelectorAll('.nav-link, .nav-actions .btn');

    if (!navToggle || !navMenu) return;

    /**
     * Toggle Mobile Navigation Drawer
     * @param {boolean} [forceState] - Optional explicit state to set
     */
    const toggleNav = (forceState) => {
        const isCurrentlyOpen = navToggle.getAttribute('aria-expanded') === 'true';
        const shouldOpen = forceState !== undefined ? forceState : !isCurrentlyOpen;

        navToggle.setAttribute('aria-expanded', String(shouldOpen));
        navMenu.classList.toggle('is-active', shouldOpen);

        // Prevent body scroll when mobile menu is open on small viewports
        if (window.innerWidth < 1024) {
            document.body.style.overflow = shouldOpen ? 'hidden' : '';
        }
    };

    /**
     * Close navigation helper
     */
    const closeNav = () => {
        if (navToggle.getAttribute('aria-expanded') === 'true') {
            toggleNav(false);
        }
    };

    // Toggle on hamburger button click
    navToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleNav();
    });

    // Close when clicking any nav link
    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            closeNav();
        });
    });

    // Close on Escape key press for accessibility (WCAG 2.1)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeNav();
        }
    });

    // Close when clicking outside of the navigation container
    document.addEventListener('click', (e) => {
        const isClickInside = navMenu.contains(e.target) || navToggle.contains(e.target);
        if (!isClickInside) {
            closeNav();
        }
    });

    // Reset navigation state on window resize past mobile breakpoint
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            closeNav();
            document.body.style.overflow = '';
        }
    });

    // Clean initial state
    navToggle.setAttribute('aria-expanded', 'false');
    navMenu.classList.remove('is-active');

    console.log('%c Cynaris Solutions - Week 2 Day 2: Responsive Navigation Initialized ', 'background: #06b6d4; color: #0b0f19; font-weight: bold; padding: 4px 8px; border-radius: 4px;');
});
