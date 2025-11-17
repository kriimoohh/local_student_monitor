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
 * Student dashboard charts and interactions.
 *
 * @module     local_student_monitor/student_dashboard
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    /**
     * Initialize student dashboard.
     */
    var init = function() {
        loadActivityData();
        loadPerformanceData();
    };

    /**
     * Load weekly activity data.
     */
    var loadActivityData = function() {
        var promises = Ajax.call([{
            methodname: 'local_student_monitor_get_student_activity',
            args: {days: 7}
        }]);

        promises[0].done(function(response) {
            renderWeeklyActivityChart(response);
        }).fail(Notification.exception);
    };

    /**
     * Load performance trend data.
     */
    var loadPerformanceData = function() {
        var promises = Ajax.call([{
            methodname: 'local_student_monitor_get_performance_trend',
            args: {days: 30}
        }]);

        promises[0].done(function(response) {
            renderPerformanceTrendChart(response);
        }).fail(Notification.exception);
    };

    /**
     * Render weekly activity chart.
     *
     * @param {object} data Activity data
     */
    var renderWeeklyActivityChart = function(data) {
        var ctx = document.getElementById('weeklyActivityChart');
        if (!ctx) {
            return;
        }

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Activities',
                        data: data.daily_counts || [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
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
                                    return 'Activities: ' + context.parsed.y;
                                }
                            }
                        }
                    }
                }
            });
        });
    };

    /**
     * Render performance trend chart.
     *
     * @param {object} data Performance data
     */
    var renderPerformanceTrendChart = function(data) {
        var ctx = document.getElementById('performanceTrendChart');
        if (!ctx) {
            return;
        }

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.dates || [],
                    datasets: [{
                        label: 'Performance Score',
                        data: data.scores || [],
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
                                    return 'Score: ' + context.parsed.y + '%';
                                }
                            }
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
