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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

use mod_learninggoalwidget\local\taxonomy;

/**
 * Learning goal widget form
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_learninggoalwidget_mod_form extends moodleform_mod {

    /**
     * setup the form
     *
     * @return void
     */
    public function definition(): void {
        global $PAGE;

        $PAGE->force_settings_menu();

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header',
          get_string('settings:header', 'mod_learninggoalwidget'),
          get_string('settings:header', 'mod_learninggoalwidget'));

        // Load topics and goals.
        $taxonomy = new taxonomy(($this->_cm) ? $this->_cm->id : null,
            ($this->_course != null) ? $this->_course->id : null,
            $this->_section,
            $this->_instance,
        );

        $jsontaxonomy = addslashes($taxonomy->get_taxonomy_as_json());

        $widgetrenderer = $PAGE->get_renderer('mod_learninggoalwidget', 'widget');
        $templatecontext = [
            'topicheader' => get_string('settings:topicheader', 'mod_learninggoalwidget'),
            'goalheader' => get_string('settings:topicheader', 'mod_learninggoalwidget'),
            'btnnewtopic' => get_string('settings:btnnewtopic', 'mod_learninggoalwidget'),
            'btnnewgoal' => get_string('settings:btnnewgoal', 'mod_learninggoalwidget'),
            'jsonheader' => get_string('settings:jsonheader', 'mod_learninggoalwidget'),
            'btnjsonupload' => get_string('settings:btnjsonupload', 'mod_learninggoalwidget'),
            'btnjsondownload' => get_string('settings:btnjsondownload', 'mod_learninggoalwidget'),
            'course' => $this->_course->id,
            'coursemodule' => ($this->_cm !== null) ? $this->_cm->id : -1,
            'instance' => ($this->_instance !== null && $this->_instance !== "") ? $this->_instance : -1,
            'taxonomy' => $jsontaxonomy,
            'notopicsmessage' => get_string('settings:notopicsmessage', 'mod_learninggoalwidget')
        ];
        $learninggoalsettings = $widgetrenderer->render_from_template(
            'mod_learninggoalwidget/editor/form_settings',
            $templatecontext
        );
        $mform->addElement('html', $learninggoalsettings);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons(true, false, null);
    }

    /**
     * Dummy stub method - override if you needed to perform some extra validation.
     * If there are errors return array of errors ("fieldname"=>"error message"),
     * otherwise true if ok.
     *
     * Server side rules do not work for uploaded files, implement serverside rules here if needed.
     *
     * @param  array $data  array of ("fieldname"=>value) of submitted data
     * @param  array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
