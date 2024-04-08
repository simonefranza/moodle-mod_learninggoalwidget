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
 * Unit tests for the delete_topic function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class delete_topic_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test delete_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\delete_topic::execute
     * @covers \mod_learninggoalwidget\external\delete_topic::execute_returns
     * @covers \mod_learninggoalwidget\external\delete_topic::execute_parameters
     */
    public function test_delete_topic() : void {
        global $DB;
        $this->setUp();

        $result1 = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // Delete topic.
        $result = delete_topic::execute(
            $result1[0]->id,
            $result1[1]->id,
            $result1[2]->id,
            $result1[3]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(delete_topic::execute_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $this->assertNotNull($parsed);

        $this->assertNotNull($parsed->name);
        $this->assertNotEmpty($parsed->name);
        $this->assertEquals("Learning Goal's taxonomy", $parsed->name);

        $this->assertNotNull($parsed->children);
        $this->assertIsArray($parsed->children);
        $this->assertEquals(0, count($parsed->children));

        $this->assertFalse($DB->record_exists('learninggoalwidget_topic', ['id' => $result1[3]->id]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_i_topics', ['id' => $result1[4]->id]));
    }
}
