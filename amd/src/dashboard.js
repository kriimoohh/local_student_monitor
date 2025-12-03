// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Dashboard JavaScript module.
 *
 * @module     local_student_monitor/dashboard
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    /**
     * Allowed risk level values for validation.
     * @type {string[]}
     */
    var ALLOWED_RISK_LEVELS = ['', 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL', 'FAIBLE', 'MOYEN', 'ÉLEVÉ', 'CRITIQUE'];

    /**
     * Validate and sanitize the risk level parameter.
     *
     * @param {string} risk - The risk level to validate
     * @returns {string} Sanitized risk level or empty string if invalid
     */
    var sanitizeRiskLevel = function(risk) {
        if (!risk) {
            return '';
        }

        // Convert to uppercase for comparison.
        var upperRisk = risk.toUpperCase().trim();

        // Check if it's an allowed value.
        if (ALLOWED_RISK_LEVELS.indexOf(upperRisk) !== -1) {
            return encodeURIComponent(upperRisk);
        }

        // Return empty string if not a valid risk level.
        return '';
    };

    /**
     * Build a safe URL with the risk parameter.
     *
     * @param {string} risk - The risk level parameter
     * @returns {string} Safe URL
     */
    var buildFilterUrl = function(risk) {
        var url = new URL(window.location.href);
        var sanitizedRisk = sanitizeRiskLevel(risk);

        if (sanitizedRisk) {
            url.searchParams.set('risk', sanitizedRisk);
        } else {
            url.searchParams.delete('risk');
        }

        return url.toString();
    };

    /**
     * Initialize dashboard.
     */
    var init = function() {
        // Add event listeners for interactive elements.

        // Auto-refresh functionality (optional).
        var autoRefresh = function() {
            // Refresh KPI cards every 60 seconds.
            setInterval(function() {
                // You can implement AJAX refresh here if needed.
            }, 60000);
        };

        // Filter handling with XSS protection.
        $('#risk-filter').on('change', function() {
            var risk = $(this).val();
            var safeUrl = buildFilterUrl(risk);
            window.location.href = safeUrl;
        });

        // Initialize tooltips.
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize any charts if needed.
        initCharts();
    };

    /**
     * Initialize charts (placeholder for future Chart.js integration).
     */
    var initCharts = function() {
        // Placeholder for Chart.js integration in future versions.
        // Example:
        // var ctx = document.getElementById('engagementChart');
        // if (ctx) {
        //     new Chart(ctx, {
        //         type: 'line',
        //         data: {...},
        //         options: {...}
        //     });
        // }
    };

    return {
        init: init
    };
});
