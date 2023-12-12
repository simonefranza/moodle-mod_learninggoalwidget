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
 * Class for the external service update_topic.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_topic extends \core_external\external_api {
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
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'topicname' => new external_value(PARAM_TEXT, 'topic name'),
                'topicshortname' => new external_value(PARAM_TEXT, 'topic shortname'),
                'topicurl' => new external_value(PARAM_TEXT, 'topic url'),
            ]
        );
    }

    /**
     * Returns description of return values
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Updated taxonomy in JSON format.');
    }

    /**
     * Update a topic in the topic table
     *
     * @param  int $course
     * @param  int $coursemodule
     * @param  int $instance
     * @param  int $topicid
     * @param  string $topicname
     * @param  string $topicshortname
     * @param  string $topicurl
     * @return void
     */
    public static function execute($course, $coursemodule, $instance, $topicid, $topicname, $topicshortname, $topicurl) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::execute_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'topicname' => $topicname,
                'topicshortname' => $topicshortname,
                'topicurl' => $topicurl,
            ]
        );

        self::validate_context(\context_user::instance($USER->id));

        // Update in topic table.
        $topicrecord = new \stdClass;
        $topicrecord->id = $topicid;
        $topicrecord->title = $topicname;
        $topicrecord->shortname = $topicshortname;
        $topicrecord->url = $topicurl;
        $DB->update_record('learninggoalwidget_topic', $topicrecord);

        return get_taxonomy::execute($course, $coursemodule, $instance);
    }
}
