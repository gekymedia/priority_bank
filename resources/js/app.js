import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Delay Alpine.js initialization to avoid conflicts with jQuery
// Only start Alpine.js if there are no critical errors
function initAlpine() {
    try {
        // Check if jQuery is being used on this page
        if (typeof jQuery !== 'undefined' && jQuery(document).ready) {
            // Wait for jQuery to be ready first
            jQuery(document).ready(function() {
                setTimeout(function() {
                    try {
                        Alpine.start();
                    } catch (e) {
                        console.warn('Alpine.js initialization skipped due to error:', e);
                    }
                }, 200);
            });
        } else {
            // No jQuery, start Alpine normally
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        try {
                            Alpine.start();
                        } catch (e) {
                            console.warn('Alpine.js initialization skipped due to error:', e);
                        }
                    }, 100);
                });
            } else {
                setTimeout(function() {
                    try {
                        Alpine.start();
                    } catch (e) {
                        console.warn('Alpine.js initialization skipped due to error:', e);
                    }
                }, 100);
            }
        }
    } catch (e) {
        console.warn('Alpine.js initialization failed:', e);
    }
}

initAlpine();
