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
use mod_learninggoalwidget\local\goal;

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
     * @param number $goalid
     * @return string
     */
    public static function execute($goalid) {
        global $USER, $DB;

        self::validate_parameters(
            self::execute_parameters(),
            [
                'goalid' => $goalid,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $goalmoveup = goal::get_db_entry_by_id($goalid);

        if ($goalmoveup->ranking == '1') {
            // No need to update, as it's already lowest rank.
            return get_taxonomy::execute($goalmoveup->learninggoalwidgetid);
        }
        $goalmovedown = goal::get_db_entry_by_ranking(
            $goalmoveup->learninggoalwidgetid,
            $goalmoveup->topicid,
            $goalmoveup->ranking - 1
        );

        $goalmoveup->ranking--;
        $goalmovedown->ranking++;
        $DB->update_record('learninggoalwidget_goals', $goalmoveup);
        $DB->update_record('learninggoalwidget_goals', $goalmovedown);

        return get_taxonomy::execute($goalmoveup->learninggoalwidgetid);
    }
}
