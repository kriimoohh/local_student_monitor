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
 * Predictions charts AMD module.
 *
 * @module     local_student_monitor/predictions
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'], function($, Chart) {

    /**
     * Initialize predicted risk distribution chart.
     *
     * @param {Object} data Chart data
     */
    var initRiskChart = function(data) {
        var ctx = document.getElementById('riskChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    };

    /**
     * Initialize trend direction chart.
     *
     * @param {Object} data Chart data
     */
    var initTrendChart = function(data) {
        var ctx = document.getElementById('trendChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Number of students',
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 1,
                    borderColor: '#fff'
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
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    };

    /**
     * Highlight high-risk predictions.
     */
    var highlightHighRisk = function() {
        $('tbody tr').each(function() {
            var predictedRisk = $(this).find('td:nth-child(3) .badge').text().trim();
            if (predictedRisk === 'CRITIQUE') {
                $(this).addClass('table-danger');
            } else if (predictedRisk === 'ÉLEVÉ') {
                $(this).addClass('table-warning');
            }
        });
    };

    /**
     * Add sorting to table.
     */
    var initTableSorting = function() {
        $('table th').on('click', function() {
            var table = $(this).parents('table').eq(0);
            var rows = table.find('tbody tr').toArray().sort(compareRows($(this).index()));

            this.asc = !this.asc;
            if (!this.asc) {
                rows = rows.reverse();
            }

            for (var i = 0; i < rows.length; i++) {
                table.find('tbody').append(rows[i]);
            }
        });
    };

    /**
     * Compare rows for sorting.
     *
     * @param {Number} index Column index
     * @return {Function} Comparison function
     */
    var compareRows = function(index) {
        return function(a, b) {
            var valA = $(a).find('td').eq(index).text();
            var valB = $(b).find('td').eq(index).text();

            // Try numeric comparison first.
            var numA = parseFloat(valA);
            var numB = parseFloat(valB);

            if (!isNaN(numA) && !isNaN(numB)) {
                return numA - numB;
            }

            // Fallback to string comparison.
            return valA.localeCompare(valB);
        };
    };

    /**
     * Filter warnings by confidence.
     */
    var initConfidenceFilter = function() {
        // Add confidence filter UI.
        var $filterDiv = $('<div>', {
            'class': 'mb-3',
            'id': 'confidence-filter'
        });

        $filterDiv.html(
            '<label for="min-confidence">Minimum confidence: </label>' +
            '<input type="range" id="min-confidence" min="0" max="100" value="50" step="10" class="mr-2">' +
            '<span id="confidence-value">50%</span>'
        );

        $('h3').first().after($filterDiv);

        // Filter on change.
        $('#min-confidence').on('input', function() {
            var minConfidence = parseInt($(this).val());
            $('#confidence-value').text(minConfidence + '%');

            $('tbody tr').each(function() {
                var confidence = parseInt($(this).find('td:nth-child(4)').text());
                if (confidence >= minConfidence) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    };

    return {
        /**
         * Initialize module.
         *
         * @param {Object} riskData Risk distribution data
         * @param {Object} trendData Trend data
         */
        init: function(riskData, trendData) {
            $(document).ready(function() {
                initRiskChart(riskData);
                initTrendChart(trendData);
                highlightHighRisk();
                initTableSorting();
                initConfidenceFilter();
            });
        }
    };
});
