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
 * Advanced filters module for student list.
 *
 * @module     local_student_monitor/advanced_filters
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * Mapping of legacy French risk levels to normalized English values.
     * Used for consistent comparison regardless of which format is stored.
     */
    var RISK_NORMALIZATION = {
        'CRITIQUE': 'CRITICAL',
        'ÉLEVÉ': 'HIGH',
        'ELEVE': 'HIGH',
        'MOYEN': 'MEDIUM',
        'FAIBLE': 'LOW',
        'CRITICAL': 'CRITICAL',
        'HIGH': 'HIGH',
        'MEDIUM': 'MEDIUM',
        'LOW': 'LOW'
    };

    /**
     * Normalize a risk level to standard English format.
     *
     * @param {string} risk - Risk level (legacy or new format)
     * @returns {string} Normalized risk level
     */
    var normalizeRiskLevel = function(risk) {
        if (!risk) {
            return '';
        }
        var upperRisk = risk.toUpperCase().trim();
        return RISK_NORMALIZATION[upperRisk] || upperRisk;
    };

    /**
     * Initialize advanced filters.
     */
    var init = function() {
        // Search filter.
        $('#student-search').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            filterStudents();
        });

        // Risk level filter.
        $('#risk-filter').on('change', function() {
            filterStudents();
        });

        // Inactivity filter.
        $('#inactivity-filter').on('change', function() {
            filterStudents();
        });

        // Missing assignments filter.
        $('#assignments-filter').on('change', function() {
            filterStudents();
        });

        // Assigned filter.
        $('#assigned-filter').on('change', function() {
            filterStudents();
        });

        // Clear filters button.
        $('#clear-filters').on('click', function() {
            $('#student-search').val('');
            $('#risk-filter').val('');
            $('#inactivity-filter').val('');
            $('#assignments-filter').val('');
            $('#assigned-filter').val('');
            filterStudents();
        });

        // Bulk selection.
        $('#select-all-students').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('.student-row:visible .student-select').prop('checked', isChecked);
        });

        // Bulk action form.
        $('#bulk-action-form').on('submit', function(e) {
            var checkedCount = $('.student-select:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Please select at least one student');
                return false;
            }

            var action = $('#bulk-action-select').val();
            if (!action) {
                e.preventDefault();
                alert('Please select an action');
                return false;
            }
        });
    };

    /**
     * Filter students based on current filter values.
     */
    var filterStudents = function() {
        var searchText = $('#student-search').val().toLowerCase();
        var riskFilter = $('#risk-filter').val();
        var inactivityFilter = $('#inactivity-filter').val();
        var assignmentsFilter = $('#assignments-filter').val();
        var assignedFilter = $('#assigned-filter').val();

        var visibleCount = 0;

        $('.student-row').each(function() {
            var $row = $(this);
            var show = true;

            // Search filter (name or email).
            if (searchText) {
                var studentName = $row.find('.student-name').text().toLowerCase();
                var studentEmail = $row.find('.student-email').text().toLowerCase();
                if (studentName.indexOf(searchText) === -1 && studentEmail.indexOf(searchText) === -1) {
                    show = false;
                }
            }

            // Risk level filter - normalize both values for comparison.
            if (riskFilter && show) {
                var riskLevel = $row.data('risk');
                var normalizedRowRisk = normalizeRiskLevel(riskLevel);
                var normalizedFilterRisk = normalizeRiskLevel(riskFilter);
                if (normalizedRowRisk !== normalizedFilterRisk) {
                    show = false;
                }
            }

            // Inactivity filter.
            if (inactivityFilter && show) {
                var inactivityDays = parseInt($row.data('inactivity'));
                var threshold = parseInt(inactivityFilter);

                if (threshold === 0 && inactivityDays > 0) {
                    show = false;
                } else if (threshold > 0 && inactivityDays < threshold) {
                    show = false;
                }
            }

            // Missing assignments filter.
            if (assignmentsFilter && show) {
                var missingAssignments = parseInt($row.data('missing-assignments'));
                var threshold = parseInt(assignmentsFilter);

                if (threshold === 0 && missingAssignments > 0) {
                    show = false;
                } else if (threshold > 0 && missingAssignments < threshold) {
                    show = false;
                }
            }

            // Assigned filter.
            if (assignedFilter && show) {
                var hasAssignment = $row.data('assigned') === 1;

                if (assignedFilter === 'assigned' && !hasAssignment) {
                    show = false;
                } else if (assignedFilter === 'unassigned' && hasAssignment) {
                    show = false;
                }
            }

            // Show or hide row.
            if (show) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });

        // Update visible count.
        $('#visible-count').text(visibleCount);
    };

    return {
        init: init
    };
});
