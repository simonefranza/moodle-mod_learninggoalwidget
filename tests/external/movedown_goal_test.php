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
 * Unit tests for the movedown_goal function.
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
 * Unit tests for the movedown_goal function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class movedown_goal_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test movedown_goal
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\movedown_goal::execute
     * @covers \mod_learninggoalwidget\external\movedown_goal::execute_returns
     * @covers \mod_learninggoalwidget\external\movedown_goal::execute_parameters
     */
    public function test_movedown_goal() : void {
        $this->setUp();

        [$resultcourse, $goalrecord1, , $goalrecord2, ] =
            $this->setup_course_and_insert_two_goals();

        // Move goal 1 behind goal 2 under topic 1.
        $result = movedown_goal::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            $goalrecord1->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(movedown_goal::execute_returns(), $result);

        $this->check_goal($result, $goalrecord1, $goalrecord2);
    }
}
