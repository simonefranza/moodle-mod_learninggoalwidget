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
     * @param number $instance
     * @return string
     */
    public static function execute($instance) {
        global $USER, $DB;

        self::validate_parameters(
            self::execute_parameters(),
            [
                'instance' => $instance,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $params = [
            'learninggoalwidgetid' => $instance,
        ];

        $DB->delete_records('learninggoalwidget_progs', $params);
        $DB->delete_records('learninggoalwidget_goals', $params);
        $DB->delete_records('learninggoalwidget_topics', $params);
        $DB->delete_records('learninggoalwidget', ['id' => $instance]);
        return get_taxonomy::execute($instance);
    }
}
