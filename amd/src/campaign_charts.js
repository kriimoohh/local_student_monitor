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
 * Campaign charts visualization.
 *
 * @module     local_student_monitor/campaign_charts
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    /**
     * Initialize campaign charts.
     *
     * @param {int} campaignId Campaign ID
     */
    var init = function(campaignId) {
        loadCampaignData(campaignId);
    };

    /**
     * Load campaign data via AJAX.
     *
     * @param {int} campaignId Campaign ID
     */
    var loadCampaignData = function(campaignId) {
        var promises = Ajax.call([{
            methodname: 'local_student_monitor_get_campaign_stats',
            args: {campaignid: campaignId}
        }]);

        promises[0].done(function(response) {
            var stats = JSON.parse(response.stats);
            renderFunnelChart(stats);
            if (stats.variant_stats) {
                renderABComparisonChart(stats.variant_stats);
            }
        }).fail(Notification.exception);
    };

    /**
     * Render conversion funnel chart.
     *
     * @param {object} stats Campaign statistics
     */
    var renderFunnelChart = function(stats) {
        var ctx = document.getElementById('funnelChart');
        if (!ctx) {
            return;
        }

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sent', 'Opened', 'Clicked', 'Converted'],
                    datasets: [{
                        label: 'Recipients',
                        data: [
                            stats.total_sent,
                            stats.total_opened,
                            stats.total_clicked,
                            stats.total_converted
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
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
                                stepSize: 1
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
                                    var label = context.label || '';
                                    var value = context.parsed.y || 0;
                                    var percentage = stats.total_sent > 0 ?
                                                   ((value / stats.total_sent) * 100).toFixed(2) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
    };

    /**
     * Render A/B comparison chart.
     *
     * @param {object} variantStats Variant statistics
     */
    var renderABComparisonChart = function(variantStats) {
        var ctx = document.getElementById('abComparisonChart');
        if (!ctx) {
            return;
        }

        var variantA = variantStats.a || {sent: 0, opened: 0, clicked: 0, converted: 0};
        var variantB = variantStats.b || {sent: 0, opened: 0, clicked: 0, converted: 0};

        var openRateA = variantA.sent > 0 ? ((variantA.opened / variantA.sent) * 100).toFixed(2) : 0;
        var openRateB = variantB.sent > 0 ? ((variantB.opened / variantB.sent) * 100).toFixed(2) : 0;

        var clickRateA = variantA.sent > 0 ? ((variantA.clicked / variantA.sent) * 100).toFixed(2) : 0;
        var clickRateB = variantB.sent > 0 ? ((variantB.clicked / variantB.sent) * 100).toFixed(2) : 0;

        var conversionRateA = variantA.sent > 0 ? ((variantA.converted / variantA.sent) * 100).toFixed(2) : 0;
        var conversionRateB = variantB.sent > 0 ? ((variantB.converted / variantB.sent) * 100).toFixed(2) : 0;

        require(['https://cdn.jsdelivr.net/npm/chart.js'], function(Chart) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Open Rate', 'Click Rate', 'Conversion Rate'],
                    datasets: [{
                        label: 'Variant A',
                        data: [openRateA, clickRateA, conversionRateA],
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Variant B',
                        data: [openRateB, clickRateB, conversionRateB],
                        backgroundColor: 'rgba(255, 99, 132, 0.8)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
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
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
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
