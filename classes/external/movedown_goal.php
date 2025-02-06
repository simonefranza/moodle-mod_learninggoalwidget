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
 * Class for the external service movedown_goal.
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
 * Class for the external service movedown_goal.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class movedown_goal extends \core_external\external_api {
    /**
     * Returns description of method parameters for the movedown_goal function
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
     * Returns description of return values for the movedown_goal function
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Taxonomy in JSON format with moveddown goal.');
    }

    /**
     * Move a goal behind the succeeding one (increase ranking)
     *
     * @param int $goalid
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

        $goalmovedown = goal::get_db_entry_by_id($goalid);
        // Find highest rank.
        $sqlstmt = "SELECT MAX(ranking) as maxranking
                      FROM {learninggoalwidget_goals}
                     WHERE learninggoalwidgetid = :instance
                       AND topicid = :topicid";

        $params = [
            'instance' => $goalmovedown->learninggoalwidgetid,
            'topicid' => $goalmovedown->topicid,
        ];

        $recordresult = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        if (!$recordresult || $goalmovedown->ranking === $recordresult->maxranking) {
            // No need to update, as it's already highest rank.
            return get_taxonomy::execute($goalmovedown->learninggoalwidgetid);
        }
        $goalmoveup = goal::get_db_entry_by_ranking(
            $goalmovedown->learninggoalwidgetid,
            $goalmovedown->topicid,
            $goalmovedown->ranking + 1);
        $goalmovedown->ranking++;
        $goalmoveup->ranking--;
        $DB->update_record('learninggoalwidget_goals', $goalmovedown);
        $DB->update_record('learninggoalwidget_goals', $goalmoveup);

        return get_taxonomy::execute($goalmovedown->learninggoalwidgetid);
    }
}
