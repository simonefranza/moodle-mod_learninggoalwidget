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

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class for the external service movedown_topic.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class movedown_topic extends \core_external\external_api {
    /**
     * Returns description of method parameters for the movedown_topic function
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course for the movedown_topic function'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module for the movedown_topic function'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance for the movedown_topic function'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic for the movedown_topic function'),
            ]
        );
    }

    /**
     * Returns description of return values for the movedown_topic function
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Taxonomy in JSON format with moveddown topic.');
    }

    /**
     * Move a topic behind the succeeding one (increase rank)
     *
     * @param [int] $course
     * @param [int] $coursemodule
     * @param [int] $instance
     * @param [int] $topicid
     * @return void
     */
    public static function execute($course, $coursemodule, $instance, $topicid) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::execute_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $topicmovedown = new \stdClass;
        $topicmovedown->course = $course;
        $topicmovedown->coursemodule = $coursemodule;
        $topicmovedown->instance = $instance;
        $topicmovedown->topic = $topicid;
        $sqlstmt = "SELECT id, rank FROM {learninggoalwidget_i_topics}
        WHERE course = ? AND coursemodule = ? AND instance = ? AND topic = ?";
        $params = [$course, $coursemodule, $instance, $topicid];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $topicmovedown->id = $topicrecord->id;
        $topicmovedown->rank = $topicrecord->rank;

        $sqlstmt = "SELECT MIN(rank) as rank FROM {learninggoalwidget_i_topics}
        WHERE course = ? AND coursemodule = ? AND instance = ? AND rank > ?";
        $params = [$course, $coursemodule, $instance, $topicrecord->rank];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, rank FROM {learninggoalwidget_i_topics}
        WHERE course = ? AND coursemodule = ? AND instance = ? AND rank = ?";
        $params = [$course, $coursemodule, $instance, $topicrecord->rank];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params);

        $topicmoveup = new \stdClass;
        $topicmoveup->id = $topicrecord->id;
        $topicmoveup->rank = $topicmovedown->rank;

        $topicmovedown->rank = $topicrecord->rank;

        $DB->update_record('learninggoalwidget_i_topics', $topicmoveup);
        $DB->update_record('learninggoalwidget_i_topics', $topicmovedown);

        return get_taxonomy::execute($course, $coursemodule, $instance);
    }
}
