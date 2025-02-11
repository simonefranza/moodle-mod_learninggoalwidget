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
 * Library of interface functions and constants.
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/learninggoalwidget/classes/local/taxonomy.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/classes/local/topic.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/classes/local/goal.php');
use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\topic;
use mod_learninggoalwidget\local\goal;

/**
 * This function is necessary for the plugin to be classified in the right
 * activity category
 *
 * @param  string $feature feature
 */
function learninggoalwidget_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        // Enable MOODLE2 backup.
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_learninggoalwidget into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param  stdClass $data An object from the form.
 * @return int The id of the newly inserted record.
 */
function learninggoalwidget_add_instance(stdClass $data): int {
    global $DB;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->id = $DB->insert_record('learninggoalwidget', $data);
    $taxonomy = json_decode($data->taxonomy);

    taxonomy::update_taxonomy($data->id, $taxonomy);

    return $data->id;
}

/**
 * Updates an instance of the mod_learninggoalwidget in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param  stdClass                        $data  An object from the form in mod_form.php.
 * @return bool True if successful, false otherwise.
 */
function learninggoalwidget_update_instance(stdClass $data): bool {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    if (!$DB->update_record('learninggoalwidget', $data)) {
        return false;
    }

    $taxonomy = json_decode($data->taxonomy);

    taxonomy::update_taxonomy($data->id, $taxonomy);

    return true;
}

/**
 * Removes an instance of the mod_learninggoalwidget from the database.
 *
 * @param  int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function learninggoalwidget_delete_instance(int $id): bool {
    global $DB;

    $activity = $DB->get_record('learninggoalwidget', ['id' => $id]);
    if (!$activity) {
        return false;
    }

    $DB->delete_records('learninggoalwidget', ['id' => $id]);
    $DB->delete_records('learninggoalwidget_topics', ['learninggoalwidgetid' => $id]);
    $DB->delete_records('learninggoalwidget_goals', ['learninggoalwidgetid' => $id]);
    $DB->delete_records('learninggoalwidget_progs', ['learninggoalwidgetid' => $id]);

    return true;
}

/**
 * Shows the learning goal widget on the course page.
 *
 * @param cm_info $cm Course-module object
 */
function learninggoalwidget_cm_info_view(cm_info $cm) {
    global $PAGE;

    $canview = has_capability(
        'mod/learninggoalwidget:view',
        context_module::instance($cm->get_course_module_record()->id)
    );

    if ($canview) {

        $widgetrenderable = new \mod_learninggoalwidget\output\widget\widget_renderable(
            $cm->get_modinfo()->get_course_id(),
            $cm->get_modinfo()->get_user_id(),
            $cm->get_course_module_record()->id,
            $cm->get_course_module_record()->instance
        );

        $widgetrenderer = $PAGE->get_renderer('mod_learninggoalwidget', 'widget');

        $cm->set_content($widgetrenderer->render($widgetrenderable), true);
    } else {

        if (isguestuser()) {
            $cm->set_content(get_string('guestaccess', 'mod_learninggoalwidget'), false);
        } else {
            $cm->set_content(get_string('noaccess', 'mod_learninggoalwidget'), false);
        }
    }
}
