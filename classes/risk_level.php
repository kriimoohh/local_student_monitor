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
 * Risk level constants and helper methods.
 *
 * This class centralizes all risk level related constants and provides
 * helper methods for risk level operations including hierarchy management.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Universite Numerique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Class risk_level
 *
 * Provides constants and helper methods for risk level management.
 */
class risk_level {

    /** @var string Low risk level constant */
    const LOW = 'LOW';

    /** @var string Medium risk level constant */
    const MEDIUM = 'MEDIUM';

    /** @var string High risk level constant */
    const HIGH = 'HIGH';

    /** @var string Critical risk level constant */
    const CRITICAL = 'CRITICAL';

    /**
     * Legacy French constants for backward compatibility during migration.
     * These will be removed in a future version.
     */
    const LEGACY_FAIBLE = 'FAIBLE';
    const LEGACY_MOYEN = 'MOYEN';
    const LEGACY_ELEVE = 'ÉLEVÉ';
    const LEGACY_CRITIQUE = 'CRITIQUE';

    /**
     * Risk level hierarchy (higher number = higher risk).
     *
     * @var array
     */
    const HIERARCHY = [
        self::LOW => 1,
        self::MEDIUM => 2,
        self::HIGH => 3,
        self::CRITICAL => 4,
        // Legacy support.
        self::LEGACY_FAIBLE => 1,
        self::LEGACY_MOYEN => 2,
        self::LEGACY_ELEVE => 3,
        self::LEGACY_CRITIQUE => 4,
    ];

    /**
     * Score thresholds for risk level calculation.
     * These are the default values that can be overridden by configuration.
     */
    const DEFAULT_THRESHOLD_CRITICAL = 60;
    const DEFAULT_THRESHOLD_HIGH = 40;
    const DEFAULT_THRESHOLD_MEDIUM = 20;

    /**
     * Inactivity days thresholds (default values).
     */
    const DEFAULT_INACTIVITY_LEVEL1 = 3;
    const DEFAULT_INACTIVITY_LEVEL2 = 7;
    const DEFAULT_INACTIVITY_LEVEL3 = 14;

    /**
     * Special value for users who never logged in.
     */
    const NEVER_LOGGED_IN_DAYS = 999;

    /**
     * Seconds in a day.
     */
    const SECONDS_PER_DAY = 86400;

    /**
     * Get all risk levels in order from lowest to highest.
     *
     * @return array Array of risk level constants
     */
    public static function get_all_levels(): array {
        return [self::LOW, self::MEDIUM, self::HIGH, self::CRITICAL];
    }

    /**
     * Get the numeric value of a risk level in the hierarchy.
     *
     * @param string $level Risk level constant
     * @return int Hierarchy value (1-4), or 0 if invalid
     */
    public static function get_hierarchy_value(string $level): int {
        return self::HIERARCHY[$level] ?? 0;
    }

    /**
     * Check if risk level A is higher than or equal to risk level B.
     *
     * @param string $levelA First risk level
     * @param string $levelB Second risk level
     * @return bool True if A >= B in hierarchy
     */
    public static function is_at_least(string $levelA, string $levelB): bool {
        return self::get_hierarchy_value($levelA) >= self::get_hierarchy_value($levelB);
    }

    /**
     * Check if risk level A is higher than risk level B.
     *
     * @param string $levelA First risk level
     * @param string $levelB Second risk level
     * @return bool True if A > B in hierarchy
     */
    public static function is_higher_than(string $levelA, string $levelB): bool {
        return self::get_hierarchy_value($levelA) > self::get_hierarchy_value($levelB);
    }

    /**
     * Get all risk levels at or above a given level.
     * Useful for filtering queries where you want "MEDIUM and above".
     *
     * @param string $minimumlevel Minimum risk level
     * @return array Array of risk levels at or above the minimum
     */
    public static function get_levels_at_or_above(string $minimumlevel): array {
        $minvalue = self::get_hierarchy_value($minimumlevel);
        $levels = [];

        foreach (self::get_all_levels() as $level) {
            if (self::get_hierarchy_value($level) >= $minvalue) {
                $levels[] = $level;
            }
        }

        return $levels;
    }

