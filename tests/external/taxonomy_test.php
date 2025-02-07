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
 * Unit tests for the get_taxonomy_as_json function.
 *
 * @package    mod_learninggoalwidget
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
final class taxonomy_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing class taxonomy
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy
     */
    public function test_emptytaxonomy(): void {
        $res = $this->setup_widget();

        $emptytaxonomy = new \stdClass;
        $emptytaxonomy->name = "name";
        $emptytaxonomy->children = [];
        $jsonemptytaxonomy = json_encode($emptytaxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        $json = taxonomy::get_taxonomy_as_json($res->instance->id);
        $this->assertNotNull($json);
        $this->assertNotEmpty($json);
        $this->assertEquals($jsonemptytaxonomy, $json);
    }
}
