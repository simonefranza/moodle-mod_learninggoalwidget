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
 * Unit tests for the insert_topic function.
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
 * Unit tests for the insert_topic function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class insert_topic_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test insert_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\insert_topic::execute
     * @covers \mod_learninggoalwidget\external\insert_topic::execute_returns
     * @covers \mod_learninggoalwidget\external\insert_topic::execute_parameters
     */
    public function test_insert_topic() : void {
        $this->setUp();

        $course1 = $this->getDataGenerator()->create_course();
        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course1->id);

        $result = insert_topic::execute(
            $course1->id,
            $coursemodule->id,
            $widgetinstance->id,
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        $result = external_api::clean_returnvalue(insert_topic::execute_returns(), $result);

        $result = get_taxonomy::execute(
            $course1->id,
            $coursemodule->id,
            $widgetinstance->id,
        );

        $result = external_api::clean_returnvalue(get_taxonomy::execute_returns(), $result);

        $resulttopic = $this->check_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at",
            $result
        );

        $this->assertEquals([], $resulttopic[0]);
    }
}