    /**
     * Get SQL IN clause for filtering by minimum risk level.
     * Includes both new and legacy constants for backward compatibility.
     *
     * @param string $minimumlevel Minimum risk level
     * @return array Array with 'sql' (the IN clause) and 'params' (parameters)
     */
    public static function get_sql_filter_at_least(string $minimumlevel): array {
        $levels = self::get_levels_at_or_above($minimumlevel);
        $legacylevels = self::convert_to_legacy_array($levels);

        // Combine both new and legacy for compatibility.
        $alllevels = array_unique(array_merge($levels, $legacylevels));

        $placeholders = [];
        $params = [];
        $i = 0;
        foreach ($alllevels as $level) {
            $key = 'risklevel' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $level;
            $i++;
        }

        return [
            'sql' => 'IN (' . implode(', ', $placeholders) . ')',
            'params' => $params,
        ];
    }

    /**
     * Get the CSS class for a risk level badge.
     *
     * @param string $level Risk level
     * @return string CSS class name
     */
    public static function get_css_class(string $level): string {
        $normalized = self::normalize($level);

        $classes = [
            self::LOW => 'risk-low',
            self::MEDIUM => 'risk-medium',
            self::HIGH => 'risk-high',
            self::CRITICAL => 'risk-critical',
        ];

        return $classes[$normalized] ?? 'risk-unknown';
    }

    /**
     * Get the Bootstrap badge class for a risk level.
     *
     * @param string $level Risk level
     * @return string Bootstrap badge class
     */
    public static function get_badge_class(string $level): string {
        $normalized = self::normalize($level);

        $classes = [
            self::LOW => 'badge-success',
            self::MEDIUM => 'badge-warning',
            self::HIGH => 'badge-danger',
            self::CRITICAL => 'badge-dark',
        ];

        return $classes[$normalized] ?? 'badge-secondary';
    }

    /**
     * Get the translated display name for a risk level.
     *
     * @param string $level Risk level
     * @return string Translated name
     */
    public static function get_display_name(string $level): string {
        $normalized = self::normalize($level);

        $stringkeys = [
            self::LOW => 'risk_low',
            self::MEDIUM => 'risk_medium',
            self::HIGH => 'risk_high',
            self::CRITICAL => 'risk_critical',
        ];

        $key = $stringkeys[$normalized] ?? 'risk_low';
        return get_string($key, 'local_student_monitor');
    }

    /**
     * Normalize a risk level to the new constant format.
     * Handles both legacy French values and new English values.
     *
     * @param string $level Risk level (legacy or new format)
     * @return string Normalized risk level constant
     */
    public static function normalize(string $level): string {
        $level = strtoupper(trim($level));

        // Map legacy French values to new constants.
        $legacymap = [
            'FAIBLE' => self::LOW,
            'MOYEN' => self::MEDIUM,
            'ÉLEVÉ' => self::HIGH,
            'ELEVE' => self::HIGH, // Without accent.
            'CRITIQUE' => self::CRITICAL,
        ];

        if (isset($legacymap[$level])) {
            return $legacymap[$level];
        }

        // Check if it's already a valid new constant.
        if (in_array($level, self::get_all_levels())) {
            return $level;
        }

        // Default to LOW if unknown.
        return self::LOW;
    }

    /**
     * Convert a new risk level constant to legacy French format.
     * Used for backward compatibility during migration.
     *
     * @param string $level New risk level constant
     * @return string Legacy French risk level
     */
    public static function convert_to_legacy(string $level): string {
        $normalized = self::normalize($level);

        $map = [
            self::LOW => self::LEGACY_FAIBLE,
            self::MEDIUM => self::LEGACY_MOYEN,
            self::HIGH => self::LEGACY_ELEVE,
            self::CRITICAL => self::LEGACY_CRITIQUE,
        ];

        return $map[$normalized] ?? self::LEGACY_FAIBLE;
    }

