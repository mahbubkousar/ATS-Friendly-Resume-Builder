/**
 * Resolves application routes independently of the deployment directory.
 *
 * This script lives at <app>/js/shared/app-url.js, so walking up two path
 * segments reliably identifies the application root.
 */
(function installApplicationUrlResolver() {
    'use strict';

    const scriptUrl = document.currentScript && document.currentScript.src;
    const applicationRoot = new URL('../../', scriptUrl || document.baseURI);

    window.appUrl = function appUrl(path = '') {
        return new URL(String(path).replace(/^\/+/, ''), applicationRoot).toString();
    };
})();
