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
 * Class for the external service delete_topic.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
     * Delete a topic (including related goals and progress) from the taxonomy
     *
     * @param number $topicid
     * @return void
     */
    public static function execute($topicid) {
        global $USER, $DB;
        self::validate_parameters(
            self::execute_parameters(),
            [
                'topicid' => $topicid,
            ]
        );
        self::validate_context(\context_user::instance($USER->id));

        // Get topic to delete.
        $params = [
            'id' => $topicid,
        ];
        $sqlstmt = "SELECT id, ranking, learninggoalwidgetid
                      FROM {learninggoalwidget_topics}
                     WHERE id = :id";
        $todelete = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $instance = $todelete->learninggoalwidgetid;

        // Get topics to update (need to lower the rankings).
        $params = [
            'learninggoalwidgetid' => $instance,
            'ranking' => $todelete->ranking,
        ];
        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_topics}
                     WHERE learninggoalwidgetid = :learninggoalwidgetid
                       AND ranking > :ranking";
        $toupdatetopics = $DB->get_records_sql($sqlstmt, $params);

        // Delete related infos (progresses, goals).
        $params = [
            'learninggoalwidgetid' => $instance,
            'topicid' => $topicid,
        ];

        $DB->delete_records('learninggoalwidget_progs', $params);
        $DB->delete_records('learninggoalwidget_goals', $params);

        $params = [
            'learninggoalwidgetid' => $instance,
            'id' => $topicid,
        ];
        $DB->delete_records('learninggoalwidget_topics', $params);

        // Update other topics.
        foreach ($toupdatetopics as $toupdate) {
            $toupdate->ranking--;
            $DB->update_record('learninggoalwidget_topics', $toupdate);
        }

        return get_taxonomy::execute($instance);
    }
}
