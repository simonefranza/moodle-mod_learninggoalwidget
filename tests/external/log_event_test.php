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
 * Unit tests for the log_event function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class log_event_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test log_event
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\log_event::execute
     * @covers \mod_learninggoalwidget\external\log_event::execute_returns
     * @covers \mod_learninggoalwidget\external\log_event::execute_parameters
     */
    public function test_log_event() : void {
        global $DB;
        $this->setUp();
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
        get_log_manager(true);
        $res = $this->setup_course_and_insert_goals();
        $coursedata = $res[0];
        $course1 = $coursedata[0];
        $coursemodule = $coursedata[1];
        $widgetinstance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user1 = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);
        $coursecontext = \context_course::instance($course1->id);
        $progress = 50;
        $timestamp = 12345678;

        update_user_progress::execute(
            $course1->id,
            $coursemodule->id,
            $widgetinstance->id,
            $user1->id,
            $topicrecord->id,
            $goalrecord->id,
            $progress,
        );

        $eventparams = [];
        $eventname = "\\mod_learninggoalwidget\\event\\learninggoal_updated";
        $eventparams[1] = ["name" => "courseid", "value" => $course1->id];
        $eventparams[2] = ["name" => "coursemoduleid", "value" => $coursemodule->id];
        $eventparams[3] = ["name" => "instanceid", "value" => $widgetinstance->id];
        $eventparams[4] = ["name" => "userid", "value" => $user1->id];
        $eventparams[5] = ["name" => "timestamp", "value" => $timestamp];
        $eventparams[6] = ["name" => "goalname", "value" => $goalrecord->title];
        $eventparams[7] = ["name" => "goalprogress", "value" => $progress];

        // Update learning goal 2 progess to 50.
        $result = log_event::execute(
            $course1->id,
            $coursemodule->id,
            $widgetinstance->id,
            $user1->id,
            $eventparams
        );

        $sqlstmt = 'SELECT id, eventname, other, userid FROM {logstore_standard_log} WHERE eventname = ? AND userid = ?';
        $res = $DB->get_record_sql($sqlstmt, [$eventname, $user1->id]);
        $this->assertTrue($res !== false);
        $otherdata = json_decode($res->other);
        $output = new \stdClass;
        foreach ($otherdata as $key => $value) {
            $output->{$value->name} = $value->value;
        }
        $this->assertTrue($output->courseid == $course1->id);
        $this->assertTrue($output->coursemoduleid == $coursemodule->id);
        $this->assertTrue($output->instanceid == $widgetinstance->id);
        $this->assertTrue($output->userid == $user1->id);
        $this->assertTrue($output->timestamp == $timestamp);
        $this->assertTrue($output->goalname == $goalrecord->title);
        $this->assertTrue($output->goalprogress == $progress);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(log_event::execute_returns(), $result);
    }
}