    /**
     * Convert an array of new risk level constants to legacy format.
     *
     * @param array $levels Array of risk level constants
     * @return array Array of legacy risk level values
     */
    public static function convert_to_legacy_array(array $levels): array {
        return array_map([self::class, 'convert_to_legacy'], $levels);
    }

    /**
     * Calculate risk level from a score.
     *
     * @param int $score The calculated score
     * @return string Risk level constant
     */
    public static function from_score(int $score): string {
        $thresholdcritical = (int) get_config('local_student_monitor', 'threshold_critical')
            ?: self::DEFAULT_THRESHOLD_CRITICAL;
        $thresholdhigh = (int) get_config('local_student_monitor', 'threshold_high')
            ?: self::DEFAULT_THRESHOLD_HIGH;
        $thresholdmedium = (int) get_config('local_student_monitor', 'threshold_medium')
            ?: self::DEFAULT_THRESHOLD_MEDIUM;

        if ($score >= $thresholdcritical) {
            return self::CRITICAL;
        } else if ($score >= $thresholdhigh) {
            return self::HIGH;
        } else if ($score >= $thresholdmedium) {
            return self::MEDIUM;
        }

        return self::LOW;
    }

    /**
     * Get configured inactivity thresholds.
     *
     * @return array Associative array with level1, level2, level3 thresholds
     */
    public static function get_inactivity_thresholds(): array {
        return [
            'level1' => (int) get_config('local_student_monitor', 'inactivity_threshold_1')
                ?: self::DEFAULT_INACTIVITY_LEVEL1,
            'level2' => (int) get_config('local_student_monitor', 'inactivity_threshold_2')
                ?: self::DEFAULT_INACTIVITY_LEVEL2,
            'level3' => (int) get_config('local_student_monitor', 'inactivity_threshold_3')
                ?: self::DEFAULT_INACTIVITY_LEVEL3,
        ];
    }

    /**
     * Determine if intervention is needed based on risk level and tracking data.
     *
     * @param string $risklevel Current risk level
     * @param int $inactivitydays Days of inactivity
     * @param int $missingassignments Number of missing assignments
     * @return bool True if intervention is needed
     */
    public static function needs_intervention(string $risklevel, int $inactivitydays, int $missingassignments): bool {
        $normalized = self::normalize($risklevel);
        $thresholds = self::get_inactivity_thresholds();

        // Intervention needed if risk is HIGH or CRITICAL.
        if (self::is_at_least($normalized, self::HIGH)) {
            return true;
        }

        // Or if inactivity is at level 3.
        if ($inactivitydays >= $thresholds['level3']) {
            return true;
        }

        // Or if too many missing assignments.
        if ($missingassignments >= 5) {
            return true;
        }

        return false;
    }

    /**
     * Get the icon class for a risk level (Font Awesome).
     *
     * @param string $level Risk level
     * @return string Font Awesome icon class
     */
    public static function get_icon_class(string $level): string {
        $normalized = self::normalize($level);

        $icons = [
            self::LOW => 'fa-check-circle text-success',
            self::MEDIUM => 'fa-exclamation-circle text-warning',
            self::HIGH => 'fa-exclamation-triangle text-danger',
            self::CRITICAL => 'fa-times-circle text-dark',
        ];

        return $icons[$normalized] ?? 'fa-question-circle';
    }

    /**
     * Get SQL ORDER BY clause for sorting by risk level (highest first).
     *
     * @param string $fieldname The field name to sort by
     * @return string SQL CASE statement for ordering
     */
    public static function get_sql_order_by(string $fieldname = 'risk_level'): string {
        return "CASE {$fieldname}
            WHEN '" . self::CRITICAL . "' THEN 1
            WHEN '" . self::LEGACY_CRITIQUE . "' THEN 1
            WHEN '" . self::HIGH . "' THEN 2
            WHEN '" . self::LEGACY_ELEVE . "' THEN 2
            WHEN '" . self::MEDIUM . "' THEN 3
            WHEN '" . self::LEGACY_MOYEN . "' THEN 3
            WHEN '" . self::LOW . "' THEN 4
            WHEN '" . self::LEGACY_FAIBLE . "' THEN 4
            ELSE 5
        END";
    }
}
