<?php
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
 * Custom report builder for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Custom report builder class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_report_builder {

    /**
     * Available columns for reports.
     */
    const AVAILABLE_COLUMNS = [
        'student_name' => 'Student name',
        'student_email' => 'Student email',
        'risk_level' => 'Risk level',
        'inactivity_days' => 'Inactivity days',
        'missing_activities' => 'Missing activities',
        'notification_count' => 'Notifications sent',
        'last_login' => 'Last login',
        'assigned_to' => 'Assigned supervisor',
        'intervention_count' => 'Interventions',
        'last_intervention' => 'Last intervention',
        'grade_average' => 'Grade average',
        'course_count' => 'Enrolled courses',
        'predicted_risk' => 'Predicted risk'
    ];

    /**
     * Available filters for reports.
     */
    const AVAILABLE_FILTERS = [
        'risk_level' => ['type' => 'select', 'options' => ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']],
        'inactivity_days' => ['type' => 'range', 'min' => 0, 'max' => 30],
        'missing_activities' => ['type' => 'range', 'min' => 0, 'max' => 20],
        'assigned_status' => ['type' => 'select', 'options' => ['assigned', 'unassigned', 'all']],
        'date_range' => ['type' => 'daterange'],
        'supervisor' => ['type' => 'select_user']
    ];

    /**
     * Save a custom report template.
     *
     * @param string $name Report name
     * @param array $columns Columns to include
     * @param array $filters Filters to apply
     * @param array $sorting Sorting configuration
     * @param int $userid Owner user ID
     * @return int Report ID
     */
    public function save_report_template($name, $columns, $filters, $sorting = [], $userid = 0) {
        global $DB, $USER;

        if ($userid == 0) {
            $userid = $USER->id;
        }

        $template = new \stdClass();
        $template->user_id = $userid;
        $template->report_name = $name;
        $template->columns = json_encode($columns);
        $template->filters = json_encode($filters);
        $template->sorting = json_encode($sorting);
        $template->is_public = 0;
        $template->timecreated = time();
        $template->timemodified = time();

        // Check if table exists.
        if ($DB->get_manager()->table_exists('local_sm_custom_reports')) {
            return $DB->insert_record('local_sm_custom_reports', $template);
        } else {
            // Fallback to config table.
            $config = new \stdClass();
            $config->courseid = 0;
            $config->config_type = 'custom_report';
            $config->config_key = 'report_' . time() . '_' . $userid;
            $config->config_value = json_encode($template);
            $config->timecreated = time();

            return $DB->insert_record('local_sm_config', $config);
        }
    }

    /**
     * Get saved report templates.
     *
     * @param int $userid User ID (0 for all public)
     * @return array Report templates
     */
    public function get_report_templates($userid = 0) {
        global $DB, $USER;

        if ($userid == 0) {
            $userid = $USER->id;
        }

        if ($DB->get_manager()->table_exists('local_sm_custom_reports')) {
            return $DB->get_records_sql("
                SELECT *
                FROM {local_sm_custom_reports}
                WHERE user_id = :userid OR is_public = 1
                ORDER BY report_name
            ", ['userid' => $userid]);
        } else {
            // Fallback to config table.
            $configs = $DB->get_records('local_sm_config', [
                'config_type' => 'custom_report'
            ]);

            $templates = [];
            foreach ($configs as $config) {
                $template = json_decode($config->config_value);
                if ($template && ($template->user_id == $userid || $template->is_public)) {
                    $template->id = $config->id;
                    $templates[] = $template;
                }
            }
            return $templates;
        }
    }

    /**
     * Generate custom report.
     *
     * @param array $columns Columns to include
     * @param array $filters Filters to apply
     * @param array $sorting Sorting configuration
     * @return array Report data
     */
    public function generate_report($columns, $filters = [], $sorting = []) {
        global $DB;

        // Build SQL query.
        $sql = $this->build_sql_query($columns, $filters, $sorting);

        // Execute query.
        $data = $DB->get_records_sql($sql['query'], $sql['params']);

        // Process data.
        $processeddata = [];
        foreach ($data as $row) {
            $processedrow = [];
            foreach ($columns as $column) {
                $processedrow[$column] = $this->format_column_value($column, $row);
            }
            $processeddata[] = $processedrow;
        }

        return $processeddata;
    }

    /**
     * Build SQL query based on columns and filters.
     *
     * @param array $columns Columns to include
     * @param array $filters Filters to apply
     * @param array $sorting Sorting configuration
     * @return array SQL query and parameters
     */
    protected function build_sql_query($columns, $filters, $sorting) {
        $select = ['st.userid'];
        $joins = [];
        $where = ['1 = 1'];
        $params = [];

        // Add user table by default.
        $joins[] = "JOIN {user} u ON u.id = st.userid";
        $select[] = 'u.firstname';
        $select[] = 'u.lastname';
        $select[] = 'u.email';
        $select[] = 'u.firstnamephonetic';
        $select[] = 'u.lastnamephonetic';
        $select[] = 'u.middlename';
        $select[] = 'u.alternatename';

        // Add student tracking fields.
        if (in_array('risk_level', $columns)) {
            $select[] = 'st.risk_level';
        }
        if (in_array('inactivity_days', $columns)) {
            $select[] = 'st.inactivity_days';
        }
        if (in_array('missing_activities', $columns)) {
            $select[] = 'st.missing_activities';
        }
        if (in_array('notification_count', $columns)) {
            $select[] = 'st.notification_count';
        }
        if (in_array('intervention_count', $columns)) {
            $select[] = 'st.intervention_count';
        }
        if (in_array('last_intervention', $columns)) {
            $select[] = 'st.last_intervention';
        }
        if (in_array('assigned_to', $columns)) {
            $select[] = 'st.assigned_to';
            $joins[] = "LEFT JOIN {user} supervisor ON supervisor.id = st.assigned_to";
            $select[] = 'supervisor.firstname as supervisor_firstname';
            $select[] = 'supervisor.lastname as supervisor_lastname';
        }

        // Add last login.
        if (in_array('last_login', $columns)) {
            $select[] = 'u.lastaccess';
        }

        // Apply filters.
        if (!empty($filters)) {
            if (isset($filters['risk_level']) && $filters['risk_level'] !== 'all') {
                $where[] = 'st.risk_level = :risklevel';
                $params['risklevel'] = $filters['risk_level'];
            }

            if (isset($filters['inactivity_min'])) {
                $where[] = 'st.inactivity_days >= :inactivitymin';
                $params['inactivitymin'] = $filters['inactivity_min'];
            }

            if (isset($filters['inactivity_max'])) {
                $where[] = 'st.inactivity_days <= :inactivitymax';
                $params['inactivitymax'] = $filters['inactivity_max'];
            }

            if (isset($filters['missing_min'])) {
                $where[] = 'st.missing_activities >= :missingmin';
                $params['missingmin'] = $filters['missing_min'];
            }

            if (isset($filters['missing_max'])) {
                $where[] = 'st.missing_activities <= :missingmax';
                $params['missingmax'] = $filters['missing_max'];
            }

            if (isset($filters['assigned_status'])) {
                if ($filters['assigned_status'] === 'assigned') {
                    $where[] = 'st.assigned_to IS NOT NULL';
                } else if ($filters['assigned_status'] === 'unassigned') {
                    $where[] = 'st.assigned_to IS NULL';
                }
            }

            if (isset($filters['supervisor']) && $filters['supervisor'] > 0) {
                $where[] = 'st.assigned_to = :supervisor';
                $params['supervisor'] = $filters['supervisor'];
            }
        }

        // Build ORDER BY.
        $orderby = [];
        if (!empty($sorting)) {
            foreach ($sorting as $column => $direction) {
                $orderby[] = $this->get_column_sql($column) . ' ' . strtoupper($direction);
            }
        }

        if (empty($orderby)) {
            $orderby[] = 'st.risk_level DESC';
            $orderby[] = 'u.lastname ASC';
        }

        $query = "SELECT " . implode(', ', array_unique($select)) . "
                  FROM {local_sm_student_tracking} st
                  " . implode("\n", $joins) . "
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY " . implode(', ', $orderby);

        return [
            'query' => $query,
            'params' => $params
        ];
    }

    /**
     * Get SQL column name for a given column.
     *
     * @param string $column Column identifier
     * @return string SQL column
     */
    protected function get_column_sql($column) {
        $mapping = [
            'student_name' => 'u.lastname',
            'student_email' => 'u.email',
            'risk_level' => 'st.risk_level',
            'inactivity_days' => 'st.inactivity_days',
            'missing_activities' => 'st.missing_activities',
            'notification_count' => 'st.notification_count',
            'last_login' => 'u.lastaccess',
            'intervention_count' => 'st.intervention_count',
            'last_intervention' => 'st.last_intervention'
        ];

        return $mapping[$column] ?? $column;
    }

    /**
     * Format column value for display.
     *
     * @param string $column Column name
     * @param object $row Data row
     * @return mixed Formatted value
     */
    protected function format_column_value($column, $row) {
        switch ($column) {
            case 'student_name':
                return fullname($row);

            case 'student_email':
                return $row->email;

            case 'last_login':
                return $row->lastaccess ? userdate($row->lastaccess) : get_string('never');

            case 'assigned_to':
                if (isset($row->supervisor_firstname)) {
                    return $row->supervisor_firstname . ' ' . $row->supervisor_lastname;
                }
                return get_string('unassigned', 'local_student_monitor');

            case 'last_intervention':
                return $row->last_intervention ? userdate($row->last_intervention) : get_string('none');

            default:
                return $row->$column ?? '';
        }
    }

    /**
     * Export report to CSV.
     *
     * @param array $data Report data
     * @param array $columns Column names
     * @param string $filename Filename
     */
    public function export_to_csv($data, $columns, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel.
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers.
        $headers = [];
        foreach ($columns as $column) {
            $headers[] = get_string('column_' . $column, 'local_student_monitor');
        }
        fputcsv($output, $headers);

        // Data rows.
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }

        fclose($output);
        exit;
    }

    /**
     * Get report summary statistics.
     *
     * @param array $data Report data
     * @return object Statistics
     */
    public function get_report_statistics($data) {
        $stats = new \stdClass();
        $stats->total_rows = count($data);
        $stats->risk_distribution = [
            'CRITICAL' => 0,
            'HIGH' => 0,
            'MEDIUM' => 0,
            'LOW' => 0
        ];

        $totalinactivity = 0;
        $totalmissing = 0;

        foreach ($data as $row) {
            if (isset($row['risk_level'])) {
                $stats->risk_distribution[$row['risk_level']]++;
            }

            if (isset($row['inactivity_days'])) {
                $totalinactivity += $row['inactivity_days'];
            }

            if (isset($row['missing_activities'])) {
                $totalmissing += $row['missing_activities'];
            }
        }

        $stats->avg_inactivity = $stats->total_rows > 0
            ? round($totalinactivity / $stats->total_rows, 1)
            : 0;

        $stats->avg_missing = $stats->total_rows > 0
            ? round($totalmissing / $stats->total_rows, 1)
            : 0;

        return $stats;
    }

    /**
     * Delete a report template.
     *
     * @param int $reportid Report ID
     * @return bool Success
     */
    public function delete_report_template($reportid) {
        global $DB;

        if ($DB->get_manager()->table_exists('local_sm_custom_reports')) {
            return $DB->delete_records('local_sm_custom_reports', ['id' => $reportid]);
        } else {
            return $DB->delete_records('local_sm_config', ['id' => $reportid, 'config_type' => 'custom_report']);
        }
    }
}
