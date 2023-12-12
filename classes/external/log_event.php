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
use mod_learninggoalwidget\event\learninggoal_updated;

/**
 * Class for the external service log_event.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_event extends \core_external\external_api {
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
                'eventparams' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            "name" => new external_value(PARAM_TEXT, 'name'),
                            "value" => new external_value(PARAM_TEXT, 'keyword'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_INT, 'True if storing the event succeeded');
    }

    /**
     * Save an event in the moodle logstore
     *
     * @param [int] $courseid
     * @param [int] $coursemoduleid
     * @param [int] $instanceid
     * @param [int] $userid
     * @param [int] $eventparams
     * @return bool
     */
    public static function execute($courseid, $coursemoduleid, $instanceid, $userid, $eventparams) {
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'instanceid' => $instanceid,
                'userid' => $userid,
                'eventparams' => $eventparams,
            ]
        );

        self::validate_context(\context_user::instance($userid));

        $usercontext = \context_user::instance($userid);

        // Left out 'courseid' => $courseid, because it was causing problems.
        $params = [
            'contextid' => $usercontext->id,
            'relateduserid' => $userid,
            'other' => $eventparams,
            'userid' => $userid,
        ];

        $event = learninggoal_updated::create($params);
        $event->trigger();

        return true;
    }
}
