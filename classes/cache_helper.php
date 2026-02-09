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
 * Cache helper for Student Monitor.
 *
 * Provides convenient methods for caching data using Moodle's MUC.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Universite Numerique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Class cache_helper
 *
 * Helper class for managing plugin caches.
 */
class cache_helper {

    /** @var string Cache area for dashboard statistics */
    const AREA_DASHBOARD_STATS = 'dashboard_stats';

    /** @var string Cache area for risk distribution */
    const AREA_RISK_DISTRIBUTION = 'risk_distribution';

    /** @var string Cache area for student tracking */
    const AREA_STUDENT_TRACKING = 'student_tracking';

    /** @var string Cache area for supervisor performance */
    const AREA_SUPERVISOR_PERFORMANCE = 'supervisor_performance';

    /** @var string Cache area for user preferences */
    const AREA_USER_PREFERENCES = 'user_preferences';

    /**
     * Get a cache instance for the specified area.
     *
     * @param string $area Cache area name
     * @return \cache Cache instance
     */
    public static function get_cache(string $area): \cache {
        return \cache::make('local_student_monitor', $area);
    }

    /**
     * Get cached data or compute it if not cached.
     *
     * @param string $area Cache area name
     * @param string $key Cache key
     * @param callable $callback Function to compute the value if not cached
     * @return mixed Cached or computed value
     */
    public static function get_or_set(string $area, string $key, callable $callback) {
        $cache = self::get_cache($area);
        $data = $cache->get($key);

        if ($data === false) {
            $data = $callback();
            $cache->set($key, $data);
        }

        return $data;
    }

    /**
     * Set a value in the cache.
     *
     * @param string $area Cache area name
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @return bool Success
     */
    public static function set(string $area, string $key, $value): bool {
        $cache = self::get_cache($area);
        return $cache->set($key, $value);
    }

    /**
     * Get a value from the cache.
     *
     * @param string $area Cache area name
     * @param string $key Cache key
     * @return mixed|false Cached value or false if not found
     */
    public static function get(string $area, string $key) {
        $cache = self::get_cache($area);
        return $cache->get($key);
    }

    /**
     * Delete a value from the cache.
     *
     * @param string $area Cache area name
     * @param string $key Cache key
     * @return bool Success
     */
    public static function delete(string $area, string $key): bool {
        $cache = self::get_cache($area);
        return $cache->delete($key);
    }

    /**
     * Purge all data from a cache area.
     *
     * @param string $area Cache area name
     * @return bool Success
     */
    public static function purge(string $area): bool {
        $cache = self::get_cache($area);
        return $cache->purge();
    }

    /**
     * Purge all plugin caches.
     *
     * @return void
     */
    public static function purge_all(): void {
        $areas = [
            self::AREA_DASHBOARD_STATS,
            self::AREA_RISK_DISTRIBUTION,
            self::AREA_STUDENT_TRACKING,
            self::AREA_SUPERVISOR_PERFORMANCE,
        ];

        foreach ($areas as $area) {
            try {
                self::purge($area);
            } catch (\Exception $e) {
                debugging('Failed to purge cache area: ' . $area, DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * Get dashboard statistics with caching.
     *
     * @param callable $computefn Function to compute stats if not cached
     * @return \stdClass Statistics object
     */
    public static function get_dashboard_stats(callable $computefn): \stdClass {
        return self::get_or_set(self::AREA_DASHBOARD_STATS, 'global_stats', $computefn);
    }

    /**
     * Invalidate dashboard statistics cache.
     *
     * @return bool Success
     */
    public static function invalidate_dashboard_stats(): bool {
        return self::delete(self::AREA_DASHBOARD_STATS, 'global_stats');
    }

    /**
     * Get risk distribution with caching.
     *
     * @param callable $computefn Function to compute distribution if not cached
     * @return array Distribution data
     */
    public static function get_risk_distribution(callable $computefn): array {
        return self::get_or_set(self::AREA_RISK_DISTRIBUTION, 'distribution', $computefn);
    }

    /**
     * Get student tracking data with caching.
     *
     * @param int $userid User ID
     * @param int|null $courseid Course ID
     * @param callable $computefn Function to compute tracking data if not cached
     * @return \stdClass|null Tracking data
     */
    public static function get_student_tracking(int $userid, ?int $courseid, callable $computefn) {
        $key = "user_{$userid}_course_" . ($courseid ?? 'global');
        return self::get_or_set(self::AREA_STUDENT_TRACKING, $key, $computefn);
    }

    /**
     * Invalidate student tracking cache.
     *
     * @param int $userid User ID
     * @param int|null $courseid Course ID (null for all courses)
     * @return bool Success
     */
    public static function invalidate_student_tracking(int $userid, ?int $courseid = null): bool {
        if ($courseid !== null) {
            $key = "user_{$userid}_course_{$courseid}";
            return self::delete(self::AREA_STUDENT_TRACKING, $key);
        }

        // Purge all tracking cache for this user.
        // Note: MUC doesn't support wildcard deletion, so we purge the whole area.
        // In production, consider using a more targeted approach.
        return self::purge(self::AREA_STUDENT_TRACKING);
    }

    /**
     * Get supervisor performance data with caching.
     *
     * @param int|null $supervisorid Supervisor ID (null for all)
     * @param callable $computefn Function to compute performance if not cached
     * @return mixed Performance data
     */
    public static function get_supervisor_performance(?int $supervisorid, callable $computefn) {
        $key = 'supervisor_' . ($supervisorid ?? 'all');
        return self::get_or_set(self::AREA_SUPERVISOR_PERFORMANCE, $key, $computefn);
    }
}
