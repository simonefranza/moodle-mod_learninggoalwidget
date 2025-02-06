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
 * Unit tests for the delete_topic function.
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
 * Unit tests for the delete_topic function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class delete_topic_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test delete_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\delete_topic::execute
     * @covers \mod_learninggoalwidget\external\delete_topic::execute_returns
     * @covers \mod_learninggoalwidget\external\delete_topic::execute_parameters
     */
    public function test_delete_topic(): void {
        global $DB;
        $this->setUp();

        $result = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // Delete topic.
        $deleted = delete_topic::execute($result->topic->id);

        // We need to execute the return values cleaning process to simulate the web service server.
        $deleted = external_api::clean_returnvalue(delete_topic::execute_returns(), $deleted);

        $this->assertNotNull($deleted);
        $this->assertNotEmpty($deleted);
        $parsed = json_decode($deleted);

        $this->assertNotNull($parsed);

        $this->assertNotNull($parsed->name);
        $this->assertNotEmpty($parsed->name);
        $this->assertEquals("Learning Goal's taxonomy", $parsed->name);

        $this->assertNotNull($parsed->children);
        $this->assertIsArray($parsed->children);
        $this->assertEquals(0, count($parsed->children));

        $this->assertFalse($DB->record_exists('learninggoalwidget_topics', ['id' => $result->topic->id]));
    }
}
