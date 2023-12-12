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

namespace mod_learninggoalwidget\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use externallib_advanced_testcase;
use core_external\external_api;

/**
 * Unit tests for the update_goal function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class update_goal_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test update_goal
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\update_goal::execute
     * @covers \mod_learninggoalwidget\external\update_goal::execute_returns
     * @covers \mod_learninggoalwidget\external\update_goal::execute_parameters
     */
    public function test_update_goal() : void {
        $this->setUp();
        [$resultcourse, $goalrecord, ] = $this->setup_course_and_insert_goals();

        // Update goal under topic 1.
        $result = update_goal::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            $goalrecord->id,
            "Updated Goalname",
            "Updated Goal Shortname",
            "http://goal1.updated.at"
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_goal::execute_returns(), $result);

        $resulttopic = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            $result
        );

        $this->assertEquals(1, count($resulttopic[0]));
        $this->assertEquals(5, count($resulttopic[0][0]));

        $goalrank = $resulttopic[0][0][0];
        $goalid = $resulttopic[0][0][1];
        $goalname = $resulttopic[0][0][2];
        $goalshortname = $resulttopic[0][0][3];
        $goalurl = $resulttopic[0][0][4];

        $this->assertEquals(1, $goalrank);
        $this->assertIsNumeric($goalid);
        $this->assertEquals($goalrecord->id, $goalid);
        $this->assertEquals("Updated Goalname", $goalname);
        $this->assertEquals("Updated Goal Shortname", $goalshortname);
        $this->assertEquals("http://goal1.updated.at", $goalurl);
    }
}
