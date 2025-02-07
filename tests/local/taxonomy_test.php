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
 * Learning Goal Taxonomy - Taxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\topic;
use mod_learninggoalwidget\local\goal;

/**
 * Learning Goal Taxonomy Goal Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class taxonomy_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing method taxonomy::sort_by_ranking
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy::sort_by_ranking
     */
    public function test_sort_by_ranking(): void {
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $obj = new \stdClass();
            $obj->ranking = (rand(0, 1) === 0) ? -1 : rand(1, 100);
            $data[] = $obj;
        }
        taxonomy::sort_by_ranking($data);
        $currentvalue = -1;
        // Check that rankings are sorted ascending.
        foreach ($data as $el) {
            $this->assertTrue($el->ranking >= $currentvalue);
            $currentvalue = $el->ranking;
        }
    }

    /**
     * testing method taxonomy::reassign_rankings
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy::reassign_rankings
     */
    public function test_reassign_rankings(): void {
        $data = [];
        for ($i = 0; $i < 101; $i++) {
            $obj = new \stdClass();
            $obj->ranking = rand(1, 100);
            $data[] = $obj;
        }
        $data[50]->ranking = -1;
        taxonomy::sort_by_ranking($data);
        $this->assertSame($data[0]->ranking, -1);
        taxonomy::reassign_rankings($data);
        // Check that rankings are reassigned from 1 to 100.
        for ($i = 1; $i < 101; $i++) {
            $this->assertSame($data[$i]->ranking, $i);
        }
    }

    /**
     * testing method taxonomy::get_taxonomy_as_json
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy::get_taxonomy_as_json
     * @covers \mod_learninggoalwidget\local\taxonomy::get_topics
     */
    public function test_get_taxonomy_as_json(): void {
        $this->assertSame(taxonomy::get_taxonomy_as_json(-1), "{}");

        // Create instance
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(isset($taxonomy->name) && is_string($taxonomy->name));
        $this->assertSame($taxonomy->name, "name");
        $this->assertTrue(isset($taxonomy->children) && is_array($taxonomy->children));
        $this->assertTrue(count($taxonomy->children) == 0);
    }
}
