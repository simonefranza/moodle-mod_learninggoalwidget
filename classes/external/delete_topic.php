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
 * Class for the external service delete_topic.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_topic extends \core_external\external_api {
    /**
     * Returns description of method parameters for the delete_topic function
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course for the delete_topic function'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module for the delete_topic function'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance for the delete_topic function'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic for the delete_topic function'),
            ]
        );
    }

    /**
     * Returns description of return values for the delete_topic function
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Taxonomy in JSON format without chosen topic.');
    }

    /**
     * Delete a topic from the topic table
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @return void
     */
    public static function execute($course, $coursemodule, $instance, $topicid) {
        global $USER, $DB;
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

        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topic' => $topicid,
        ];
        $DB->delete_records('learninggoalwidget_i_userpro', $params);
        $DB->delete_records('learninggoalwidget_i_goals', $params);
        $DB->delete_records('learninggoalwidget_i_topics', $params);
        $DB->delete_records('learninggoalwidget_goal', ['topic' => $topicid]);

        $DB->delete_records('learninggoalwidget_topic', ['id' => $topicid]);

        return get_taxonomy::execute($course, $coursemodule, $instance);
    }
}
