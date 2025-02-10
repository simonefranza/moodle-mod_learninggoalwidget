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
 * Learning Goal Taxonomy Goal Test
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
final class goal_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing method goal::validate_goal
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\goal::validate_goal
     * @covers \mod_learninggoalwidget\local\goal::validate_children_properties
     */
    public function test_validate_goal(): void {
        $goal = new \stdClass;
        $this->validate_and_reset_goal($goal, false);

        // Goal name is not string.
        $goal->name = 10;
        $this->validate_and_reset_goal($goal, false);

        // Goal name is string, but no shortname.
        $goal->name = "Name";
        $this->validate_and_reset_goal($goal, false);

        // Goal name is string, but shortname is not.
        $goal->shortname = 10;
        $this->validate_and_reset_goal($goal, false);

        // Goal name, shortname is string, but no url.
        $goal->shortname = "Shortname";
        $this->validate_and_reset_goal($goal, false);

        // Goal name, shortname is string, but url is not.
        $goal->url = 10;
        $this->validate_and_reset_goal($goal, false);

        // Goal name, shortname, url is string, but no ranking.
        $goal->url = 'http://example.com';
        $this->validate_and_reset_goal($goal, false);

        // Goal name, shortname, url is string, but ranking is not int.
        $goal->ranking = 'test';
        $this->validate_and_reset_goal($goal, false);

        // Goal name, shortname, url is string, and ranking is int, but no goalid.
        $goal->ranking = 1;
        $this->validate_and_reset_goal($goal, false);

        // Goal name, shortname, url is string, and ranking is int, but goalid is not int.
        $goal->goalid = 'test';
        $this->validate_and_reset_goal($goal, false);

        // Valid goal.
        $goal->goalid = 1;
        $this->validate_and_reset_goal($goal, true);

        // Valid goal.
        $goal->deleted = false;
        $goal->new = true;
        $goal->edit = true;
        $this->validate_and_reset_goal($goal, true);
    }

    /**
     * testing methods goal::update_goal and goal::delete_goal
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\goal::update_goal
     * @covers \mod_learninggoalwidget\local\goal::delete_goal
     * @covers \mod_learninggoalwidget\local\goal::validate_goal
     * @covers \mod_learninggoalwidget\local\goal::validate_children_properties
     */
    public function test_goal(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $topic = (object) [
            'name' => 'T1',
            'shortname' => 'T1',
            'url' => 'http://example.com',
            'ranking' => 1,
            'topicid' => 1,
            'children' => [],
            'new' => true,
        ];
        topic::update_topic($lgwid, $topic);

        // Get taxonomy.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 1);
        $this->assertTrue(count($taxonomy->children[0]->children) == 0);

        // Test invalid goal.
        $newgoal = (object) [
            'name' => 'G1',
        ];
        $topicid = $taxonomy->children[0]->topicid;
        $this->assertFalse(goal::validate_goal($newgoal));
        unset($newgoal->valid);
        $this->assertSame(goal::update_goal($lgwid, $topicid, $newgoal), -2);

        // Fix goal.
        $newgoal->shortname = 'G1';
        $newgoal->url = 'http://example.com';
        $newgoal->ranking = 1;
        $newgoal->goalid = 3;
        $this->assertTrue(goal::validate_goal($newgoal));
        unset($newgoal->valid);

        // Test invalid lgwid.
        $this->assertSame(goal::update_goal($lgwid + 1, $topicid, $newgoal), -1);
        // Test invalid topicid.
        $this->assertSame(goal::update_goal($lgwid, $topicid + 1, $newgoal), -1);

        // Goal not marked as new -> should not exist in db so cannot be updated.
        $this->assertSame(goal::update_goal($lgwid, $topicid, $newgoal), -1);

        // Add goal.
        $newgoal->new = true;
        $newgoal->goalid = goal::update_goal($lgwid, $topicid, $newgoal);

        // Get taxonomy and check for update.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 1);
        $this->assertTrue(count($taxonomy->children[0]->children) == 1);
        $addedgoal = $taxonomy->children[0]->children[0];
        $this->compare_two_goals($addedgoal, $newgoal);

        // Update goal.
        unset($newgoal->new);
        $newgoal->edit = true;
        $newgoal->name = 'G2';
        $newgoal->shortname = 'G2';
        $newgoal->url = 'http://example2.com';
        $newgoal->ranking = 2;
        $newgoal->goalid = $addedgoal->goalid + 1;

        // Goal to update has invalid goalid.
        $this->assertSame(goal::update_goal($lgwid, $topicid, $newgoal), -1);

        // Fix goalid.
        $newgoal->goalid = $addedgoal->goalid;
        $this->assertSame(goal::update_goal($lgwid, $topicid, $newgoal), $addedgoal->goalid);

        // Get taxonomy and check for update.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 1);
        $this->assertTrue(count($taxonomy->children[0]->children) == 1);
        $addedgoal = $taxonomy->children[0]->children[0];
        $this->compare_two_goals($addedgoal, $newgoal);

        // Check delete_goal.
        // Invalid lgwid.
        $this->assertFalse(goal::delete_goal($lgwid + 1, $topicid, $addedgoal->goalid));
        // Invalid topicid.
        $this->assertFalse(goal::delete_goal($lgwid, $topicid + 1, $addedgoal->goalid));
        // Invalid goalid.
        $this->assertFalse(goal::delete_goal($lgwid, $topicid, $addedgoal->goalid + 1));

        // Valid delete.
        $this->assertTrue(goal::delete_goal($lgwid, $topicid, $addedgoal->goalid));

        // Check goal is deleted.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 1);
        $this->assertTrue(count($taxonomy->children[0]->children) == 0);
    }

    /**
     * Helper function to check goal validation
     *
     * @param stdClass $goal Goal to check
     * @param bool $expected expected outcome
     */
    private function validate_and_reset_goal($goal, $expected): void {
        $this->assertSame(goal::validate_goal($goal), $expected);
        $this->assertSame($goal->valid, $expected);
        unset($goal->valid);
    }

    /**
     * Helper function to check that two goals are equivalent
     *
     * @param stdClass $goal1 goal1 to check
     * @param stdClass $goal2 goal2 to check
     */
    private function compare_two_goals($goal1, $goal2): void {
        $this->assertSame($goal1->goalid, $goal2->goalid);
        $this->assertSame($goal1->name, $goal2->name);
        $this->assertSame($goal1->shortname, $goal2->shortname);
        $this->assertSame($goal1->url, $goal2->url);
        $this->assertSame($goal1->ranking, $goal2->ranking);
    }
}
