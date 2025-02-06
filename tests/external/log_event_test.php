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
 * Unit tests for the log_event function.
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
 * Unit tests for the log_event function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class log_event_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test log_event
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\log_event::execute
     * @covers \mod_learninggoalwidget\external\log_event::execute_returns
     * @covers \mod_learninggoalwidget\external\log_event::execute_parameters
     */
    public function test_log_event(): void {
        global $DB;
        $this->setUp();
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
        get_log_manager(true);
        $res = $this->setup_course_and_insert_goals();
        $widgetinstance = $res->instance;
        $topicrecord = $res->topic1;
        $user1 = $res->user;
        $goalrecord = $res->goal;

        $progress = 50;
        $timestamp = 12345678;

        update_user_progress::execute(
            $widgetinstance->id,
            $user1->id,
            $topicrecord->id,
            $goalrecord->id,
            $progress,
        );

        $eventparams = [];
        $eventname = "\\mod_learninggoalwidget\\event\\learninggoal_updated";
        $eventparams[3] = ["name" => "instanceid", "value" => $widgetinstance->id];
        $eventparams[4] = ["name" => "userid", "value" => $user1->id];
        $eventparams[5] = ["name" => "timestamp", "value" => $timestamp];
        $eventparams[6] = ["name" => "goalname", "value" => $goalrecord->title];
        $eventparams[7] = ["name" => "goalprogress", "value" => $progress];

        // Update learning goal 2 progess to 50.
        $result = log_event::execute(
            $widgetinstance->id,
            $user1->id,
            $eventparams
        );

        $sqlstmt = 'SELECT id, eventname, other, userid
                      FROM {logstore_standard_log}
                     WHERE eventname = :eventname
                       AND userid = :userid';
        $params = [
            'eventname' => $eventname,
            'userid' => $user1->id,
        ];
        $res = $DB->get_record_sql($sqlstmt, $params);
        $this->assertTrue($res !== false);
        $otherdata = json_decode($res->other);
        $output = new \stdClass;
        foreach (get_object_vars($otherdata) as $value) {
            $output->{$value->name} = $value->value;
        }
        $this->assertTrue($output->instanceid == $widgetinstance->id);
        $this->assertTrue($output->userid == $user1->id);
        $this->assertTrue($output->timestamp == $timestamp);
        $this->assertTrue($output->goalname == $goalrecord->title);
        $this->assertTrue($output->goalprogress == $progress);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(log_event::execute_returns(), $result);
    }
}
