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
 * Unit tests for the moveup_topic function.
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
 * Unit tests for the moveup_topic function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class moveup_topic_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test moveup_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\moveup_topic::execute
     * @covers \mod_learninggoalwidget\external\moveup_topic::execute_returns
     * @covers \mod_learninggoalwidget\external\moveup_topic::execute_parameters
     */
    public function test_moveup_topic() : void {
        $this->setUp();
        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Move topic 2 up.
        $result = moveup_topic::execute(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[4]->id
        );

        $result = external_api::clean_returnvalue(moveup_topic::execute_returns(), $result);

        $this->check_course_with_topics($result, $resultcourse[3], $resultcourse[4]);
    }
}
