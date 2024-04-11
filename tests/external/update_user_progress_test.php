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
class update_user_progress_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test update_user_progress
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute_returns
     * @covers \mod_learninggoalwidget\external\update_user_progress::execute_parameters
     */
    public function test_update_user_progress() : void {
        $this->setUp();
        [$resultcourse, $goalrecord1, , $goalrecord2, ] = $this->setup_course_and_insert_two_goals();

        // Update learning goal 1 progess to 99.
        $result = update_user_progress::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[7]->id,
            $resultcourse[3]->id,
            $goalrecord1->id,
            99
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            99,
            $goalrecord1->id,
            $result
        );

        // Update learning goal 1 progess to 50.
        $result = update_user_progress::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[7]->id,
            $resultcourse[3]->id,
            $goalrecord1->id,
            50
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            50,
            $goalrecord1->id,
            $result
        );

        // Update learning goal 2 progess to 100.
        $result = update_user_progress::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[7]->id,
            $resultcourse[3]->id,
            $goalrecord2->id,
            100
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_user_progress::execute_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            100,
            $goalrecord2->id,
            $result
        );
    }
}
