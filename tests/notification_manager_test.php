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
 * Notification manager tests.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK - Université Numérique Cheikh Hamidou Kane
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_student_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Notification manager test class.
 *
 * @package    local_student_monitor
 * @copyright  2025 UNCHK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_manager_test extends \advanced_testcase {

    /**
     * Setup for each test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test creating a basic notification.
     */
    public function test_create_notification() {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_student_monitor\manager\notification_manager();

        $notificationid = $manager->create_notification(
            $user->id,
            'manual_alert',
            'Test Subject',
            'Test Message',
            null,
            ['email', 'moodle'],
            ['test' => true]
        );

        $this->assertNotEmpty($notificationid);

        // Verify database record.
        $notification = $DB->get_record('local_sm_notifications', ['id' => $notificationid]);
        $this->assertNotFalse($notification);
        $this->assertEquals($user->id, $notification->userid);
        $this->assertEquals('manual_alert', $notification->type);
        $this->assertEquals('Test Subject', $notification->subject);
        $this->assertEquals('pending', $notification->status);
        $this->assertEquals('email,moodle', $notification->channels);
    }

    /**
     * Test creating inactivity notification.
     */
    public function test_create_inactivity_notification() {
        global $DB;

        $user = $this->getDataGenerator()->create_user([
            'firstname' => 'Fatou',
            'lastname' => 'DIOP',
        ]);

        $manager = new \local_student_monitor\manager\notification_manager();
        $notificationid = $manager->create_inactivity_notification($user->id, 'level1', 7);

        $this->assertNotEmpty($notificationid);

        $notification = $DB->get_record('local_sm_notifications', ['id' => $notificationid]);
        $this->assertEquals($user->id, $notification->userid);
        $this->assertEquals('inactivity_level1', $notification->type);
        $this->assertStringContainsString('Fatou', $notification->message);
    }

    /**
     * Test placeholder replacement.
     */
    public function test_replace_placeholders() {
        $user = $this->getDataGenerator()->create_user([
            'firstname' => 'Fatou',
            'lastname' => 'DIOP',
            'email' => 'fatou.diop@unchk.edu.sn',
        ]);

        $template = "Bonjour {firstname} {lastname}, votre email est {email}";

        $manager = new \local_student_monitor\manager\notification_manager();
        $result = $manager->replace_placeholders($template, $user);

        $this->assertEquals("Bonjour Fatou DIOP, votre email est fatou.diop@unchk.edu.sn", $result);
    }

    /**
     * Test checking for recent notifications.
     */
    public function test_has_recent_notification() {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_student_monitor\manager\notification_manager();

        // No recent notification initially.
        $this->assertFalse($manager->has_recent_notification($user->id, 'inactivity_level1', 86400));

        // Create a notification.
        $manager->create_inactivity_notification($user->id, 'level1', 5);

        // Should now find recent notification.
        $this->assertTrue($manager->has_recent_notification($user->id, 'inactivity_level1', 86400));
    }

    /**
     * Test updating notification status.
     */
    public function test_update_notification_status() {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_student_monitor\manager\notification_manager();

        $notificationid = $manager->create_notification(
            $user->id,
            'manual_alert',
            'Test',
            'Test message'
        );

        // Update to sent.
        $manager->update_notification_status($notificationid, 'sent');

        $notification = $DB->get_record('local_sm_notifications', ['id' => $notificationid]);
        $this->assertEquals('sent', $notification->status);
        $this->assertNotEmpty($notification->timesent);

        // Update to read.
        $manager->update_notification_status($notificationid, 'read');

        $notification = $DB->get_record('local_sm_notifications', ['id' => $notificationid]);
        $this->assertEquals('read', $notification->status);
        $this->assertNotEmpty($notification->timeread);
    }
}
