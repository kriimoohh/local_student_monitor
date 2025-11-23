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
 * Charts module for Student Monitor dashboard.
 *
 * @module     local_student_monitor/charts
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    /**
     * Initialize charts.
     *
     * @param {Object} chartsData - Data for all charts
     */
    var init = function(chartsData) {
        // Check if Chart.js is loaded.
        if (typeof Chart === 'undefined') {
            requireChartJS(function() {
                initializeAllCharts(chartsData);
            });
        } else {
            initializeAllCharts(chartsData);
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
     * Initialize all charts.
     *
     * @param {Object} chartsData - Data for all charts
     */
    var initializeAllCharts = function(chartsData) {
        if (chartsData.riskDistribution) {
            initRiskDistributionChart(chartsData.riskDistribution);
        }
        if (chartsData.notificationTrends) {
            initNotificationTrendsChart(chartsData.notificationTrends);
        }
        if (chartsData.activityTrends) {
            initActivityTrendsChart(chartsData.activityTrends);
        }
        if (chartsData.interventionStats) {
            initInterventionStatsChart(chartsData.interventionStats);
        }
    };

    /**
     * Initialize risk distribution pie chart.
     *
     * @param {Object} data - Risk distribution data
     */
    var initRiskDistributionChart = function(data) {
        var ctx = document.getElementById('riskDistributionChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [data.labels.faible, data.labels.moyen, data.labels.eleve, data.labels.critique],
                datasets: [{
                    data: [data.faible, data.moyen, data.eleve, data.critique],
                    backgroundColor: [
                        '#28a745', // Green for FAIBLE
                        '#ffc107', // Yellow for MOYEN
                        '#fd7e14', // Orange for ÉLEVÉ
                        '#dc3545'  // Red for CRITIQUE
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    };

    /**
     * Initialize notification trends line chart.
     *
     * @param {Object} data - Notification trends data
     */
    var initNotificationTrendsChart = function(data) {
        var ctx = document.getElementById('notificationTrendsChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: data.sentLabel,
                        data: data.sent,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: data.readLabel,
                        data: data.read,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
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
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    };

    /**
     * Initialize activity trends bar chart.
     *
     * @param {Object} data - Activity trends data
     */
    var initActivityTrendsChart = function(data) {
        var ctx = document.getElementById('activityTrendsChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.datasetLabel,
                    data: data.values,
                    backgroundColor: '#17a2b8',
                    borderColor: '#117a8b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: data.title,
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: 20
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    };

    /**
     * Initialize intervention stats horizontal bar chart.
     *
     * @param {Object} data - Intervention stats data
     */
    var initInterventionStatsChart = function(data) {
        var ctx = document.getElementById('interventionStatsChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.datasetLabel,
                    data: data.values,
                    backgroundColor: [
                        '#dc3545',
                        '#ffc107',
                        '#28a745',
                        '#17a2b8'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: data.title,
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: 20
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
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
