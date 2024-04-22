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
 * Learning Goal Taxonomy Provider Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/externallib.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use core_privacy\tests\provider_testcase;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist_collection;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use mod_learninggoalwidget\privacy\provider;

/**
 * Learning Goal Taxonomy Privacy Provider Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
class provider_test extends provider_testcase {
    use mod_learninggoalwidget\utils;
    /**
     * testing privacy provider: get metadata
     *
     * @return void
     */
    public function test_get_metadata() {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $metadata = provider::get_metadata(new collection('mod_learninggoalwidget'));
        $itemcollection = $metadata->get_collection();
        $this->assertCount(1, $itemcollection);

        $item = reset($itemcollection);
        $this->assertEquals('learninggoalwidget_i_userpro', $item->get_name());

        $privacyfields = $item->get_privacy_fields();
        $this->assertCount(7, $privacyfields);
        $this->assertArrayHasKey('course', $privacyfields);
        $this->assertArrayHasKey('coursemodule', $privacyfields);
        $this->assertArrayHasKey('instance', $privacyfields);
        $this->assertArrayHasKey('topic', $privacyfields);
        $this->assertArrayHasKey('goal', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('progress', $privacyfields);

        $this->assertEquals('privacy:metadata:learninggoalwidget_i_userpro', $item->get_summary());
    }
    /**
     * testing privacy provider: get contexts for userid
     *
     * @return void
     */
    public function test_get_contexts_for_userid() {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course = $coursedata[0];
        $coursemodule = $coursedata[1];
        $instance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);

        // The user will be in these contexts.
        $usercontextids = [
            $cmcontext,
        ];

        mod_learninggoalwidget_external::update_user_progress(
            $course->id,
            $coursemodule->id,
            $instance->id,
            $user->id,
            $topicrecord->id,
            $goalrecord->id,
            50,
        );

        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
    }

    /**
     * Test returning a list of user IDs related to a context (assign).
     */
    public function test_get_users_in_context() {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course = $coursedata[0];
        $coursemodule = $coursedata[1];
        $instance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user1 = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);

        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user4->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user5->id, $course->id, 'editingteacher');

        mod_learninggoalwidget_external::update_user_progress(
            $course->id,
            $coursemodule->id,
            $instance->id,
            $user1->id,
            $topicrecord->id,
            $goalrecord->id,
            60,
        );

        $userlist = new \core_privacy\local\request\userlist($cmcontext, 'assign');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        $this->assertTrue(in_array($user1->id, $userids));
        $this->assertFalse(in_array($user2->id, $userids));
        $this->assertFalse(in_array($user3->id, $userids));
        $this->assertFalse(in_array($user4->id, $userids));
        $this->assertFalse(in_array($user5->id, $userids));
    }

    /**
     * Test exporting data with empty contextlist
     */
    public function test_empty_export_user_data_student() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $approvedlist = new approved_contextlist($user, '', []);
        $this->assertEquals(provider::export_user_data($approvedlist), null);
    }

    /**
     * Test exporting data
     */
    public function test_export_user_data_student() {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course = $coursedata[0];
        $coursecontext = \context_course::instance($course->id);
        $coursemodule = $coursedata[1];
        $instance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);

        mod_learninggoalwidget_external::update_user_progress(
            $course->id,
            $coursemodule->id,
            $instance->id,
            $user->id,
            $topicrecord->id,
            $goalrecord->id,
            50,
        );

        $writer = writer::with_context($cmcontext);
        $this->assertFalse($writer->has_any_data());

        // Add the course context as well to make sure there is no error.
        $approvedlist = new approved_contextlist($user, 'learninggoalwidget', [$cmcontext->id, $coursecontext->id]);
        provider::export_user_data($approvedlist);

        // Check export details.
        $progressexport = $writer->get_data(['progress'])->progress;
        $this->assertNotNull($progressexport);
        $this->assertEquals(count($progressexport), 1);
        $this->assertEquals($progressexport[0]->topictitle, "Artificial Intelligence Basics Part 1");
        $this->assertEquals($progressexport[0]->goaltitle, "Goal under Topic 1 to be updated");
        $this->assertEquals($progressexport[0]->progress, "50.00");
    }

    /**
     * Test delete all users data wrt training amplifier widget
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course = $coursedata[0];
        $coursemodule = $coursedata[1];
        $instance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);

        mod_learninggoalwidget_external::update_user_progress(
            $course->id,
            $coursemodule->id,
            $instance->id,
            $user->id,
            $topicrecord->id,
            $goalrecord->id,
            50,
        );

        // Delete all user data for this assignment.
        provider::delete_data_for_all_users_in_context($cmcontext);

        // Check all relevant tables.
        $records = $DB->get_records('learninggoalwidget_i_userpro');
        $this->assertEmpty($records);
        $records = $DB->get_records('learninggoalwidget_i_goals');
        $this->assertEmpty($records);
        $records = $DB->get_records('learninggoalwidget_i_topics');
        $this->assertEmpty($records);

        $widgetinstance1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $coursemodule1 = get_coursemodule_from_instance('quiz', $widgetinstance1->id, $course->id);
        $cmcontext1 = \context_module::instance($coursemodule1->id);
        // Delete all user data for this assignment.
        provider::delete_data_for_all_users_in_context($cmcontext1);
    }

    /**
     * A test for deleting all user data for one user.
     */
    public function test_delete_data_for_user() {
        global $DB;

        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course = $coursedata[0];
        $coursemodule = $coursedata[1];
        $instance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);
        $coursecontext = \context_course::instance($course->id);

        mod_learninggoalwidget_external::update_user_progress(
            $course->id,
            $coursemodule->id,
            $instance->id,
            $user->id,
            $topicrecord->id,
            $goalrecord->id,
            50,
        );

        // Delete user 1's data.
        $approvedlist = new approved_contextlist($user, 'learninggoalwidget', [$cmcontext->id, $coursecontext->id]);
        provider::delete_data_for_user($approvedlist);

        // Check all relevant tables.
        $records = $DB->get_records('learninggoalwidget_i_userpro', ['userid' => $user->id]);
        $this->assertEmpty($records);

        $widgetinstance1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $coursemodule1 = get_coursemodule_from_instance('quiz', $widgetinstance1->id, $course->id);
        $cmcontext1 = \context_module::instance($coursemodule1->id);

        $approvedlist = new approved_contextlist($user, 'quiz', [$cmcontext1->id, $coursecontext->id]);
        provider::delete_data_for_user($approvedlist);
    }

    /**
     * A test for deleting all user data for a bunch of users.
     */
    public function test_delete_data_for_users() {
        global $DB;
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course1 = $coursedata[0];
        $cm = $coursedata[1];
        $widgetinstance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user1 = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext1 = \context_module::instance($cm->id);

        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, 'student');
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id, 'student');

        mod_learninggoalwidget_external::update_user_progress(
            $course1->id,
            $cm->id,
            $widgetinstance->id,
            $user1->id,
            $topicrecord->id,
            $goalrecord->id,
            50,
        );
        mod_learninggoalwidget_external::update_user_progress(
            $course1->id,
            $cm->id,
            $widgetinstance->id,
            $user2->id,
            $topicrecord->id,
            $goalrecord->id,
            60,
        );
        mod_learninggoalwidget_external::update_user_progress(
            $course1->id,
            $cm->id,
            $widgetinstance->id,
            $user3->id,
            $topicrecord->id,
            $goalrecord->id,
            80,
        );

        $userlist = new \core_privacy\local\request\approved_userlist($cmcontext1, 'learninggoalwidget', [$user2->id, $user3->id]);
        provider::delete_data_for_users($userlist);

        // Check all relevant tables.
        $records = $DB->get_records('learninggoalwidget_i_userpro', ['userid' => $user2->id]);
        $this->assertEmpty($records);
        $records = $DB->get_records('learninggoalwidget_i_userpro', ['userid' => $user3->id]);
        $this->assertEmpty($records);
        $records = $DB->get_records('learninggoalwidget_i_userpro', ['userid' => $user1->id]);
        $this->assertNotEmpty($records);

        $widgetinstance2 = $this->getDataGenerator()->create_module('quiz', ['course' => $course1->id]);
        $coursemodule2 = get_coursemodule_from_instance('quiz', $widgetinstance2->id, $course1->id);
        $cmcontext2 = \context_module::instance($coursemodule2->id);

        $userlist = new \core_privacy\local\request\approved_userlist($cmcontext2, 'quiz', [$user2->id, $user3->id]);
        provider::delete_data_for_users($userlist);
    }
}
