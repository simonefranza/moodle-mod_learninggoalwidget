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
 * Learning Goal Taxonomy Topic Test
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

/**
 * Learning Goal Taxonomy Topic Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class topic_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing method topic::validate_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\topic::validate_topic
     * @covers \mod_learninggoalwidget\local\topic::validate_children_properties
     */
    public function test_validate_topic(): void {
        $topic = new \stdClass;
        $this->validate_and_reset_topic($topic, false);

        // Topic name is not string.
        $topic->name = 10;
        $this->validate_and_reset_topic($topic, false);

        // Topic name is string, but no keyword.
        $topic->name = "Name";
        $this->validate_and_reset_topic($topic, false);

        // Topic name is string, but keyword is not.
        $topic->keyword = 10;
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword is string, but no link.
        $topic->keyword = "Keyword";
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword is string, but link is not.
        $topic->link = 10;
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword, link is string, but no ranking.
        $topic->link = 'http://example.com';
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword, link is string, but ranking is not int.
        $topic->ranking = 'test';
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword, link is string, and ranking is int, but no topicid.
        $topic->ranking = 1;
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword, link is string, and ranking is int, but topicid is not int.
        $topic->topicid = 'test';
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword, link is string, and ranking and topicid are int, but no children array.
        $topic->topicid = 1;
        $this->validate_and_reset_topic($topic, false);

        // Topic name, keyword, link is string, and ranking and topicid are int, but children is not an array.
        $topic->children = 1;
        $this->validate_and_reset_topic($topic, false);

        // Valid topic.
        $topic->children = [];
        $this->validate_and_reset_topic($topic, true);

        // Valid topic.
        $topic->deleted = false;
        $topic->new = true;
        $topic->edit = true;
        $this->validate_and_reset_topic($topic, true);
    }

    /**
     * testing methods topic::update_topic and topic::delete_topic
     * @return void
     *
     * @covers \mod_learninggoalwidget\local\topic::update_topic
     * @covers \mod_learninggoalwidget\local\topic::delete_topic
     * @covers \mod_learninggoalwidget\local\topic::validate_topic
     * @covers \mod_learninggoalwidget\local\topic::validate_children_properties
     */
    public function test_goal(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;

        // Get taxonomy.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 0);

        // Invalid topic.
        $newtopic = (object) [
          'name' => 'T1',
        ];
        $this->assertFalse(goal::validate_goal($newtopic));
        unset($newtopic->valid);
        $this->assertSame(topic::update_topic($lgwid, $newtopic), -2);

        $newtopic = (object) [
            'name' => 'T1',
            'keyword' => 'T1',
            'link' => 'http://example.com',
            'ranking' => 1,
            'topicid' => 1,
            'children' => [],
        ];

        $this->assertTrue(topic::validate_topic($newtopic));
        unset($newtopic->valid);

        // Test invalid lgwid.
        $this->assertSame(topic::update_topic($lgwid + 1, $newtopic), -1);

        // Topic not marked as new -> should not exist in db so cannot be updated.
        $this->assertSame(topic::update_topic($lgwid, $newtopic), -1);

        // Add topic.
        $newtopic->new = true;
        $newtopic->topicid = topic::update_topic($lgwid, $newtopic);

        // Get taxonomy and check for update.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 1);
        $addedtopic = $taxonomy->children[0];
        $this->compare_two_topics($addedtopic, $newtopic);

        // Update topic.
        unset($newtopic->new);
        $newtopic->edit = true;
        $newtopic->name = 'T2';
        $newtopic->shortname = 'T2';
        $newtopic->link = 'http://example2.com';
        $newtopic->ranking = 2;
        $newtopic->topicid = $addedtopic->topicid + 1;

        // Topic to update has invalid topicid.
        $this->assertSame(topic::update_topic($lgwid, $newtopic), -1);

        // Fix goalid.
        $newtopic->topicid = $addedtopic->topicid;
        $this->assertSame(topic::update_topic($lgwid, $newtopic), $addedtopic->topicid);

        // Get taxonomy and check for update.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 1);
        $addedtopic = $taxonomy->children[0];
        $this->compare_two_topics($addedtopic, $newtopic);

        // Check delete_topic.
        // Invalid lgwid.
        $this->assertFalse(topic::delete_topic($lgwid + 1, $addedtopic->topicid));
        // Invalid topicid.
        $this->assertFalse(topic::delete_topic($lgwid, $addedtopic->topicid + 1));

        // Valid delete.
        $this->assertTrue(topic::delete_topic($lgwid, $addedtopic->topicid));

        // Check topic is deleted.
        $taxonomy = json_decode(taxonomy::get_taxonomy_as_json($lgwid));
        $this->assertNotNull($taxonomy);
        $this->assertTrue(count($taxonomy->children) == 0);
    }

    /**
     * Helper function to check topic validation
     *
     * @param stdClass topic topic to check
     * @param bool expected expected outcome
     */
    private function validate_and_reset_topic($topic, $expected): void {
        $this->assertSame(topic::validate_topic($topic), $expected);
        $this->assertSame($topic->valid, $expected);
        unset($topic->valid);
    }

    /**
     * Helper function to check that two topics are equivalent
     *
     * @param stdClass topic1 topic1 to check
     * @param stdClass topic2 topic2 to check
     */
    private function compare_two_topics($topic1, $topic2): void {
        $this->assertSame($topic1->topicid, $topic2->topicid);
        $this->assertSame($topic1->name, $topic2->name);
        $this->assertSame($topic1->keyword, $topic2->keyword);
        $this->assertSame($topic1->link, $topic2->link);
        $this->assertSame($topic1->ranking, $topic2->ranking);
        $this->assertSame(count($topic1->children), count($topic2->children));
    }
}
