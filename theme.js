/**
 * theme.js — Kenya Transparency Portal
 * Handles light/dark theme toggling with localStorage persistence,
 * system preference detection, and chart theme updates.
 */

document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;

    const themeIcon = themeToggle.querySelector('i');

    // ── Determine initial theme ──────────────────────────────────────────
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = savedTheme || (prefersDark ? 'dark' : 'light');

    applyTheme(initialTheme);

    // ── Toggle on button click ────────────────────────────────────────────
    themeToggle.addEventListener('click', function () {
        const current = document.documentElement.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });

    // ── Listen for system preference changes ─────────────────────────────
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
        // Only follow system preference if user hasn't manually chosen
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    /**
     * Apply a theme to the document.
     * @param {'light'|'dark'} theme
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);

        // Update icon
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeToggle.setAttribute('aria-label', 'Switch to light mode');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                themeToggle.setAttribute('aria-label', 'Switch to dark mode');
            }
        }

        // Update charts if they are ready
        updateChartsTheme(theme);
    }

    /**
     * Update Chart.js instances to match the current theme.
     * Falls back gracefully if charts are not yet loaded.
     * Also calls window.updateChartsTheme if defined (for page-specific logic).
     * @param {'light'|'dark'} theme
     */
    function updateChartsTheme(theme) {
        // Call page-level hook first (defined in the dashboard script)
        if (typeof window.updateChartsTheme === 'function') {
            window.updateChartsTheme(theme);
            return;
        }

        // Fallback: iterate all Chart.js instances directly
        if (!window.Chart || !Chart.instances) return;

        const isDark = theme === 'dark';
        const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
        const tickColor  = isDark ? '#6b7d71' : '#7a8a80';
        const legendColor = isDark ? '#b4c4ba' : '#374840';

        Object.values(Chart.instances).forEach(chart => {
            try {
                const scales = chart.options.scales;
                if (scales) {
                    ['x', 'y'].forEach(axis => {
                        if (scales[axis]) {
                            if (scales[axis].grid) scales[axis].grid.color = gridColor;
                            if (scales[axis].ticks) scales[axis].ticks.color = tickColor;
                            if (scales[axis].title) scales[axis].title.color = tickColor;
                        }
                    });
                }
                if (chart.options.plugins?.legend?.labels) {
                    chart.options.plugins.legend.labels.color = legendColor;
                }
                chart.update('none'); // 'none' = skip animation for theme change
            } catch (e) {
                // Silently skip if chart is not compatible
            }
        });
    }

    /**
     * If charts load after theme.js runs (async), retry after a short delay.
     * This handles cases where Chart.js initialises after DOMContentLoaded.
     */
    const currentTheme = document.documentElement.getAttribute('data-theme');
    if (currentTheme === 'dark') {
        let retries = 0;
        const maxRetries = 30; // 3 seconds
        const pollInterval = setInterval(function () {
            retries++;
            const hasCharts =
                window.Chart &&
                Chart.instances &&
                Object.keys(Chart.instances).length > 0;

            if (hasCharts || retries >= maxRetries) {
                clearInterval(pollInterval);
                if (hasCharts) updateChartsTheme('dark');
            }
        }, 100);
    }
});