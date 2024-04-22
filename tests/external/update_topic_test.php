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
 * Unit tests for the update_topic function.
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
 * Unit tests for the update_topic function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class update_topic_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test update_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\update_topic::execute
     * @covers \mod_learninggoalwidget\external\update_topic::execute_returns
     * @covers \mod_learninggoalwidget\external\update_topic::execute_parameters
     */
    public function test_update_topic(): void {
        $result1 = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // Update topic.
        $result = update_topic::execute(
            $result1[0]->id,
            $result1[1]->id,
            $result1[2]->id,
            $result1[3]->id,
            "Updated Name",
            "Updated Shortname",
            "http://updated.at"
        );

        $this->check_updatetopic(
            "Updated Name",
            "Updated Shortname",
            "http://updated.at",
            $result
        );

        $goals = $this->check_updatetopic_getgoals($result);
        $result = external_api::clean_returnvalue(update_topic::execute_returns(), $result);
        $topic = json_decode($result)->children[0];
        $this->assertEquals([], $topic[5]);
    }
}
