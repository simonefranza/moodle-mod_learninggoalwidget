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
 * Class for the external service update_user_progress.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_user_progress extends \core_external\external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemoduleid' => new external_value(PARAM_INT, 'ID of the course module'),
                'instanceid' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'userid' => new external_value(PARAM_INT, 'ID of the user'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
                'progress' => new external_value(PARAM_INT, 'Progress value for the learning goal'),
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Taxonomy in JSON format with the updated progress.');
    }

    /**
     * Updates the progress of a learning goal for the chosen user
     *
     * @param [int] $courseid
     * @param [int] $coursemoduleid
     * @param [int] $instanceid
     * @param [int] $userid
     * @param [int] $topicid
     * @param [int] $goalid
     * @param [int] $progress
     * @return [string] taxonomy
     */
    public static function execute($courseid, $coursemoduleid, $instanceid, $userid, $topicid, $goalid, $progress) {
        global $USER, $DB;

        self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'instanceid' => $instanceid,
                'userid' => $userid,
                'topicid' => $topicid,
                'goalid' => $goalid,
                'progress' => $progress,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $sqlstmt = "SELECT id FROM {learninggoalwidget_i_userpro}
        WHERE course = ? AND coursemodule = ? AND instance = ? AND user = ? AND topic = ? AND goal = ?";
        $params = [$courseid, $coursemoduleid, $instanceid, $userid, $topicid, $goalid];
        $userprogressrecord = $DB->get_record_sql($sqlstmt, $params);
        if ($userprogressrecord) {
            $userprogress = new \stdClass;
            $userprogress->id = $userprogressrecord->id;
            $userprogress->progress = $progress;
            $DB->update_record('learninggoalwidget_i_userpro', $userprogress);
        } else {
            $userprogress = new \stdClass;
            $userprogress->course = $courseid;
            $userprogress->coursemodule = $coursemoduleid;
            $userprogress->instance = $instanceid;
            $userprogress->topic = $topicid;
            $userprogress->goal = $goalid;
            $userprogress->user = $userid;
            $userprogress->progress = $progress;
            $DB->insert_record('learninggoalwidget_i_userpro', $userprogress);
        }

        return get_taxonomy_for_user::execute($courseid, $userid, $coursemoduleid, $instanceid);
    }
}
