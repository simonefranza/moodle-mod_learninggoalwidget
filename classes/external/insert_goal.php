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
 * Class for the external service insert_goal.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class for the external service insert_goal.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class insert_goal extends \core_external\external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalname' => new external_value(PARAM_TEXT, 'goal name'),
                'goalshortname' => new external_value(PARAM_TEXT, 'goal shortname'),
                'goalurl' => new external_value(PARAM_TEXT, 'goal url'),
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_INT, 'ID of the inserted goal');
    }

    /**
     * Insert a new goal in the goal table
     *
     * @param  [int] $course
     * @param  [int] $coursemodule
     * @param  [int] $instance
     * @param  [int] $topicid
     * @param  [string] $goalname
     * @param  [string] $goalshortname
     * @param  [string] $goalurl
     * @return int goal_id
     */
    public static function execute($course, $coursemodule, $instance, $topicid, $goalname, $goalshortname, $goalurl) {
        global $USER;
        global $DB;

        // Parameter validation.
        self::validate_parameters(
            self::execute_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalname' => $goalname,
                'goalshortname' => $goalshortname,
                'goalurl' => $goalurl,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        // Insert in goal table.
        $goalrecord = new \stdClass;
        $goalrecord->title = $goalname;
        $goalrecord->shortname = $goalshortname;
        $goalrecord->url = $goalurl;
        $goalrecord->topic = $topicid;
        $goalrecord->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord);

        // Link goal with learning goal activity in a course.
        $goalinstancerecord = new \stdClass;
        $goalinstancerecord->course = $course;
        $goalinstancerecord->coursemodule = $coursemodule;
        $goalinstancerecord->instance = $instance;
        $goalinstancerecord->topic = $topicid;
        $goalinstancerecord->goal = $goalrecord->id;
        $goalinstancerecord->ranking = 1;
        $sqlstmt = "SELECT MAX(ranking) as maxranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
        ];
        $goalcountrecord = $DB->get_record_sql($sqlstmt, $params);
        if ($goalcountrecord) {
            $goalinstancerecord->ranking = $goalcountrecord->maxranking + 1;
        }
        $goalinstancerecord->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord);

        return $goalinstancerecord->id;
    }
}
