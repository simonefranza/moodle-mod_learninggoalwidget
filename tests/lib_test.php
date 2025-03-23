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
 * Learning Goal Taxonomy Lib Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\user_taxonomy;
use mod_learninggoalwidget\external\update_user_progress;
use core_external\external_api;

/**
 * Learning Goal Taxonomy Lib Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class lib_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing method learninggoalwidget_supports
     * @return void
     *
     * @covers ::learninggoalwidget_supports
     */
    public function test_supports(): void {
        $this->assertSame(learninggoalwidget_supports(FEATURE_MOD_PURPOSE), MOD_PURPOSE_ASSESSMENT);
        $this->assertSame(learninggoalwidget_supports(FEATURE_BACKUP_MOODLE2), true);
        $this->assertSame(learninggoalwidget_supports(FEATURE_NO_VIEW_LINK), true);
        $this->assertSame(learninggoalwidget_supports('test'), null);
    }

    /**
     * testing method learninggoalwidget_update_instance
     * @return void
     *
     * @covers ::learninggoalwidget_add_instance
     * @covers ::learninggoalwidget_update_instance
     */
    public function test_update(): void {
        global $DB;
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;

        $taxonomy = new \stdClass;
        $newname = 'Different name';
        $taxonomy->name = $newname;
        $taxonomy->children = $this->create_taxonomy(1, 1);

        $data = (object)[
            'instance' => $lgwid + 342,
            'name' => $newname,
            'taxonomy' => json_encode($taxonomy),
        ];
        $this->assertFalse(learninggoalwidget_update_instance($data));
        $record = $DB->get_record('learninggoalwidget', ['id' => $lgwid]);
        $this->assertSame($record->name, 'name');
        $newtaxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame($newtaxonomy->name, 'name');
        $this->assertSame(count($newtaxonomy->children), 0);

        $data->instance = $lgwid;
        $this->assertTrue(learninggoalwidget_update_instance($data));
        $record = $DB->get_record('learninggoalwidget', ['id' => $lgwid]);
        $this->assertSame($record->name, $newname);
        $newtaxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame($newtaxonomy->name, $newname);
        $this->assertSame(count($newtaxonomy->children), 1);
        $this->check_topic($newtaxonomy->children[0], 0, 1, 1, true);
    }

    /**
     * testing method learninggoalwidget_delete_instance
     * @return void
     *
     * @covers ::learninggoalwidget_add_instance
     * @covers ::learninggoalwidget_update_instance
     * @covers ::learninggoalwidget_delete_instance
     */
    public function test_delete(): void {
        global $DB;
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;

        $taxonomy = new \stdClass;
        $newname = 'Different name';
        $taxonomy->name = $newname;
        $taxonomy->children = $this->create_taxonomy(1, 1);

        $data = (object)[
            'instance' => $lgwid,
            'name' => $newname,
            'taxonomy' => json_encode($taxonomy),
        ];

        $this->assertTrue(learninggoalwidget_update_instance($data));
        $record = $DB->get_record('learninggoalwidget', ['id' => $lgwid]);
        $this->assertSame($record->name, $newname);
        $newtaxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame($newtaxonomy->name, $newname);
        $this->assertSame(count($newtaxonomy->children), 1);
        $this->check_topic($newtaxonomy->children[0], 0, 1, 1, true);

        // Update learning goal 1 progess to 99.
        $this->create_user('student', $res->course->id, true);
        $result = update_user_progress::execute(
            $lgwid,
            $newtaxonomy->children[0]->topicid,
            $newtaxonomy->children[0]->children[0]->goalid,
            99
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);
        $this->assertTrue($DB->record_exists('learninggoalwidget', ['id' => $lgwid]));
        $this->assertTrue($DB->record_exists('learninggoalwidget_topics',
            ['learninggoalwidgetid' => $lgwid]));
        $this->assertTrue($DB->record_exists('learninggoalwidget_goals',
            ['learninggoalwidgetid' => $lgwid]));
        $this->assertTrue($DB->record_exists('learninggoalwidget_progs',
            ['learninggoalwidgetid' => $lgwid]));

        $taxonomy = json_decode(user_taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame($taxonomy->name, $newname);
        $this->assertSame(count($taxonomy->children), 1);
        $this->check_topic($taxonomy->children[0], 0, 1, 1, true);

        // Delete instance.
        $this->assertTrue(learninggoalwidget_delete_instance($lgwid));
        $this->assertFalse($DB->record_exists('learninggoalwidget', ['id' => $lgwid]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_topics',
            ['learninggoalwidgetid' => $lgwid]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_goals',
            ['learninggoalwidgetid' => $lgwid]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_progs',
            ['learninggoalwidgetid' => $lgwid]));

        // Try to delete instance again.
        $this->assertFalse(learninggoalwidget_delete_instance($lgwid));
    }

    /**
     * testing method learninggoalwidget_cm_info_view as guest
     * @return void
     *
     * @covers ::learninggoalwidget_add_instance
     * @covers ::learninggoalwidget_update_instance
     * @covers ::learninggoalwidget_cm_info_view
     */
    public function test_cm_info_view_as_guest(): void {
        $res = $this->setup_widget();
        // Switch to guest user.
        $this->setGuestUser();
        // Create a cm_info object.
        $cm = get_fast_modinfo($res->course->id)->get_cm($res->instance->cmid);

        // Call the function being tested.
        learninggoalwidget_cm_info_view($cm);

        // Assert the guest access message is set.
        $this->assertStringContainsString(get_string('guestaccess', 'learninggoalwidget'), $cm->content);
    }

    /**
     * testing method learninggoalwidget_cm_info_view as user without access
     * @return void
     *
     * @covers ::learninggoalwidget_add_instance
     * @covers ::learninggoalwidget_update_instance
     * @covers ::learninggoalwidget_cm_info_view
     */
    public function test_cm_info_view_as_user_without_access(): void {
        $res = $this->setup_widget();

        // Create a user without capability.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Create a cm_info object.
        $cm = get_fast_modinfo($res->course->id)->get_cm($res->instance->cmid);

        // Call the function being tested.
        learninggoalwidget_cm_info_view($cm);

        // Assert the no access message is set.
        $this->assertStringContainsString(get_string('noaccess', 'learninggoalwidget'), $cm->content);
    }

    /**
     * testing method learninggoalwidget_cm_info_view as user with access
     * @return void
     *
     * @covers ::learninggoalwidget_add_instance
     * @covers ::learninggoalwidget_update_instance
     * @covers ::learninggoalwidget_cm_info_view
     */
    public function test_cm_info_view_as_user_with_access(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;

        $taxonomy = new \stdClass;
        $newname = 'Different name';
        $taxonomy->name = $newname;
        $taxonomy->children = $this->create_taxonomy(1, 1);

        $data = (object)[
            'instance' => $lgwid,
            'name' => $newname,
            'taxonomy' => json_encode($taxonomy),
        ];

        $this->assertTrue(learninggoalwidget_update_instance($data));

        // Create a cm_info object.
        $cm = get_fast_modinfo($res->course->id)->get_cm($res->instance->cmid);

        // Call the function being tested.
        learninggoalwidget_cm_info_view($cm);

        // Assert the no access message is not set.
        $this->assertStringNotContainsString(get_string('noaccess', 'learninggoalwidget'), $cm->content);
        // Assert the guest message is not set.
        $this->assertStringNotContainsString(get_string('guestaccess', 'learninggoalwidget'), $cm->content);

        // Check the rest.
        $this->assertNotEmpty($cm->content);
        $this->assertStringContainsString($newname, $cm->content);
        $this->assertStringContainsString(
            "learninggoals-widget-{$res->course->id}-{$res->instance->cmid}-{$lgwid}",
            $cm->content);
        $this->assertStringContainsString(
            get_string('progresslabel0', 'learninggoalwidget'),
            $cm->content);
        $this->assertStringContainsString(
            get_string('progresslabel50', 'learninggoalwidget'),
            $cm->content);
        $this->assertStringContainsString(
            get_string('progresslabel100', 'learninggoalwidget'),
            $cm->content);
        $this->assertStringContainsString(
            get_string('contentview', 'learninggoalwidget'),
            $cm->content);
        $this->assertStringContainsString(
            get_string('examview', 'learninggoalwidget'),
            $cm->content);
    }
}
