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
 * Learning Goal Taxonomy Privacy Provider Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\privacy;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use core_external\external_api;
use core_privacy\tests\provider_testcase;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist_collection;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use mod_learninggoalwidget\privacy\provider;
use mod_learninggoalwidget\external\update_user_progress;
use mod_learninggoalwidget\local\taxonomy;

/**
 * Learning Goal Taxonomy Privacy Provider Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class provider_test extends provider_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * helper function, sets up test environment
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }
    /**
     * testing privacy provider: get metadata
     * @return void
     *
     * @covers \mod_learninggoalwidget\privacy\provider::get_metadata
     */
    public function test_get_metadata(): void {
        // Reset all changes automatically after this test.
        $this->setUp();
        $metadata = provider::get_metadata(new collection('mod_learninggoalwidget'));
        $itemcollection = $metadata->get_collection();
        $this->assertCount(1, $itemcollection);

        $item = reset($itemcollection);
        $this->assertEquals('learninggoalwidget_progs', $item->get_name());

        $privacyfields = $item->get_privacy_fields();
        $this->assertCount(5, $privacyfields);
        $this->assertArrayHasKey('learninggoalwidgetid', $privacyfields);
        $this->assertArrayHasKey('topicid', $privacyfields);
        $this->assertArrayHasKey('goalid', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('progress', $privacyfields);

        $this->assertEquals('privacy:metadata:learninggoalwidget_progs', $item->get_summary());
    }
    /**
     * testing privacy provider: get contexts for userid
     *
     * @return void
     *
     * @covers \mod_learninggoalwidget\privacy\provider::get_contexts_for_userid
     */
    public function test_get_contexts_for_userid(): void {
        // Reset all changes automatically after this test.
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $userid = $res->user->id;
        $this->insert_two_goals($lgwid);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic = $taxonomy->children[0];
        $goal = $topic->children[0];

        $student = $this->create_user('student', $res->course->id, true);
        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $lgwid);
        $cmcontext = \context_module::instance($coursemodule->id);

        // The user will be in these contexts.
        $usercontextids = [
            $cmcontext,
        ];

        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            50,
        );

        $contextlist = provider::get_contexts_for_userid($student->id);
        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
    }

    /**
     * Test returning a list of user IDs related to a context (assign).
     *
     * @covers \mod_learninggoalwidget\privacy\provider::get_users_in_context
     */
    public function test_get_users_in_context(): void {
        // Reset all changes automatically after this test.
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $this->insert_two_goals($lgwid);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic = $taxonomy->children[0];
        $goal = $topic->children[0];

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $lgwid);
        $cmcontext = \context_module::instance($coursemodule->id);

        $user1 = $this->create_user('student', $res->course->id, true);
        $user2 = $this->create_user('student', $res->course->id, false);
        $user3 = $this->create_user('student', $res->course->id, false);
        $user4 = $this->create_user('student', $res->course->id, false);
        $user5 = $this->create_user('editingteacher', $res->course->id, false);

        // User1 is active.
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
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
     *
     * @covers \mod_learninggoalwidget\privacy\provider::export_user_data
     */
    public function test_empty_export_user_data_student(): void {
        $this->setUp();
        $user = $this->getDataGenerator()->create_user();
        $approvedlist = new approved_contextlist($user, '', []);
        $this->assertEquals(provider::export_user_data($approvedlist), null);
    }

    /**
     * Test exporting data
     *
     * @covers \mod_learninggoalwidget\privacy\provider::export_user_data
     */
    public function test_export_user_data_student(): void {
        // Reset all changes automatically after this test.
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $userid = $res->user->id;
        $this->insert_two_goals($lgwid);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic = $taxonomy->children[0];
        $goal = $topic->children[0];

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $lgwid);
        $coursecontext = \context_course::instance($coursemodule->course);
        $cmcontext = \context_module::instance($coursemodule->id);

        $student = $this->create_user('student', $res->course->id, true);
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            50,
        );

        $writer = writer::with_context($cmcontext);
        $this->assertFalse($writer->has_any_data());

        // Add the course context as well to make sure there is no error.
        $approvedlist = new approved_contextlist($student, 'learninggoalwidget', [$cmcontext->id, $coursecontext->id]);
        provider::export_user_data($approvedlist);

        // Check export details.
        $progressexport = $writer->get_data(['progress'])->progress;
        $this->assertNotNull($progressexport);
        $this->assertEquals(count($progressexport), 1);
        $this->assertEquals($progressexport[0]->topictitle, $topic->name);
        $this->assertEquals($progressexport[0]->goaltitle, $goal->name);
        $this->assertEquals($progressexport[0]->progress, "50.00");
    }

    /**
     * Test delete all users data wrt training amplifier widget
     *
     * @covers \mod_learninggoalwidget\privacy\provider::delete_data_for_all_users_in_context
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;

        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $this->insert_two_goals($lgwid);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic = $taxonomy->children[0];
        $goal = $topic->children[0];

        // Reset all changes automatically after this test.
        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $lgwid);
        $cmcontext = \context_module::instance($coursemodule->id);

        $student = $this->create_user('student', $res->course->id, true);
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            50,
        );

        // Delete all user data for this assignment.
        provider::delete_data_for_all_users_in_context($cmcontext);

        // Check all relevant tables.
        $records = $DB->get_records('learninggoalwidget_progs');
        $this->assertEmpty($records);
    }

    /**
     * A test for deleting all user data for one user.
     *
     * @covers \mod_learninggoalwidget\privacy\provider::delete_data_for_user
     */
    public function test_delete_data_for_user(): void {
        global $DB;

        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $this->insert_two_goals($lgwid);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic = $taxonomy->children[0];
        $goal = $topic->children[0];

        // Reset all changes automatically after this test.
        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $lgwid);
        $cmcontext = \context_module::instance($coursemodule->id);
        $coursecontext = \context_course::instance($coursemodule->course);

        $student = $this->create_user('student', $res->course->id, true);
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            50,
        );

        // Delete user 1's data.
        $approvedlist = new approved_contextlist($student, 'learninggoalwidget', [$cmcontext->id, $coursecontext->id]);
        provider::delete_data_for_user($approvedlist);

        // Check all relevant tables.
        $records = $DB->get_records('learninggoalwidget_progs', ['userid' => $student->id]);
        $this->assertEmpty($records);
    }

    /**
     * A test for deleting all user data for a bunch of users.
     *
     * @covers \mod_learninggoalwidget\privacy\provider::delete_data_for_users
     */
    public function test_delete_data_for_users(): void {
        global $DB;

        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $this->insert_two_goals($lgwid);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic = $taxonomy->children[0];
        $goal = $topic->children[0];

        // Reset all changes automatically after this test.
        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $lgwid);
        $cmcontext1 = \context_module::instance($coursemodule->id);

        $user1 = $this->create_user('student', $res->course->id, true);
        $user2 = $this->create_user('student', $res->course->id, false);
        $user3 = $this->create_user('student', $res->course->id, false);

        $this->setUser($user1);
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            50,
        );
        $this->setUser($user2);
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            60,
        );
        $this->setUser($user3);
        update_user_progress::execute(
            $lgwid,
            $topic->topicid,
            $goal->goalid,
            80,
        );

        $userlist = new \core_privacy\local\request\approved_userlist($cmcontext1, 'learninggoalwidget', [$user2->id, $user3->id]);
        provider::delete_data_for_users($userlist);

        // Check all relevant tables.
        $records = $DB->get_records('learninggoalwidget_progs', ['userid' => $user2->id]);
        $this->assertEmpty($records);
        $records = $DB->get_records('learninggoalwidget_progs', ['userid' => $user3->id]);
        $this->assertEmpty($records);
        $records = $DB->get_records('learninggoalwidget_progs', ['userid' => $user1->id]);
        $this->assertNotEmpty($records);
    }
}
