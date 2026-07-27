/**
 * Adds the session CSRF token to every same-origin state-changing fetch request.
 */
(function installCsrfFetchProtection() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token || typeof window.fetch !== 'function') return;

    const originalFetch = window.fetch.bind(window);
    const safeMethods = new Set(['GET', 'HEAD', 'OPTIONS']);

    window.fetch = function csrfProtectedFetch(input, init) {
        const request = new Request(input, init);
        const requestUrl = new URL(request.url, window.location.href);

        if (
            requestUrl.origin !== window.location.origin ||
            safeMethods.has(request.method.toUpperCase())
        ) {
            return originalFetch(request);
        }

        const headers = new Headers(request.headers);
        headers.set('X-CSRF-Token', token);

        return originalFetch(new Request(request, { headers }));
    };
})();
