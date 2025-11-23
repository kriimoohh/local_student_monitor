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
 * BI Dashboard charts and visualizations.
 *
 * @module     local_student_monitor/bi_charts
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    /**
     * Initialize BI charts.
     *
     * @param {int} days Number of days to display
     */
    var init = function(days) {
        loadTrendData(days);
        loadRetentionData();
    };

    /**
     * Load trend data for charts.
     *
     * @param {int} days Number of days
     */
    var loadTrendData = function(days) {
        var promises = Ajax.call([{
            methodname: 'local_student_monitor_get_bi_trends',
            args: {days: days}
        }]);

        promises[0].done(function(response) {
            var data = JSON.parse(response.data);
            renderInterventionsTrendChart(data.daily_interventions);
            renderSuccessRateTrendChart(data.success_trend);
        }).fail(Notification.exception);
    };

    /**
     * Load retention data.
     */
    var loadRetentionData = function() {
        var promises = Ajax.call([{
            methodname: 'local_student_monitor_get_retention_data',
            args: {days: 90}
        }]);

        promises[0].done(function(response) {
            var data = JSON.parse(response.data);
            renderRetentionTrendChart(data.weekly_trend);
        }).fail(Notification.exception);
    };

    /**
     * Render daily interventions trend chart.
     *
     * @param {array} data Daily intervention data
     */
    var renderInterventionsTrendChart = function(data) {
        var ctx = document.getElementById('interventionsTrendChart');
        if (!ctx) {
            return;
        }

        var dates = [];
        var counts = [];

        for (var date in data) {
            dates.push(date);
            counts.push(data[date].count);
        }

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Interventions',
                        data: counts,
                        fill: true,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    };

    /**
     * Render success rate trend chart.
     *
     * @param {array} data Success rate trend data
     */
    var renderSuccessRateTrendChart = function(data) {
        var ctx = document.getElementById('successRateTrendChart');
        if (!ctx) {
            return;
        }

        var weeks = [];
        var rates = [];

        data.forEach(function(item) {
            weeks.push('Week ' + item.week);
            rates.push(item.rate);
        });

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: weeks,
                    datasets: [{
                        label: 'Success Rate',
                        data: rates,
                        fill: true,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Success Rate: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    }
                }
            });
        });
    };

    /**
     * Render retention trend chart.
     *
     * @param {array} data Retention trend data
     */
    var renderRetentionTrendChart = function(data) {
        var ctx = document.getElementById('retentionTrendChart');
        if (!ctx) {
            return;
        }

        var weeks = [];
        var rates = [];
        var actives = [];

        data.forEach(function(item) {
            weeks.push('Week ' + item.week);
            rates.push(item.rate);
            actives.push(item.active);
        });

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: weeks,
                    datasets: [{
                        label: 'Retention Rate',
                        data: rates,
                        yAxisID: 'y',
                        fill: true,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    }, {
                        label: 'Active Students',
                        data: actives,
                        yAxisID: 'y1',
                        fill: false,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    };

    return {
        init: init
    };
});
