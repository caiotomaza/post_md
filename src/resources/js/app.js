import './bootstrap';
import Alpine from 'alpinejs';
import workspace from './workspace.js';

// Register Alpine component
Alpine.data('workspace', workspace);

// Expose workspace instance for HTML event handlers (tree rows rendered via x-html)
document.addEventListener('alpine:init', () => {
    // noop
});

// Make workspace accessible from inline handlers after Alpine init
document.addEventListener('DOMContentLoaded', () => {
    // Defer to ensure Alpine has initialized
    setTimeout(() => {
        const el = document.getElementById('workspace');
        if (el && el._x_dataStack) {
            window._pmdWs = Alpine.$data(el);
        }
    }, 100);
});

window.Alpine = Alpine;
Alpine.start();
