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
 * AMD module for searching users in autocomplete field.
 *
 * @module     local_student_monitor/search_users
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax'], function(Ajax) {

    return {
        /**
         * Transport method for autocomplete.
         *
         * @param {string} selector The selector for the autocomplete element
         * @param {string} query The search query
         * @param {function} success Success callback
         * @param {function} failure Failure callback
         */
        transport: function(selector, query, success, failure) {
            var promises = Ajax.call([{
                methodname: 'local_student_monitor_search_users',
                args: {
                    query: query,
                    limitnum: 100
                }
            }]);

            promises[0].then(function(results) {
                var options = results.map(function(user) {
                    return {
                        value: user.id,
                        label: user.fullname + ' (' + user.email + ')'
                    };
                });
                success(options);
            }).catch(failure);
        },

        /**
         * Process the results for display in the autocomplete list.
         *
         * @param {string} selector The selector for the autocomplete element
         * @param {array} results The results from the transport
         * @return {array} Processed results
         */
        processResults: function(selector, results) {
            return results;
        }
    };
});
