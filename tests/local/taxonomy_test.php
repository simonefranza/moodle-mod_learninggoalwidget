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
        $numtopics = 50;
        $numgoals = 10;
        $topics = [];
        // Create 50 topics with 10 goals each
        for ($i = 0; $i < $numtopics; $i++) {
            $goals = [];
            for ($ii = 0; $ii < $numgoals; $ii++) {
                $newgoal= (object) [
                    'name' => 'T' . $i . 'G' . $ii,
                    'keyword' => 'T' . $i . 'G' . $ii,
                    'link' => 'http://topic' . $i . 'goal' . $ii . '.com',
                    'ranking' => $ii + 1,
                    'goalid' => $i * $numtopics + $ii,
                    'new' => true,
                ];
                $goals[] = $newgoal;
            }
            $newtopic = (object) [
                'name' => 'T' . $i,
                'keyword' => 'T' $i,
                'link' => 'http://topic' . $i . '.com',
                'ranking' => $i + 1,
                'topicid' => $i,
                'children' => $goals,
                'new' => true,
            ];
            $topics[] = $newtopic;
        }

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

        // Check that everything was added correctly
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(isset($taxonomy->name) && is_string($taxonomy->name));
        $this->assertSame($taxonomy->name, "name");
        $this->assertTrue(isset($taxonomy->children) && is_array($taxonomy->children));
        $this->assertTrue(count($taxonomy->children) == $numtopics);
        for ($i = 0; $i < $numtopics; $i++) {
            $topic = $taxonomy->children[$i];
            $this->assertTrue(isset($topic->name) && is_string($topic->name));
            $this->assertSame($topic->name, 'T' . $i);
            $this->assertTrue(isset($topic->shortname) && is_string($topic->shortname));
            $this->assertSame($topic->shortname, 'T' . $i);
            $this->assertTrue(isset($topic->link) && is_string($topic->link));
            $this->assertSame($topic->link, 'http://topic' . $i . '.com');
            $this->assertTrue(isset($topic->ranking) && is_int($topic->ranking));
            $this->assertSame($topic->ranking, $i + 1);
            $this->assertTrue(isset($topic->topicid) && is_int($topic->topicid));
            $this->assertTrue(isset($topic->children) && is_array($topic->children));
            $this->assertTrue(count($topic->children) == $numgoals);
            for ($ii = 0; $ii < $numgoals; $ii++) {
                $goal = $topic->children[$ii];
                $this->assertTrue(isset($goal->name) && is_string($goal->name));
                $this->assertSame($goal->name, 'T' . $i . 'G' . $ii);
                $this->assertTrue(isset($goal->shortname) && is_string($goal->shortname));
                $this->assertSame($goal->shortname, 'T' . $i . 'G' . $ii);
                $this->assertTrue(isset($goal->link) && is_string($goal->link));
                $this->assertSame($goal->link, 'http://topic' . $i . 'goal' . $ii . '.com');
                $this->assertTrue(isset($goal->ranking) && is_int($goal->ranking));
                $this->assertSame($goal->ranking, $ii + 1);
                $this->assertTrue(isset($goal->goalid) && is_int($topic->goalid));
            }
        }
    }
}
