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
 * Learning Goal Taxonomy Mod Form Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
final class mod_form_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing class mod_learninggoalwidget_mod_form
     * @return void
     */
    public function test_definition(): void {
        global $COURSE;
        $res = $this->setup_widget();
        $course = $res->course;
        $lgwid = $res->instance->id;

        $COURSE->id = $course->id;

        $cm = get_coursemodule_from_instance('learninggoalwidget', $lgwid, $course->id, true);

        $data = new \stdClass();
        $data->instance = $lgwid;
        new mod_learninggoalwidget_mod_form($data, $cm->sectionnum, $cm, $course);
        $this->assertDebuggingNotCalled();
    }
}
