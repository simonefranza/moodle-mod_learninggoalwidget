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
//
namespace mod_learninggoalwidget\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use mod_learninggoalwidget\local\taxonomy;
use external_api;
use externallib_advanced_testcase;

/**
 * Learning Goal Taxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @category  external
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class taxonomy_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing class taxonomy
     *
     * @return void
     */
    public function test_emptytaxonomy() {
        $this->setUp();

        $course1 = $this->getDataGenerator()->create_course();
        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course1->id);

        $emptytaxonomy = new \stdClass;
        $emptytaxonomy->name = "Learning Goal's Taxonomy";
        $emptytaxonomy->children = [];
        $jsonemptytaxonomy = json_encode($emptytaxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        $taxonomy = new taxonomy($coursemodule->id, $course1->id, $coursemodule->section, $widgetinstance->id);
        $this->assertNotNull($taxonomy);
        $this->assertNotNull($taxonomy->get_taxonomy_as_json());
        $this->assertNotEmpty($taxonomy->get_taxonomy_as_json());
        $this->assertEquals($jsonemptytaxonomy, $taxonomy->get_taxonomy_as_json());
    }
}
