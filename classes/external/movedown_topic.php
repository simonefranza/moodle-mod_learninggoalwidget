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
 * Class for the external service movedown_topic.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\external;
use mod_learninggoalwidget\local\topic;

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
     * Move a topic behind the succeeding one (increase ranking)
     *
     * @param number $topicid
     * @return void
     */
    public static function execute($topicid) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::execute_parameters(),
            [
                'topicid' => $topicid,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        $topicmovedown = topic::get_db_entry_by_id($topicid);

        // Find highest rank.
        $sqlstmt = "SELECT MAX(ranking) as maxranking
                      FROM {learninggoalwidget_topics}
                     WHERE learninggoalwidgetid = :instance";

        $params = [
            'instance' => $topicmovedown->learninggoalwidgetid,
        ];

        $recordresult = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        if (!$recordresult || $topicmovedown->ranking === $recordresult->maxranking) {
            // No need to update, as it's already highest rank.
            return get_taxonomy::execute($topicmovedown->learninggoalwidgetid);
        }
        $topicmoveup = topic::get_db_entry_by_ranking($topicmovedown->learninggoalwidgetid, $topicmovedown->ranking + 1);

        $topicmovedown->ranking++;
        $topicmoveup->ranking--;
        $DB->update_record('learninggoalwidget_topics', $topicmovedown);
        $DB->update_record('learninggoalwidget_topics', $topicmoveup);

        return get_taxonomy::execute($topicmovedown->learninggoalwidgetid);
    }
}
