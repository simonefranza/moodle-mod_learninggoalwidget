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
 * Class for the external service moveup_goal.
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
 * Class for the external service moveup_goal.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moveup_goal extends \core_external\external_api {
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
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Updated taxonomy in JSON format with moved goal.');
    }

    /**
     * Move a goal before the preceding one (decrease ranking)
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @param int $goalid
     * @return string
     */
    public static function execute($course, $coursemodule, $instance, $topicid, $goalid) {
        global $USER, $DB;

        self::validate_parameters(
            self::execute_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $goalmoveup = new \stdClass;
        $goalmoveup->course = $course;
        $goalmoveup->coursemodule = $coursemodule;
        $goalmoveup->instance = $instance;
        $goalmoveup->topic = $topicid;
        $goalmoveup->goal = $goalid;
        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND goal = :goalid";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalid' => $goalid,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $goalmoveup->id = $goalrecord->id;
        $goalmoveup->ranking = $goalrecord->ranking;
        $sqlstmt = "SELECT MAX(ranking) as ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND ranking < :goalranking";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalranking' => $goalrecord->ranking,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND ranking = :goalranking";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalranking' => $goalrecord->ranking,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params);

        $goalmovedown = new \stdClass;
        $goalmovedown->id = $goalrecord->id;
        $goalmovedown->ranking = $goalmoveup->ranking;

        $goalmoveup->ranking = $goalrecord->ranking;

        $DB->update_record('learninggoalwidget_i_goals', $goalmoveup);
        $DB->update_record('learninggoalwidget_i_goals', $goalmovedown);

        return get_taxonomy::execute($course, $coursemodule, $instance);
    }
}
