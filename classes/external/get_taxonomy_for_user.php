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

use mod_learninggoalwidget\local\userTaxonomy;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class for the external service get_taxonomy_for_user.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_taxonomy_for_user extends \core_external\external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                'userid' => new external_value(PARAM_INT, 'ID of the logged in user'),
                'coursemoduleid' => new external_value(PARAM_INT, ''),
                'instanceid' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Taxonomy for user in JSON format');
    }

    /**
     * Get taxonomy as JSON for a user
     *
     * @param [type] $courseid
     * @param [type] $userid
     * @param [type] $coursemoduleid
     * @param [type] $instanceid
     * @return void
     */
    public static function execute($courseid, $userid, $coursemoduleid, $instanceid) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'userid' => $userid,
                'coursemoduleid' => $coursemoduleid,
                'instanceid' => $instanceid,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        return (new userTaxonomy($coursemoduleid, $courseid, null, $instanceid, $userid))->get_taxonomy_as_json();
    }
}
