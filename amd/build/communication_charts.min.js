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
 * Communication charts module for SMS cost tracking.
 *
 * @module     local_student_monitor/communication_charts
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * Initialize communication charts.
     *
     * @param {Object} costData - Daily cost data
     */
    var init = function(costData) {
        // Check if Chart.js is loaded.
        if (typeof Chart === 'undefined') {
            requireChartJS(function() {
                initDailyCostsChart(costData);
            });
        } else {
            initDailyCostsChart(costData);
        }
    };

    /**
     * Load Chart.js library.
     *
     * @param {Function} callback - Callback after loading
     */
    var requireChartJS = function(callback) {
        require(['https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'], function() {
            callback();
        });
    };

    /**
     * Initialize daily costs chart.
     *
     * @param {Object} data - Cost data
     */
    var initDailyCostsChart = function(data) {
        var ctx = document.getElementById('dailyCostsChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.title + ' (' + data.currency + ')',
                    data: data.data,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: data.title,
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: 20
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                var value = context.parsed.y || 0;
                                return label + ': ' + value.toFixed(0) + ' ' + data.currency;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: function(value) {
                                return value + ' ' + data.currency;
                            }
                        }
                    }
                }
            }
        });
    };

    return {
        init: init
    };
});
