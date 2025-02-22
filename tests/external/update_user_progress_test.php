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
 * Unit tests for the update_user_progress function.
 *
 * @package    mod_learninggoalwidget
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\userTaxonomy;
use externallib_advanced_testcase;
use core_external\external_api;

/**
 * Unit tests for the update_user_progress function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class update_user_progress_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test update_user_progress to trigger exception
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute_returns
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute_parameters
     */
    public function test_update_user_progress_exc(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $this->insert_two_goals($lgwid);

        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic1 = $taxonomy->children[0];

        // Teacher cannot get data of user.
        $this->expectException(\required_capability_exception::class);
        update_user_progress::execute(
            $lgwid,
            $topic1->topicid,
            $topic1->children[0]->goalid,
            99
        );
    }
    /**
     * Test update_user_progress
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute_returns
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute_parameters
     */
    public function test_update_user_progress(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $this->insert_two_goals($lgwid);

        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $topic1 = $taxonomy->children[0];

        // Update learning goal 1 progess to 99.
        $this->create_user('student', $res->course->id, true);
        $result = update_user_progress::execute(
            $lgwid,
            $topic1->topicid,
            $topic1->children[0]->goalid,
            99
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));

        for ($i = 0; $i < 2; $i++) {
            $topic = $taxonomy->children[$i];
            $this->check_topic($topic, $i, $i + 1, 2, true);
        }
        $this->assertSame($taxonomy->children[0]->children[0]->pro, 99);
        $this->assertSame($taxonomy->children[0]->children[1]->pro, 0);
        $this->assertSame($taxonomy->children[1]->children[0]->pro, 0);
        $this->assertSame($taxonomy->children[1]->children[1]->pro, 0);

        $topic1 = $taxonomy->children[0];

        // Update learning goal 1 progess to 50.
        $result = update_user_progress::execute(
            $lgwid,
            $topic1->topicid,
            $topic1->children[0]->goalid,
            50
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));

        for ($i = 0; $i < 2; $i++) {
            $topic = $taxonomy->children[$i];
            $this->check_topic($topic, $i, $i + 1, 2, true);
        }
        $this->assertSame($taxonomy->children[0]->children[0]->pro, 50);
        $this->assertSame($taxonomy->children[0]->children[1]->pro, 0);
        $this->assertSame($taxonomy->children[1]->children[0]->pro, 0);
        $this->assertSame($taxonomy->children[1]->children[1]->pro, 0);

        $topic1 = $taxonomy->children[0];

        // Update learning goal 2 progess to 100.
        $result = update_user_progress::execute(
            $lgwid,
            $topic1->topicid,
            $topic1->children[1]->goalid,
            100
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));

        for ($i = 0; $i < 2; $i++) {
            $topic = $taxonomy->children[$i];
            $this->check_topic($topic, $i, $i + 1, 2, true);
        }
        $this->assertSame($taxonomy->children[0]->children[0]->pro, 50);
        $this->assertSame($taxonomy->children[0]->children[1]->pro, 100);
        $this->assertSame($taxonomy->children[1]->children[0]->pro, 0);
        $this->assertSame($taxonomy->children[1]->children[1]->pro, 0);
    }
}
