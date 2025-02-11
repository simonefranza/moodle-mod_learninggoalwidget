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
 * Structure step of the restore process
 *
 * @package   mod_learninggoalwidget
 * @category  backup
 * @copyright 2024 onwards Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restores steps that will be used by the
 * restore_learninggoalwidget_activity_task
 */

/**
 * Structure step to restore one learninggoalwidget activity
 */
class restore_learninggoalwidget_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore process
     */
    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('learninggoalwidget', '/activity/learninggoalwidget');
        $paths[] = new restore_path_element('topic', '/activity/learninggoalwidget/topics/topic');
        $paths[] = new restore_path_element('goal', '/activity/learninggoalwidget/topics/topic/goals/goal');
        if ($userinfo) {
            $paths[] = new restore_path_element(
                'userprogress',
                '/activity/learninggoalwidget/topics/topic/goals/goal/userprogresses/userprogress'
            );
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a learninggoalwidget element
     *
     * @param object $data
     */
    protected function process_learninggoalwidget($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the learninggoalwidget record.
        $newitemid = $DB->insert_record('learninggoalwidget', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a topic element
     *
     * @param object $data
     */
    protected function process_topic($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->learninggoalwidgetid = $this->get_new_parentid('learninggoalwidget');

        $newitemid = $DB->insert_record('learninggoalwidget_topics', $data);
        $this->set_mapping('topic', $oldid, $newitemid);
    }

    /**
     * Process a goal element
     *
     * @param object $data
     */
    protected function process_goal($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->learninggoalwidgetid = $this->get_new_parentid('learninggoalwidget');
        $data->topicid = $this->get_new_parentid('topic');

        $newitemid = $DB->insert_record('learninggoalwidget_goals', $data);
        $this->set_mapping('goal', $oldid, $newitemid);
    }

    /**
     * Process a userprogress element
     *
     * @param object $data
     */
    protected function process_userprogress($data) {
        global $DB;

        $data = (object)$data;

        $data->learninggoalwidgetid = $this->get_new_parentid('learninggoalwidget');
        $data->topicid = $this->get_new_parentid('topic');
        $data->goalid = $this->get_new_parentid('goal');

        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('learninggoalwidget_progs', $data);
        // No need to save this mapping as far as nothing depend on it.
    }

    /**
     * Function to execute after restore is complete
     */
    protected function after_execute() {
        // No related files.
    }
}
