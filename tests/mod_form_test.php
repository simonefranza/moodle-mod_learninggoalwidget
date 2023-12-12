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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/mod_form.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use core_external\external_api;

/**
 * Learning Goal Taxonomy Mod Form Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class mod_form_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing class mod_learninggoalwidget_mod_form
     *
     * @return void
     */
    public function test_definition() {
        global $COURSE;
        $this->setUp();

        $course = $this->getDataGenerator()->create_course();
        $COURSE->id = $course->id;

        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course->id, true);

        $data = new \stdClass();
        $data->instance = $widgetinstance->id;
        $moodleform = new mod_learninggoalwidget_mod_form($data, $coursemodule->sectionnum, $coursemodule, $course);
        $this->assertDebuggingNotCalled();
    }
}
