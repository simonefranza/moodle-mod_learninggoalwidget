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
 * Unit tests for the get_taxonomy function.
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

use externallib_advanced_testcase;
use core_external\external_api;

/**
 * Unit tests for the get_taxonomy function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class get_taxonomy_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test get_taxonomy
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\get_taxonomy::execute
     * @covers \mod_learninggoalwidget\external\get_taxonomy::execute_returns
     * @covers \mod_learninggoalwidget\external\get_taxonomy::execute_parameters
     */
    public function test_get_taxonomy(): void {
        $this->setUp();
        $res = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Get taxonomy.
        $result = get_taxonomy::execute($res->instance->id);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(get_taxonomy::execute_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $expectedjson = new \stdClass();
        $expectedjson->name = "name";

        $topic1 = new \stdClass();
        $topic1->topicid = $res->topic1->id;
        $topic1->name = "Artificial Intelligence Basics Part 1";
        $topic1->shortname = "AIBasics 1";
        $topic1->url = "http://aibasics1.at";
        $topic1->ranking = 1;
        $topic1->children = [];

        $topic2 = new \stdClass();
        $topic2->topicid = $res->topic2->id;
        $topic2->name = "Artificial Intelligence Basics Part 2";
        $topic2->shortname = "AIBasics 2";
        $topic2->url = "http://aibasics2.at";
        $topic2->ranking = 2;
        $topic2->children = [];
        $expectedjson->children = [$topic1, $topic2];

        $this->check_json($parsed, $expectedjson);
    }
}
