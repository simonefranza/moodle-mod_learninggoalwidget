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
    global $DB, $COURSE;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->name = $data->name;
    $data->course = $data->course;
    $data->id = $DB->insert_record('learninggoalwidget', $data);
    $sql = "SELECT 1
              FROM {learninggoalwidget_i_topics}
             WHERE course = :courseid
               AND coursemodule = -1
               AND instance = -1";

    $updateparams = [
        'coursemodule' => $data->coursemodule,
        'instance' => $data->id,
        'course'=> $COURSE->id,
    ];
    $courseparams = ['courseid' => $COURSE->id];

    if ($DB->record_exists_sql($sql, $courseparams)) {
        $sql = "UPDATE {learninggoalwidget_i_topics}
                   SET coursemodule = :coursemodule, instance = :instance
                 WHERE course = :course
                   AND coursemodule = -1
                   AND instance = -1";
        $DB->execute($sql, $updateparams);
    }

    $sql = "SELECT 1
              FROM {learninggoalwidget_i_goals}
             WHERE course = :course
               AND coursemodule = -1
               AND instance = -1";

    if ($DB->record_exists_sql($sql, $courseparams)) {
        $sql = "UPDATE {learninggoalwidget_i_goals}
                   SET coursemodule = :coursemodule, instance = :instance
                 WHERE course = :course
                   AND coursemodule = -1
                   AND instance = -1";
        $DB->execute($sql, $updateparams);
    }
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
    $data->name = $data->name;
    $data->intro = $data->intro;

    return $DB->update_record('learninggoalwidget', $data);
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
