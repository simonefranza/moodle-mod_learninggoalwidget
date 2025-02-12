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
 * Learning Goal Taxonomy - UserTaxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use externallib_advanced_testcase;
use core_external\external_api;

use mod_learninggoalwidget\local\userTaxonomy;
use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\topic;
use mod_learninggoalwidget\local\goal;
use mod_learninggoalwidget\external\update_user_progress;

/**
 * Learning Goal Taxonomy UserTaxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class user_taxonomy_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing method userTaxonomy::get_taxonomy_as_json
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\userTaxonomy::get_taxonomy_as_json
     * @covers \mod_learninggoalwidget\local\userTaxonomy::get_topics
     */
    public function test_update_taxonomy(): void {
        $this->assertSame(userTaxonomy::get_taxonomy_as_json(null), '{}');
        $this->expectException(dml_missing_record_exception::class);
        userTaxonomy::get_taxonomy_as_json(-1);

        $res = $this->setup_widget();
        $lgwid = $res->instance->id;

        // Test with teacher user.
        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));

        // Test with student user.
        $student = $this->create_user('student', $res->course->id, true);
        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame(count($taxonomy->children), 0);

        $taxonomy = new \stdClass;
        $numtopics = 2;
        $numgoals = 2;
        $taxonomy->children = $this->create_taxonomy($numtopics, $numgoals);
        $taxonomy->children[1]->children[0]->deleted = true;
        $taxonomy->children[1]->children[1]->deleted = true;
        $this->setUser($res->user);
        taxonomy::update_taxonomy($lgwid, $taxonomy);
        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));
        $this->check_topic($taxonomy->children[0], 0, 1, 2, true);
        $this->check_topic($taxonomy->children[1], 1, 2, 0, true);
        $this->assertFalse(isset($taxonomy->children[0]->children[0]->pro));
        $this->assertFalse(isset($taxonomy->children[0]->children[1]->pro));
        $this->assertFalse($taxonomy->student);

        // Update progress.
        $this->setUser($student);
        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));
        $this->check_topic($taxonomy->children[0], 0, 1, 2, true);
        $this->check_topic($taxonomy->children[1], 1, 2, 0, true);
        $topic0 = $taxonomy->children[0];
        $goal0 = $topic0->children[0];
        $goal1 = $topic0->children[1];
        $this->assertSame($goal0->pro, 0);
        $this->assertSame($goal1->pro, 0);
        $this->assertTrue($taxonomy->student);

        $result = update_user_progress::execute(
            $lgwid,
            $topic0->topicid,
            $goal0->goalid,
            50
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $result = update_user_progress::execute(
            $lgwid,
            $topic0->topicid,
            $goal1->goalid,
            25
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $taxonomy = json_decode(userTaxonomy::get_taxonomy_as_json($lgwid));
        $this->check_topic($taxonomy->children[0], 0, 1, 2, true);
        $this->check_topic($taxonomy->children[1], 1, 2, 0, true);
        $this->assertSame($taxonomy->children[0]->children[0]->pro, 50);
        $this->assertSame($taxonomy->children[0]->children[1]->pro, 25);
        $this->assertTrue($taxonomy->student);
    }
}
