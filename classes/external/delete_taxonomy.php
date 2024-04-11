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
 * Class for the external service delete_taxonomy.
 *
 * @package    mod_learninggoalwidget
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class for the external service delete_taxonomy.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_taxonomy extends \core_external\external_api {
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
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Updated (empty) taxonomy in JSON format.');
    }

    /**
     * Delete the entire taxonomy
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @return string
     */
    public static function execute($course, $coursemodule, $instance) {
        global $USER, $DB;

        self::validate_parameters(
            self::execute_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
        ];

        $sqlstmt = "SELECT topic
                      FROM {learninggoalwidget_i_topics}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance";
        $topicrecords = $DB->get_records_sql($sqlstmt, $params);
        $sqlstmt = "SELECT goal
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance";
        $goalrecords = $DB->get_records_sql($sqlstmt, $params);

        $DB->delete_records('learninggoalwidget_i_userpro', $params);
        $DB->delete_records('learninggoalwidget_i_goals', $params);
        $DB->delete_records('learninggoalwidget_i_topics', $params);
        foreach ($topicrecords as $topicrecord) {
            $DB->delete_records('learninggoalwidget_topic', ['id' => $topicrecord->topic]);
        }
        foreach ($goalrecords as $goalrecord) {
            $DB->delete_records('learninggoalwidget_goal', ['id' => $goalrecord->goal]);
        }

        return get_taxonomy::execute($course, $coursemodule, $instance);
    }
}
