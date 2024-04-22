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
 * Unit tests for the insert_goal function.
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
 * Unit tests for the insert_goal function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class insert_goal_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test insert_goal
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\insert_goal::execute
     * @covers \mod_learninggoalwidget\external\insert_goal::execute_returns
     * @covers \mod_learninggoalwidget\external\insert_goal::execute_parameters
     */
    public function test_insert_goal(): void {
        $this->setUp();

        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Insert goal under topic 1.
        $result = insert_goal::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            "Knowing theoretical foundations of AI",
            "TheoreticalFoundationsAI",
            "http://aibasics.goal1.at"
        );

        $result = external_api::clean_returnvalue(insert_goal::execute_returns(), $result);

        $result = get_taxonomy::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
        );

        $result = external_api::clean_returnvalue(get_taxonomy::execute_returns(), $result);

        $this->check_updatetopic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            $result
        );

        $goals = $this->check_updatetopic_getgoals($result);

        $this->assertIsArray($goals);
        $this->assertEquals(1, count($goals));
        $this->assertEquals(5, count($goals[0]));

        $goalranking = $goals[0][0];
        $goalid = $goals[0][1];
        $goalname = $goals[0][2];
        $goalshortname = $goals[0][3];
        $goalurl = $goals[0][4];

        $this->assertEquals(1, $goalranking);
        $this->assertIsNumeric($goalid);
        $this->assertTrue($goalid > 0);
        $this->assertEquals("Knowing theoretical foundations of AI", $goalname);
        $this->assertEquals("TheoreticalFoundationsAI", $goalshortname);
        $this->assertEquals("http://aibasics.goal1.at", $goalurl);
    }
}
