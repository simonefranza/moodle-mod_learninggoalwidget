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
 * Unit tests for the get_taxonomy_for_user function.
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
 * Unit tests for the get_taxonomy_for_user function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class get_taxonomy_for_user_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test get_taxonomy_for_user
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\get_taxonomy_for_user::execute
     * @covers \mod_learninggoalwidget\external\get_taxonomy_for_user::execute_returns
     * @covers \mod_learninggoalwidget\external\get_taxonomy_for_user::execute_parameters
     */
    public function test_get_taxonomy_for_user(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $userid = $res->user->id;
        $this->insert_two_goals($lgwid);

        // Get taxonomy with user progress values.
        $result = get_taxonomy_for_user::execute($lgwid, $userid);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(get_taxonomy_for_user::execute_returns(), $result);

        for ($i = 0; $i < 2; $i++) {
            $this->check_topic($taxonomy->children[$i], $i, $i + 1, 2, true);
        }
        $this->assertSame($taxonomy->children[0]->children[0], 0);
        $this->assertSame($taxonomy->children[0]->children[1], 0);
        $this->assertSame($taxonomy->children[1]->children[0], 0);
        $this->assertSame($taxonomy->children[1]->children[1], 0);
    }
}
