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
 * Student tracker tests.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Student tracker test class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_tracker_test extends \advanced_testcase {

    /**
     * Setup for each test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test updating student tracking.
     */
    public function test_update_student_tracking() {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $tracker = new \local_student_monitor\manager\student_tracker();

        $tracker->update_student_tracking($user->id);

        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $user->id]);
        $this->assertNotFalse($tracking);
        $this->assertEquals($user->id, $tracking->userid);
        $this->assertNotEmpty($tracking->risk_level);
    }

    /**
     * Test risk level calculation — highest criterion wins.
     */
    public function test_calculate_risk_level() {
        $tracker = new \local_student_monitor\manager\student_tracker();

        // Test LOW: no inactivity, no missing activities.
        $tracking = new \stdClass();
        $tracking->inactivity_days = 2;
        $tracking->missing_activities = 0;
        $tracking->notification_count = 0;
        $this->assertEquals(risk_level::LOW, $tracker->calculate_risk_level($tracking));

        // Test MEDIUM from inactivity only.
        $tracking->inactivity_days = 5;
        $tracking->missing_activities = 0;
        $this->assertEquals(risk_level::MEDIUM, $tracker->calculate_risk_level($tracking));

        // Test MEDIUM from missing activities only.
        $tracking->inactivity_days = 0;
        $tracking->missing_activities = 1;
        $this->assertEquals(risk_level::MEDIUM, $tracker->calculate_risk_level($tracking));

        // Test HIGH from inactivity.
        $tracking->inactivity_days = 10;
        $tracking->missing_activities = 0;
        $this->assertEquals(risk_level::HIGH, $tracker->calculate_risk_level($tracking));

        // Test HIGH from missing activities.
        $tracking->inactivity_days = 0;
        $tracking->missing_activities = 4;
        $this->assertEquals(risk_level::HIGH, $tracker->calculate_risk_level($tracking));

        // Test CRITICAL: both criteria high.
        $tracking->inactivity_days = 15;
        $tracking->missing_activities = 6;
        $tracking->notification_count = 12;
        $this->assertEquals(risk_level::CRITICAL, $tracker->calculate_risk_level($tracking));

        // Test highest wins: critical inactivity, low activities.
        $tracking->inactivity_days = 15;
        $tracking->missing_activities = 0;
        $this->assertEquals(risk_level::CRITICAL, $tracker->calculate_risk_level($tracking));

        // Test highest wins: low inactivity, critical activities.
        $tracking->inactivity_days = 0;
        $tracking->missing_activities = 6;
        $this->assertEquals(risk_level::CRITICAL, $tracker->calculate_risk_level($tracking));

        // Test notification_count does NOT affect risk.
        $tracking->inactivity_days = 2;
        $tracking->missing_activities = 0;
        $tracking->notification_count = 100;
        $this->assertEquals(risk_level::LOW, $tracker->calculate_risk_level($tracking));
    }

    /**
     * Test assigning to supervisor.
     */
    public function test_assign_to_supervisor() {
        global $DB;

        $student = $this->getDataGenerator()->create_user();
        $supervisor = $this->getDataGenerator()->create_user();

        $tracker = new \local_student_monitor\manager\student_tracker();

        $tracker->update_student_tracking($student->id);
        $tracker->assign_to_supervisor($student->id, $supervisor->id);

        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $student->id]);
        $this->assertEquals($supervisor->id, $tracking->assigned_to);
    }

    /**
     * Test adding notes.
     */
    public function test_add_notes() {
        global $DB;

        $student = $this->getDataGenerator()->create_user();
        $tracker = new \local_student_monitor\manager\student_tracker();

        $tracker->update_student_tracking($student->id);
        $tracker->add_notes($student->id, 'Test note 1');
        $tracker->add_notes($student->id, 'Test note 2');

        $tracking = $DB->get_record('local_sm_student_tracking', ['userid' => $student->id]);
        $this->assertStringContainsString('Test note 1', $tracking->notes);
        $this->assertStringContainsString('Test note 2', $tracking->notes);
    }

    /**
     * Test getting students at risk.
     */
    public function test_get_students_at_risk() {
        $tracker = new \local_student_monitor\manager\student_tracker();

        // Create test users with different risk levels.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $tracker->update_student_tracking($user1->id);
        $tracker->update_student_tracking($user2->id);

        // Get all at-risk students.
        $students = $tracker->get_students_at_risk();

        $this->assertIsArray($students);
    }

    /**
     * Test getting statistics.
     */
    public function test_get_statistics() {
        $tracker = new \local_student_monitor\manager\student_tracker();

        // Create some test data.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $tracker->update_student_tracking($user1->id);
        $tracker->update_student_tracking($user2->id);

        $stats = $tracker->get_statistics();

        $this->assertIsObject($stats);
        $this->assertObjectHasAttribute('total_students', $stats);
        $this->assertObjectHasAttribute('critical', $stats);
        $this->assertObjectHasAttribute('high', $stats);
        $this->assertObjectHasAttribute('medium', $stats);
        $this->assertObjectHasAttribute('low', $stats);
    }
}
