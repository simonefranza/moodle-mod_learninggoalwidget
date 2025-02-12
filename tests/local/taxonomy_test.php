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
        $this->assertSame(taxonomy::get_taxonomy_as_json(null), "{}");
        $this->expectException(dml_missing_record_exception::class);
        taxonomy::get_taxonomy_as_json(-1);

        // Create instance.
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(isset($taxonomy->name) && is_string($taxonomy->name));
        $this->assertSame($taxonomy->name, "name");
        $this->assertTrue(isset($taxonomy->children) && is_array($taxonomy->children));
        $this->assertTrue(count($taxonomy->children) == 0);
        $numtopics = 50;
        $numgoals = 10;
        $topics = $this->create_taxonomy($numtopics, $numgoals);

        foreach ($topics as &$topic) {
            $this->assertTrue(topic::validate_topic($topic));
            $topic->topicid = topic::update_topic($lgwid, $topic);
            $this->assertTrue($topic->topicid > 0);
            foreach ($topic->children as &$goal) {
                $this->assertTrue(goal::validate_goal($goal));
                $goal->goalid = goal::update_goal($lgwid, $topic->topicid, $goal);
                $this->assertTrue($goal->goalid > 0);
            }
        }

        // Check that everything was added correctly.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(isset($taxonomy->name) && is_string($taxonomy->name));
        $this->assertSame($taxonomy->name, "name");
        $this->assertTrue(isset($taxonomy->children) && is_array($taxonomy->children));
        $this->assertTrue(count($taxonomy->children) == $numtopics);
        for ($i = 0; $i < $numtopics; $i++) {
            $topic = $taxonomy->children[$i];
            $this->check_topic($topic, $i, $i + 1, $numgoals, true);
        }
    }

    /**
     * testing basics of method taxonomy::validate_taxonomy
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy::validate_taxonomy
     */
    public function test_basic_validate_taxonomy(): void {
        $taxonomy = new \stdClass;

        // Taxonomy with no children prop.
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertTrue(isset($taxonomy->children) && is_array($taxonomy->children));
        $this->assertTrue(count($taxonomy->children) == 0);

        // Make topic invalid -> has to be removed.
        $taxonomy->children = $this->create_taxonomy(1, 0);
        $this->assertSame(count($taxonomy->children), 1);
        unset($taxonomy->children[0]->name);
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), 0);

        // Make 2 topics and change ranking -> has to reassign rankings.
        $taxonomy->children = $this->create_taxonomy(2, 0);
        $this->assertSame(count($taxonomy->children), 2);
        $taxonomy->children[0]->ranking = 100;
        $taxonomy->children[1]->ranking = 200;
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), 2);
        for ($i = 0; $i < 2; $i++) {
            $this->check_topic($taxonomy->children[$i], $i, $i + 1, 0, true);
        }

        // Make 2 topics and change ranking (change also order) -> has to reassign rankings and sort.
        $taxonomy->children = $this->create_taxonomy(2, 0);
        $this->assertSame(count($taxonomy->children), 2);
        $taxonomy->children[0]->ranking = 50;
        $taxonomy->children[1]->ranking = 25;
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), 2);
        for ($i = 0; $i < 2; $i++) {
            $this->check_topic($taxonomy->children[$i], 1 - $i, $i + 1, 0, true);
        }

        // Create 1 topics w/ 1 goal and remove prop from goal -> has to delete goal.
        $taxonomy->children = $this->create_taxonomy(1, 1);
        $this->assertSame(count($taxonomy->children), 1);
        $this->assertSame(count($taxonomy->children[0]->children), 1);
        unset($taxonomy->children[0]->children[0]->name);
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), 1);
        $this->assertSame(count($taxonomy->children[0]->children), 0);
        $this->check_topic($taxonomy->children[0], 0, 1, 0, true);

        // Create 1 topics w/ 2 goal and change ranking of goal -> has to reassign_rankings.
        $taxonomy->children = $this->create_taxonomy(1, 2);
        $this->assertSame(count($taxonomy->children), 1);
        $this->assertSame(count($taxonomy->children[0]->children), 2);
        $taxonomy->children[0]->children[0]->ranking = 100;
        $taxonomy->children[0]->children[1]->ranking = 200;
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), 1);
        $this->check_topic($taxonomy->children[0], 0, 1, 2, true);

        // Create 1 topics w/ 2 goal and invert ranking of goals -> has to reassign_rankings and re-sort.
        $taxonomy->children = $this->create_taxonomy(1, 2);
        $this->assertSame(count($taxonomy->children), 1);
        $this->assertSame(count($taxonomy->children[0]->children), 2);
        $taxonomy->children[0]->children[0]->ranking = 50;
        $taxonomy->children[0]->children[1]->ranking = 25;
        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), 1);
        $this->check_topic($taxonomy->children[0], 0, 1, 2, false);
        for ($i = 0; $i < 2; $i++) {
            $this->check_goal($taxonomy->children[0]->children[$i], 0, 1 - $i, $i + 1);
        }
    }

    /**
     * testing method taxonomy::validate_taxonomy
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy::validate_taxonomy
     */
    public function test_validate_taxonomy(): void {
        $taxonomy = new \stdClass;
        $numtopics = 10;
        $numgoals = 10;
        $taxonomy->children = $this->create_taxonomy($numtopics, $numgoals);
        // Change ranking of topics 1 and 8.
        $taxonomy->children[1]->ranking = 100;
        $taxonomy->children[8]->ranking = 2;
        // Change rankings of goals of topic 3.
        $taxonomy->children[3]->children[2]->ranking = 100;
        $taxonomy->children[3]->children[4]->ranking = 3;
        // Remove name from topic 5 -> invalid -> should be removed.
        unset($taxonomy->children[5]->name);
        // Remove name from goal 0 of topic 7 -> invalid -> should be removed.
        unset($taxonomy->children[7]->children[0]->name);

        taxonomy::validate_taxonomy($taxonomy);
        $this->assertSame(count($taxonomy->children), $numtopics - 1);

        $originalindex = [0, 8, 2, 3, 4, 6, 7, 9, 1];

        for ($i = 0; $i <= 2; $i++) {
            $this->check_topic($taxonomy->children[$i], $originalindex[$i], $i + 1, $numgoals, true);
        }

        // Need to check children manually.
        $this->check_topic($taxonomy->children[3], $originalindex[3], 4, $numgoals, false);
        $originalgoalsindex = [0, 1, 4, 3, 5, 6, 7, 8, 9, 2];
        for ($ii = 0; $ii < $numgoals; $ii++) {
            $this->check_goal($taxonomy->children[3]->children[$ii], $originalindex[3], $originalgoalsindex[$ii], $ii + 1);
        }

        for ($i = 4; $i <= 5; $i++) {
            $this->check_topic($taxonomy->children[$i], $originalindex[$i], $i + 1, $numgoals, true);
        }

        // Need to check children manually.
        $this->check_topic($taxonomy->children[6], $originalindex[6], 7, $numgoals - 1, false);
        $originalgoalsindex = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        for ($ii = 0; $ii < $numgoals - 1; $ii++) {
            $this->check_goal($taxonomy->children[6]->children[$ii], $originalindex[6], $originalgoalsindex[$ii], $ii + 1);
        }

        for ($i = 7; $i <= 8; $i++) {
            $this->check_topic($taxonomy->children[$i], $originalindex[$i], $i + 1, $numgoals, true);
        }
    }

    /**
     * testing method taxonomy::update_taxonomy
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\taxonomy::update_taxonomy
     * @covers \mod_learninggoalwidget\local\taxonomy::maanage_taxonomy
     * @covers \mod_learninggoalwidget\local\taxonomy::update_topic_goals
     * @covers \mod_learninggoalwidget\local\taxonomy::get_taxonomy_as_json
     * @covers \mod_learninggoalwidget\local\taxonomy::get_topics
     */
    public function test_update_taxonomy(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;

        $taxonomy = new \stdClass;
        $taxonomy->name = 'name';
        $taxonomy->children = $this->create_taxonomy(1, 0);

        // Check that students cannot update taxonomy
        $student = $this->create_user('student', $res->course->id, true);
        $this->expectException(required_capability_exception::class);
        taxonomy::update_taxonomy($lgwid, $taxonomy);

        $this->setUser($res->user);

        // Add + delete = no changes.
        $taxonomy->children[0]->deleted = true;
        taxonomy::update_taxonomy($lgwid, $taxonomy);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame(count($taxonomy->children), 0);

        // Add one topic with one deleted and one valid goal.
        $taxonomy->children = $this->create_taxonomy(1, 2);
        $taxonomy->children[0]->children[1]->deleted = true;
        taxonomy::update_taxonomy($lgwid, $taxonomy);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame(count($taxonomy->children), 1);
        $this->assertSame(count($taxonomy->children[0]->children), 1);
        $this->check_topic($taxonomy->children[0], 0, 1, 1, true);

        // Delete goal.
        $taxonomy->children[0]->children[0]->deleted = true;
        taxonomy::update_taxonomy($lgwid, $taxonomy);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame(count($taxonomy->children), 1);
        $this->assertSame(count($taxonomy->children[0]->children), 0);
        $this->check_topic($taxonomy->children[0], 0, 1, 0, true);

        // Delete topic.
        $taxonomy->children[0]->deleted = true;
        taxonomy::update_taxonomy($lgwid, $taxonomy);
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertSame(count($taxonomy->children), 0);
    }
}
