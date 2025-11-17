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
 * Advanced Business Intelligence analytics engine.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * BI Analytics Engine manager class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bi_analytics_engine {

    /**
     * Get institutional overview metrics.
     *
     * @return object Institutional metrics
     */
    public function get_institutional_overview() {
        global $DB;

        $overview = new \stdClass();

        // Total students tracked.
        $overview->total_students = $DB->count_records('local_sm_student_tracking');

        // Students by risk level.
        $overview->risk_distribution = $DB->get_records_sql("
            SELECT risk_level, COUNT(*) as count
            FROM {local_sm_student_tracking}
            GROUP BY risk_level
        ");

        // Average risk score.
        $overview->avg_risk_score = $DB->get_field_sql("
            SELECT AVG(risk_score)
            FROM {local_sm_student_tracking}
        ") ?? 0;

        // Students requiring intervention.
        $overview->needs_intervention = $DB->count_records_select('local_sm_student_tracking',
            "risk_level IN ('ÉLEVÉ', 'CRITIQUE') OR requires_intervention = 1"
        );

        // Total interventions this month.
        $monthago = time() - (30 * 24 * 3600);
        $overview->interventions_this_month = $DB->count_records_select('local_sm_interventions',
            'timecreated > :since', ['since' => $monthago]
        );

        // Total notifications sent this month.
        $overview->notifications_this_month = $DB->count_records_select('local_sm_notifications',
            'timecreated > :since', ['since' => $monthago]
        );

        // Success rate (students improved).
        $overview->success_rate = $this->calculate_success_rate();

        // Average response time (hours).
        $overview->avg_response_time = $this->calculate_avg_response_time();

        // Active supervisors.
        $overview->active_supervisors = $DB->count_records_sql("
            SELECT COUNT(DISTINCT assigned_to)
            FROM {local_sm_student_tracking}
            WHERE assigned_to IS NOT NULL
        ");

        return $overview;
    }

    /**
     * Calculate success rate (students who improved their risk level).
     *
     * @return float Success rate percentage
     */
    protected function calculate_success_rate() {
        global $DB;

        $monthago = time() - (30 * 24 * 3600);

        $sql = "
            SELECT COUNT(DISTINCT userid) as improved
            FROM {local_sm_risk_history}
            WHERE timecreated > :since
            AND previous_risk_level IN ('CRITIQUE', 'ÉLEVÉ')
            AND new_risk_level IN ('MOYEN', 'FAIBLE')
        ";

        $improved = $DB->get_field_sql($sql, ['since' => $monthago]) ?? 0;

        $total = $DB->count_records_select('local_sm_student_tracking',
            "risk_level IN ('CRITIQUE', 'ÉLEVÉ', 'MOYEN')"
        );

        return $total > 0 ? round(($improved / $total) * 100, 2) : 0;
    }

    /**
     * Calculate average response time for interventions.
     *
     * @return float Average hours
     */
    protected function calculate_avg_response_time() {
        global $DB;

        $sql = "
            SELECT AVG(TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(st.timemodified), FROM_UNIXTIME(i.timecreated))) as avg_hours
            FROM {local_sm_interventions} i
            JOIN {local_sm_student_tracking} st ON st.userid = i.userid
            WHERE i.timecreated > :since
        ";

        $monthago = time() - (30 * 24 * 3600);
        $result = $DB->get_field_sql($sql, ['since' => $monthago]);

        return $result ? round($result, 1) : 0;
    }

    /**
     * Get trend data for dashboard charts.
     *
     * @param int $days Number of days to analyze
     * @return object Trend data
     */
    public function get_trend_data($days = 30) {
        global $DB;

        $trends = new \stdClass();

        $since = time() - ($days * 24 * 3600);

        // Daily risk distribution.
        $trends->daily_risk = $this->get_daily_risk_distribution($days);

        // Daily intervention count.
        $sql = "
            SELECT DATE(FROM_UNIXTIME(timecreated)) as date, COUNT(*) as count
            FROM {local_sm_interventions}
            WHERE timecreated > :since
            GROUP BY DATE(FROM_UNIXTIME(timecreated))
            ORDER BY date ASC
        ";

        $trends->daily_interventions = $DB->get_records_sql($sql, ['since' => $since]);

        // Daily notification count.
        $sql = "
            SELECT DATE(FROM_UNIXTIME(timecreated)) as date, COUNT(*) as count
            FROM {local_sm_notifications}
            WHERE timecreated > :since
            GROUP BY DATE(FROM_UNIXTIME(timecreated))
            ORDER BY date ASC
        ";

        $trends->daily_notifications = $DB->get_records_sql($sql, ['since' => $since]);

        // Success rate trend.
        $trends->success_trend = $this->get_success_rate_trend($days);

        return $trends;
    }

    /**
     * Get daily risk distribution.
     *
     * @param int $days Number of days
     * @return array Daily risk data
     */
    protected function get_daily_risk_distribution($days) {
        global $DB;

        $data = [];
        $since = time() - ($days * 24 * 3600);

        $sql = "
            SELECT DATE(FROM_UNIXTIME(timecreated)) as date,
                   risk_level,
                   COUNT(*) as count
            FROM {local_sm_risk_history}
            WHERE timecreated > :since
            GROUP BY DATE(FROM_UNIXTIME(timecreated)), risk_level
            ORDER BY date ASC
        ";

        $records = $DB->get_records_sql($sql, ['since' => $since]);

        foreach ($records as $record) {
            if (!isset($data[$record->date])) {
                $data[$record->date] = [];
            }
            $data[$record->date][$record->risk_level] = $record->count;
        }

        return $data;
    }

    /**
     * Get success rate trend over time.
     *
     * @param int $days Number of days
     * @return array Success rate by week
     */
    protected function get_success_rate_trend($days) {
        global $DB;

        $data = [];
        $weeks = ceil($days / 7);

        for ($i = $weeks; $i >= 0; $i--) {
            $weekstart = time() - (($i + 1) * 7 * 24 * 3600);
            $weekend = time() - ($i * 7 * 24 * 3600);

            $sql = "
                SELECT COUNT(DISTINCT userid) as improved
                FROM {local_sm_risk_history}
                WHERE timecreated BETWEEN :start AND :end
                AND previous_risk_level IN ('CRITIQUE', 'ÉLEVÉ')
                AND new_risk_level IN ('MOYEN', 'FAIBLE')
            ";

            $improved = $DB->get_field_sql($sql, ['start' => $weekstart, 'end' => $weekend]) ?? 0;

            $total = $DB->count_records_select('local_sm_risk_history',
                "timecreated BETWEEN :start AND :end",
                ['start' => $weekstart, 'end' => $weekend]
            );

            $rate = $total > 0 ? round(($improved / $total) * 100, 2) : 0;

            $data[] = (object)[
                'week' => $weeks - $i,
                'rate' => $rate,
                'improved' => $improved,
                'total' => $total
            ];
        }

        return $data;
    }

    /**
     * Get supervisor performance analytics.
     *
     * @return array Supervisor metrics
     */
    public function get_supervisor_performance() {
        global $DB;

        $sql = "
            SELECT
                u.id,
                u.firstname,
                u.lastname,
                COUNT(DISTINCT st.userid) as assigned_students,
                COUNT(DISTINCT i.id) as total_interventions,
                AVG(TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(st.timemodified), FROM_UNIXTIME(i.timecreated))) as avg_response_hours,
                SUM(CASE WHEN rh.new_risk_level IN ('MOYEN', 'FAIBLE')
                    AND rh.previous_risk_level IN ('CRITIQUE', 'ÉLEVÉ') THEN 1 ELSE 0 END) as students_improved
            FROM {user} u
            JOIN {local_sm_student_tracking} st ON st.assigned_to = u.id
            LEFT JOIN {local_sm_interventions} i ON i.supervisor_id = u.id AND i.userid = st.userid
            LEFT JOIN {local_sm_risk_history} rh ON rh.userid = st.userid
            WHERE st.timemodified > :since
            GROUP BY u.id, u.firstname, u.lastname
            ORDER BY total_interventions DESC
        ";

        $monthago = time() - (30 * 24 * 3600);
        $supervisors = $DB->get_records_sql($sql, ['since' => $monthago]);

        foreach ($supervisors as $supervisor) {
            $supervisor->success_rate = $supervisor->assigned_students > 0 ?
                round(($supervisor->students_improved / $supervisor->assigned_students) * 100, 2) : 0;
        }

        return array_values($supervisors);
    }

    /**
     * Get cohort analysis data.
     *
     * @param string $groupby Group by field (course, enrolment_date, risk_level)
     * @return array Cohort data
     */
    public function get_cohort_analysis($groupby = 'course') {
        global $DB;

        $cohorts = [];

        switch ($groupby) {
            case 'course':
                $cohorts = $this->get_cohorts_by_course();
                break;
            case 'enrolment_date':
                $cohorts = $this->get_cohorts_by_enrolment();
                break;
            case 'risk_level':
                $cohorts = $this->get_cohorts_by_risk();
                break;
        }

        return $cohorts;
    }

    /**
     * Get cohorts grouped by course.
     *
     * @return array Course cohorts
     */
    protected function get_cohorts_by_course() {
        global $DB;

        $sql = "
            SELECT
                c.id as cohort_id,
                c.fullname as cohort_name,
                COUNT(DISTINCT st.userid) as total_students,
                AVG(st.risk_score) as avg_risk_score,
                SUM(CASE WHEN st.risk_level = 'CRITIQUE' THEN 1 ELSE 0 END) as critical_count,
                SUM(CASE WHEN st.risk_level = 'ÉLEVÉ' THEN 1 ELSE 0 END) as high_count,
                SUM(CASE WHEN st.risk_level = 'MOYEN' THEN 1 ELSE 0 END) as medium_count,
                SUM(CASE WHEN st.risk_level = 'FAIBLE' THEN 1 ELSE 0 END) as low_count
            FROM {course} c
            JOIN {user_enrolments} ue ON ue.enrolid IN (SELECT id FROM {enrol} WHERE courseid = c.id)
            JOIN {local_sm_student_tracking} st ON st.userid = ue.userid
            WHERE c.id > 1
            GROUP BY c.id, c.fullname
            ORDER BY avg_risk_score DESC
        ";

        return $DB->get_records_sql($sql);
    }

    /**
     * Get cohorts grouped by enrolment period.
     *
     * @return array Enrolment cohorts
     */
    protected function get_cohorts_by_enrolment() {
        global $DB;

        $sql = "
            SELECT
                CONCAT(YEAR(FROM_UNIXTIME(ue.timecreated)), '-', QUARTER(FROM_UNIXTIME(ue.timecreated))) as cohort_id,
                CONCAT('Q', QUARTER(FROM_UNIXTIME(ue.timecreated)), ' ', YEAR(FROM_UNIXTIME(ue.timecreated))) as cohort_name,
                COUNT(DISTINCT st.userid) as total_students,
                AVG(st.risk_score) as avg_risk_score,
                SUM(CASE WHEN st.risk_level = 'CRITIQUE' THEN 1 ELSE 0 END) as critical_count,
                SUM(CASE WHEN st.risk_level = 'ÉLEVÉ' THEN 1 ELSE 0 END) as high_count,
                SUM(CASE WHEN st.risk_level = 'MOYEN' THEN 1 ELSE 0 END) as medium_count,
                SUM(CASE WHEN st.risk_level = 'FAIBLE' THEN 1 ELSE 0 END) as low_count
            FROM {user_enrolments} ue
            JOIN {local_sm_student_tracking} st ON st.userid = ue.userid
            GROUP BY YEAR(FROM_UNIXTIME(ue.timecreated)), QUARTER(FROM_UNIXTIME(ue.timecreated))
            ORDER BY cohort_id DESC
        ";

        return $DB->get_records_sql($sql);
    }

    /**
     * Get cohorts grouped by risk level.
     *
     * @return array Risk cohorts
     */
    protected function get_cohorts_by_risk() {
        global $DB;

        $sql = "
            SELECT
                risk_level as cohort_id,
                risk_level as cohort_name,
                COUNT(*) as total_students,
                AVG(risk_score) as avg_risk_score,
                AVG(inactivity_days) as avg_inactivity,
                AVG(missing_assignments) as avg_missing_assignments,
                COUNT(DISTINCT assigned_to) as supervisors_assigned
            FROM {local_sm_student_tracking}
            GROUP BY risk_level
            ORDER BY FIELD(risk_level, 'CRITIQUE', 'ÉLEVÉ', 'MOYEN', 'FAIBLE')
        ";

        return $DB->get_records_sql($sql);
    }

    /**
     * Get retention analytics.
     *
     * @param int $days Days to analyze
     * @return object Retention data
     */
    public function get_retention_analytics($days = 90) {
        global $DB;

        $retention = new \stdClass();

        $since = time() - ($days * 24 * 3600);

        // Overall retention rate.
        $sql = "
            SELECT
                COUNT(DISTINCT userid) as total,
                SUM(CASE WHEN last_login_time > :recent THEN 1 ELSE 0 END) as active
            FROM {local_sm_student_tracking}
        ";

        $weekago = time() - (7 * 24 * 3600);
        $data = $DB->get_record_sql($sql, ['recent' => $weekago]);

        $retention->retention_rate = $data->total > 0 ?
            round(($data->active / $data->total) * 100, 2) : 0;
        $retention->total_students = $data->total;
        $retention->active_students = $data->active;
        $retention->at_risk_dropout = $data->total - $data->active;

        // Retention by risk level.
        $sql = "
            SELECT
                risk_level,
                COUNT(*) as total,
                SUM(CASE WHEN last_login_time > :recent THEN 1 ELSE 0 END) as active
            FROM {local_sm_student_tracking}
            GROUP BY risk_level
        ";

        $retention->by_risk_level = $DB->get_records_sql($sql, ['recent' => $weekago]);

        // Dropout prediction (students with high inactivity).
        $retention->dropout_prediction = $DB->count_records_select('local_sm_student_tracking',
            "inactivity_days > 14 AND risk_level IN ('CRITIQUE', 'ÉLEVÉ')"
        );

        // Retention trend over weeks.
        $retention->weekly_trend = $this->get_retention_trend(12);

        return $retention;
    }

    /**
     * Get retention trend over weeks.
     *
     * @param int $weeks Number of weeks
     * @return array Weekly retention data
     */
    protected function get_retention_trend($weeks) {
        global $DB;

        $trend = [];

        for ($i = $weeks; $i >= 0; $i--) {
            $weekstart = time() - (($i + 1) * 7 * 24 * 3600);
            $weekend = time() - ($i * 7 * 24 * 3600);

            $sql = "
                SELECT
                    COUNT(DISTINCT userid) as total,
                    SUM(CASE WHEN last_login_time > :weekend THEN 1 ELSE 0 END) as active
                FROM {local_sm_student_tracking}
                WHERE timecreated < :weekend
            ";

            $data = $DB->get_record_sql($sql, ['weekend' => $weekend]);

            $rate = $data->total > 0 ? round(($data->active / $data->total) * 100, 2) : 0;

            $trend[] = (object)[
                'week' => $weeks - $i,
                'rate' => $rate,
                'active' => $data->active,
                'total' => $data->total
            ];
        }

        return $trend;
    }

    /**
     * Generate executive summary report.
     *
     * @return object Executive summary
     */
    public function generate_executive_summary() {
        $summary = new \stdClass();

        $summary->overview = $this->get_institutional_overview();
        $summary->trends = $this->get_trend_data(30);
        $summary->supervisors = $this->get_supervisor_performance();
        $summary->retention = $this->get_retention_analytics(90);
        $summary->top_risks = $this->get_top_risk_students(10);
        $summary->recommendations = $this->generate_institutional_recommendations();
        $summary->generated_at = time();

        return $summary;
    }

    /**
     * Get top risk students.
     *
     * @param int $limit Number of students
     * @return array Students
     */
    protected function get_top_risk_students($limit = 10) {
        global $DB;

        $sql = "
            SELECT
                st.userid,
                u.firstname,
                u.lastname,
                st.risk_level,
                st.risk_score,
                st.inactivity_days,
                st.missing_assignments,
                st.assigned_to
            FROM {local_sm_student_tracking} st
            JOIN {user} u ON u.id = st.userid
            WHERE st.risk_level IN ('CRITIQUE', 'ÉLEVÉ')
            ORDER BY st.risk_score DESC, st.inactivity_days DESC
            LIMIT :limit
        ";

        return $DB->get_records_sql($sql, ['limit' => $limit]);
    }

    /**
     * Generate institutional recommendations.
     *
     * @return array Recommendations
     */
    protected function generate_institutional_recommendations() {
        $recommendations = [];

        $overview = $this->get_institutional_overview();

        // High risk student count.
        if ($overview->needs_intervention > 20) {
            $recommendations[] = (object)[
                'priority' => 'high',
                'title' => 'High number of at-risk students',
                'description' => "You have {$overview->needs_intervention} students requiring intervention. Consider increasing supervisor capacity.",
                'action' => 'Review supervisor workload and consider hiring'
            ];
        }

        // Low success rate.
        if ($overview->success_rate < 50) {
            $recommendations[] = (object)[
                'priority' => 'high',
                'title' => 'Low intervention success rate',
                'description' => "Success rate is {$overview->success_rate}%. Review intervention strategies.",
                'action' => 'Analyze successful interventions and replicate'
            ];
        }

        // Slow response time.
        if ($overview->avg_response_time > 48) {
            $recommendations[] = (object)[
                'priority' => 'medium',
                'title' => 'Slow response time',
                'description' => "Average response time is {$overview->avg_response_time} hours. Aim for under 24 hours.",
                'action' => 'Implement automated task assignment'
            ];
        }

        return $recommendations;
    }
}
