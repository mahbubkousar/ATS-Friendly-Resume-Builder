/**
 * Shared Utility Functions
 * Consolidates common utilities used across the application
 */

/**
 * Debounce function - delays execution until after wait time has elapsed
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

/**
 * Logger utility for better production readiness
 */
class Logger {
    constructor(isDev = true) {
        this.isDev = isDev;
    }

    log(...args) {
        if (this.isDev) {
            console.log(...args);
        }
    }

    info(...args) {
        if (this.isDev) {
            console.info(...args);
        }
    }

    warn(...args) {
        console.warn(...args);
    }

    error(...args) {
        console.error(...args);
    }
}

/**
 * Async Button Handler - Standardizes async button behavior
 * Handles loading states and prevents double-clicks
 */
class AsyncButtonHandler {
    /**
     * Attach async handler to a button
     * @param {HTMLElement} button - Button element
     * @param {Function} asyncFn - Async function to execute
     * @param {string} loadingText - Text to display while loading
     */
    static attach(button, asyncFn, loadingText = 'Loading...') {
        if (!button) return;

        button.addEventListener('click', async () => {
            const originalHTML = button.innerHTML;
            const originalDisabled = button.disabled;

            // Set loading state
            button.disabled = true;
            button.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${loadingText}`;

            try {
                await asyncFn();
            } catch (error) {
                console.error('Button action failed:', error);
                throw error;
            } finally {
                // Restore original state
                button.disabled = originalDisabled;
                button.innerHTML = originalHTML;
            }
        });
    }
}

/**
 * DOM Cache - Caches frequently accessed DOM elements
 */
class DOMCache {
    constructor() {
        this.cache = new Map();
    }

    get(id) {
        if (!this.cache.has(id)) {
            const element = document.getElementById(id);
            if (element) {
                this.cache.set(id, element);
            }
        }
        return this.cache.get(id);
    }

    query(selector) {
        if (!this.cache.has(selector)) {
            const element = document.querySelector(selector);
            if (element) {
                this.cache.set(selector, element);
            }
        }
        return this.cache.get(selector);
    }

    clear() {
        this.cache.clear();
    }
}

/**
 * Format date to readable string
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date string
 */
function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Create global instances
const logger = new Logger(true);
const domCache = new DOMCache();
