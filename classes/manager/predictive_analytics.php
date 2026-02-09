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
 * Predictive analytics engine for Student Monitor.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Predictive analytics class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class predictive_analytics {

    /**
     * Predict future risk level for a student.
     *
     * @param int $userid User ID
     * @param int $daysahead Days to predict ahead (default 7)
     * @return object Prediction with confidence score
     */
    public function predict_risk($userid, $daysahead = 7) {
        global $DB;

        // Get historical data.
        $history = $this->get_student_history($userid, 30);

        if (empty($history)) {
            return (object)[
                'predicted_risk' => 'UNKNOWN',
                'confidence' => 0,
                'probability' => [
                    'CRITICAL' => 0,
                    'HIGH' => 0,
                    'MEDIUM' => 0,
                    'LOW' => 0
                ],
                'factors' => []
            ];
        }

        // Calculate trend scores.
        $trends = $this->calculate_trends($history);

        // Apply predictive model.
        $prediction = $this->apply_prediction_model($trends, $daysahead);

        // Calculate confidence based on data quality.
        $confidence = $this->calculate_confidence($history, $trends);

        return (object)[
            'predicted_risk' => $prediction['risk_level'],
            'confidence' => $confidence,
            'probability' => $prediction['probabilities'],
            'factors' => $prediction['factors'],
            'trend_direction' => $trends['direction'],
            'prediction_date' => time() + ($daysahead * 24 * 60 * 60)
        ];
    }

    /**
     * Get student historical data.
     *
     * @param int $userid User ID
     * @param int $days Number of days to look back
     * @return array Historical data points
     */
    protected function get_student_history($userid, $days = 30) {
        global $DB;

        $since = time() - ($days * 24 * 60 * 60);
        $history = [];

        // Get tracking snapshots from logs.
        $logs = $DB->get_records_sql("
            SELECT *
            FROM {local_sm_logs}
            WHERE userid = :userid
              AND action IN ('risk_level_changed', 'tracking_updated')
              AND timecreated >= :since
            ORDER BY timecreated ASC
        ", ['userid' => $userid, 'since' => $since]);

        foreach ($logs as $log) {
            $details = json_decode($log->details);
            if ($details) {
                $history[] = (object)[
                    'date' => $log->timecreated,
                    'risk_level' => $details->risk_level ?? null,
                    'inactivity_days' => $details->inactivity_days ?? 0,
                    'missing_activities' => $details->missing_activities ?? 0,
                    'login_count' => $details->login_count ?? 0,
                    'grade_average' => $details->grade_average ?? 0
                ];
            }
        }

        // If no history, get current state.
        if (empty($history)) {
            $current = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);
            if ($current) {
                $history[] = (object)[
                    'date' => time(),
                    'risk_level' => $current->risk_level,
                    'inactivity_days' => $current->inactivity_days,
                    'missing_activities' => $current->missing_activities,
                    'login_count' => 0,
                    'grade_average' => 0
                ];
            }
        }

        return $history;
    }

    /**
     * Calculate trends from historical data.
     *
     * @param array $history Historical data
     * @return array Trend analysis
     */
    protected function calculate_trends($history) {
        $trends = [
            'inactivity_trend' => 0,
            'assignment_trend' => 0,
            'engagement_trend' => 0,
            'risk_trend' => 0,
            'direction' => 'stable',
            'velocity' => 0
        ];

        if (count($history) < 2) {
            return $trends;
        }

        // Calculate linear regression for inactivity.
        $inactivity_data = array_map(function($h) {
            return $h->inactivity_days;
        }, $history);
        $trends['inactivity_trend'] = $this->calculate_slope($inactivity_data);

        // Calculate linear regression for missing assignments.
        $assignment_data = array_map(function($h) {
            return $h->missing_activities;
        }, $history);
        $trends['assignment_trend'] = $this->calculate_slope($assignment_data);

        // Calculate engagement trend (login frequency).
        $engagement_data = array_map(function($h) {
            return $h->login_count ?? 0;
        }, $history);
        $trends['engagement_trend'] = $this->calculate_slope($engagement_data);

        // Calculate risk level trend.
        $risk_hierarchy = ['LOW' => 1, 'MEDIUM' => 2, 'HIGH' => 3, 'CRITICAL' => 4];
        $risk_data = array_map(function($h) use ($risk_hierarchy) {
            return $risk_hierarchy[$h->risk_level] ?? 0;
        }, $history);
        $trends['risk_trend'] = $this->calculate_slope($risk_data);

        // Determine overall direction.
        $overall_trend = ($trends['inactivity_trend'] + $trends['assignment_trend'] - $trends['engagement_trend']) / 3;

        if ($overall_trend > 0.5) {
            $trends['direction'] = 'deteriorating';
        } else if ($overall_trend < -0.5) {
            $trends['direction'] = 'improving';
        } else {
            $trends['direction'] = 'stable';
        }

        $trends['velocity'] = abs($overall_trend);

        return $trends;
    }

    /**
     * Calculate slope using simple linear regression.
     *
     * @param array $data Data points
     * @return float Slope
     */
    protected function calculate_slope($data) {
        $n = count($data);
        if ($n < 2) {
            return 0;
        }

        $x = range(0, $n - 1);
        $y = array_values($data);

        $xmean = array_sum($x) / $n;
        $ymean = array_sum($y) / $n;

        $numerator = 0;
        $denominator = 0;

        for ($i = 0; $i < $n; $i++) {
            $numerator += ($x[$i] - $xmean) * ($y[$i] - $ymean);
            $denominator += pow($x[$i] - $xmean, 2);
        }

        return $denominator != 0 ? $numerator / $denominator : 0;
    }

    /**
     * Apply prediction model.
     *
     * @param array $trends Trend analysis
     * @param int $daysahead Days to predict
     * @return array Prediction results
     */
    protected function apply_prediction_model($trends, $daysahead) {
        // Initialize probabilities.
        $probabilities = [
            'CRITICAL' => 0,
            'HIGH' => 0,
            'MEDIUM' => 0,
            'LOW' => 0
        ];

        $factors = [];

        // Base probability on current trend direction.
        if ($trends['direction'] === 'deteriorating') {
            // Increasing risk.
            $probabilities['CRITICAL'] = min(40 + ($trends['velocity'] * 20), 80);
            $probabilities['HIGH'] = min(30 + ($trends['velocity'] * 15), 60);
            $probabilities['MEDIUM'] = 20;
            $probabilities['LOW'] = 10;

            $factors[] = [
                'factor' => 'Trend deteriorating',
                'impact' => 'high',
                'value' => round($trends['velocity'], 2)
            ];
        } else if ($trends['direction'] === 'improving') {
            // Decreasing risk.
            $probabilities['LOW'] = min(50 + ($trends['velocity'] * 20), 80);
            $probabilities['MEDIUM'] = min(30 + ($trends['velocity'] * 10), 50);
            $probabilities['HIGH'] = 15;
            $probabilities['CRITICAL'] = 5;

            $factors[] = [
                'factor' => 'Trend improving',
                'impact' => 'positive',
                'value' => round($trends['velocity'], 2)
            ];
        } else {
            // Stable.
            $probabilities['MEDIUM'] = 40;
            $probabilities['LOW'] = 30;
            $probabilities['HIGH'] = 20;
            $probabilities['CRITICAL'] = 10;

            $factors[] = [
                'factor' => 'Trend stable',
                'impact' => 'neutral',
                'value' => 0
            ];
        }

        // Adjust based on inactivity trend.
        if ($trends['inactivity_trend'] > 0.5) {
            $probabilities['CRITICAL'] += 15;
            $probabilities['HIGH'] += 10;
            $factors[] = [
                'factor' => 'Inactivity increasing',
                'impact' => 'high',
                'value' => round($trends['inactivity_trend'], 2)
            ];
        }

        // Adjust based on assignment trend.
        if ($trends['assignment_trend'] > 0.3) {
            $probabilities['CRITICAL'] += 10;
            $probabilities['HIGH'] += 15;
            $factors[] = [
                'factor' => 'Missing assignments increasing',
                'impact' => 'high',
                'value' => round($trends['assignment_trend'], 2)
            ];
        }

        // Adjust based on engagement trend.
        if ($trends['engagement_trend'] < -0.2) {
            $probabilities['CRITICAL'] += 5;
            $probabilities['HIGH'] += 10;
            $factors[] = [
                'factor' => 'Engagement decreasing',
                'impact' => 'medium',
                'value' => round($trends['engagement_trend'], 2)
            ];
        }

        // Normalize probabilities to sum to 100.
        $total = array_sum($probabilities);
        if ($total > 0) {
            foreach ($probabilities as $level => $prob) {
                $probabilities[$level] = round(($prob / $total) * 100, 1);
            }
        }

        // Determine most likely risk level.
        arsort($probabilities);
        $predicted_level = array_key_first($probabilities);

        return [
            'risk_level' => $predicted_level,
            'probabilities' => $probabilities,
            'factors' => $factors
        ];
    }

    /**
     * Calculate prediction confidence.
     *
     * @param array $history Historical data
     * @param array $trends Trend analysis
     * @return float Confidence score (0-100)
     */
    protected function calculate_confidence($history, $trends) {
        $confidence = 50; // Base confidence.

        // More data points = higher confidence.
        $datapoints = count($history);
        if ($datapoints >= 20) {
            $confidence += 30;
        } else if ($datapoints >= 10) {
            $confidence += 20;
        } else if ($datapoints >= 5) {
            $confidence += 10;
        }

        // Consistent trend = higher confidence.
        if ($trends['velocity'] > 0.5) {
            $confidence += 15;
        } else if ($trends['velocity'] > 0.3) {
            $confidence += 10;
        }

        // Recent data = higher confidence.
        if (!empty($history)) {
            $mostrecent = end($history);
            $daysold = (time() - $mostrecent->date) / (24 * 60 * 60);
            if ($daysold < 1) {
                $confidence += 5;
            }
        }

        return min($confidence, 100);
    }

    /**
     * Get predictions for all at-risk students.
     *
     * @param int $daysahead Days to predict
     * @return array Predictions for all students
     */
    public function get_all_predictions($daysahead = 7) {
        global $DB;

        $students = $DB->get_records('local_sm_student_tracking', null, '', 'userid');
        $predictions = [];

        foreach ($students as $student) {
            $prediction = $this->predict_risk($student->userid, $daysahead);
            if ($prediction->confidence > 30) { // Only include confident predictions.
                $predictions[$student->userid] = $prediction;
            }
        }

        return $predictions;
    }

    /**
     * Get early warning students (predicted to become at-risk).
     *
     * @param int $daysahead Days to predict
     * @param int $minconfidence Minimum confidence threshold
     * @return array Students predicted to become at-risk
     */
    public function get_early_warnings($daysahead = 7, $minconfidence = 50) {
        global $DB;

        $warnings = [];
        $predictions = $this->get_all_predictions($daysahead);

        foreach ($predictions as $userid => $prediction) {
            // Check if predicted risk is high/critical and confidence is sufficient.
            if ($prediction->confidence >= $minconfidence &&
                in_array($prediction->predicted_risk, ['HIGH', 'CRITICAL'])) {

                $user = $DB->get_record('user', ['id' => $userid]);
                $current = $DB->get_record('local_sm_student_tracking', ['userid' => $userid]);

                $warnings[] = (object)[
                    'userid' => $userid,
                    'fullname' => fullname($user),
                    'email' => $user->email,
                    'current_risk' => $current->risk_level,
                    'predicted_risk' => $prediction->predicted_risk,
                    'confidence' => $prediction->confidence,
                    'probability' => $prediction->probability[$prediction->predicted_risk],
                    'trend_direction' => $prediction->trend_direction,
                    'factors' => $prediction->factors
                ];
            }
        }

        return $warnings;
    }

    /**
     * Generate prediction report.
     *
     * @param int $daysahead Days to predict
     * @return object Prediction report
     */
    public function generate_prediction_report($daysahead = 7) {
        $predictions = $this->get_all_predictions($daysahead);
        $warnings = $this->get_early_warnings($daysahead);

        $report = (object)[
            'total_students' => count($predictions),
            'early_warnings' => count($warnings),
            'risk_distribution' => [
                'CRITICAL' => 0,
                'HIGH' => 0,
                'MEDIUM' => 0,
                'LOW' => 0
            ],
            'avg_confidence' => 0,
            'trend_summary' => [
                'deteriorating' => 0,
                'improving' => 0,
                'stable' => 0
            ],
            'warnings' => $warnings,
            'generated_at' => time(),
            'prediction_date' => time() + ($daysahead * 24 * 60 * 60)
        ];

        $totalconfidence = 0;

        foreach ($predictions as $prediction) {
            $report->risk_distribution[$prediction->predicted_risk]++;
            $totalconfidence += $prediction->confidence;
            $report->trend_summary[$prediction->trend_direction]++;
        }

        $report->avg_confidence = count($predictions) > 0
            ? round($totalconfidence / count($predictions), 1)
            : 0;

        return $report;
    }
}
