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
 * Unit tests for the get_taxonomy_for_user function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class get_taxonomy_for_user_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test get_taxonomy_for_user
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\get_taxonomy_for_user::execute
     * @covers \mod_learninggoalwidget\external\get_taxonomy_for_user::execute_returns
     * @covers \mod_learninggoalwidget\external\get_taxonomy_for_user::execute_parameters
     */
    public function test_get_taxonomy_for_user() : void {
        $this->setUp();

        [$resultcourse, $goalrecord1, , $goalrecord2, ] =
            $this->setup_course_and_insert_two_goals();

        // Get taxonomy with user progress values.
        $result = get_taxonomy_for_user::execute(
            $resultcourse[0]->id,
            $resultcourse[7]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(get_taxonomy_for_user::execute_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            0,
            $goalrecord1->id,
            $result
        );
        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            0,
            $goalrecord2->id,
            $result
        );
    }
}
