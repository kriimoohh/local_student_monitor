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
 * Task manager AMD module.
 *
 * @module     local_student_monitor/task_manager
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * Initialize task filtering.
     */
    var initFiltering = function() {
        // Table row filtering based on status.
        $('#status-filter').on('change', function() {
            var status = $(this).val();
            if (status === 'all') {
                $('tbody tr').show();
            } else {
                $('tbody tr').each(function() {
                    var rowStatus = $(this).find('.badge').last().text().toLowerCase();
                    if (rowStatus.includes(status.replace('_', ' '))) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    };

    /**
     * Initialize action confirmations.
     */
    var initConfirmations = function() {
        // Confirm task completion.
        $('a[href*="action=complete"]').on('click', function(e) {
            if (!confirm('Are you sure you want to mark this task as completed?')) {
                e.preventDefault();
                return false;
            }
        });

        // Confirm task reassignment.
        $('a[href*="action=reassign"]').on('click', function(e) {
            if (!confirm('Are you sure you want to reassign this task?')) {
                e.preventDefault();
                return false;
            }
        });
    };

    /**
     * Highlight overdue tasks.
     */
    var highlightOverdue = function() {
        $('tbody tr.table-danger').each(function() {
            var $row = $(this);
            setInterval(function() {
                $row.toggleClass('highlight-pulse');
            }, 2000);
        });
    };

    /**
     * Add task statistics summary.
     */
    var addTaskStatistics = function() {
        var total = $('tbody tr').length;
        var overdue = $('tbody tr.table-danger').length;
        var percentage = total > 0 ? Math.round((overdue / total) * 100) : 0;

        if (overdue > 0) {
            var $alert = $('<div>', {
                'class': 'alert alert-warning',
                'role': 'alert'
            }).html('<strong>Warning:</strong> You have ' + overdue + ' overdue task(s) (' + percentage + '% of total).');

            $('.kpi-card').first().parent().parent().before($alert);
        }
    };

    /**
     * Initialize quick actions.
     */
    var initQuickActions = function() {
        // Add quick complete button functionality.
        $('.btn-success').on('click', function(e) {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Processing...');
        });

        // Add quick start button functionality.
        $('.btn-primary').on('click', function(e) {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Starting...');
        });
    };

    return {
        /**
         * Initialize module.
         */
        init: function() {
            $(document).ready(function() {
                initFiltering();
                initConfirmations();
                highlightOverdue();
                addTaskStatistics();
                initQuickActions();
            });
        }
    };
});
