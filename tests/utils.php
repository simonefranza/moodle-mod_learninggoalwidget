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
 * Learning Goal Taxonomy Test Utils
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_external\external_api;
use mod_learninggoalwidget\external\update_topic;

/**
 * Learning Goal Taxonomy Test Utils
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait utils {
    /**
     * helper function, sets up test environment
     *
     * @return void
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * helper function inserting a topic
     *
     * @param [number] $instance
     * @param [string] $title
     * @param [string] $shortname
     * @param [string] $url
     * @param [number] $ranking
     * @return dbrecord
     */
    protected function insert_topic($instance, $title, $shortname, $url, $ranking) {
        global $DB;
        $topic = new \stdClass;
        $topic->learninggoalwidgetid = $instance;
        $topic->title = $title;
        $topic->shortname = $shortname;
        $topic->url = $url;
        $topic->ranking = $ranking;
        $topic->id = $DB->insert_record('learninggoalwidget_topics', $topic);
        return $topic;
    }

    /**
     * helper function inserting a goal
     *
     * @param [number] $instance
     * @param [number] $topicid
     * @param [string] $title
     * @param [string] $shortname
     * @param [string] $url
     * @param [number] $ranking
     * @return dbrecord
     */
    protected function insert_goal($instance, $topicid, $title, $shortname, $url, $ranking) {
        global $DB;
        $goal = new \stdClass;
        $goal->learninggoalwidgetid = $instance;
        $goal->topicid = $topicid;
        $goal->title = $title;
        $goal->shortname = $shortname;
        $goal->url = $url;
        $goal->ranking = $ranking;
        $goal->id = $DB->insert_record('learninggoalwidget_goals', $goal);
        return $goal;
    }


    /**
     * helper function creating a course with 2 topics
     *
     * @param [string] $topic1title
     * @param [string] $topic1shortname
     * @param [string] $topic1url
     * @param [string] $topic2title
     * @param [string] $topic2shortname
     * @param [string] $topic2url
     * @return \stdClass
     */
    protected function setup_course_with_topics($topic1title, $topic1shortname, $topic1url,
        $topic2title, $topic2shortname, $topic2url) {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $return = new \stdClass;

        $course1 = $this->getDataGenerator()->create_course();
        $return->instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $return->user = $this->getDataGenerator()->create_user();
        $this->setUser($return->user);

        // Create topic 1 in course.
        $return->topic1 = $this->insert_topic($return->instance->id, $topic1title, $topic1shortname, $topic1url, 1);

        // Create topic 2 in course.
        $return->topic2 = $this->insert_topic($return->instance->id, $topic2title, $topic2shortname, $topic2url, 2);

        return $return;
    }

    /**
     * create course with topics and one learing goal
     *
     * @return \stdClass
     */
    protected function setup_course_and_insert_goals() {
        $course = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Insert goal under topic 1.
        // Insert in goal table.
        $course->goal = $this->insert_goal(
            $course->instance->id,
            $course->topic1->id,
            "Goal under Topic 1 to be updated",
            "Goal 1 shortname to be updated",
            "http://goal1.updateme.at",
            1);
        return $course;
    }

    /**
     * helper function to check that two taxonomies contain the same content
     *
     * @param [object] $json
     * @param [object] $expectedjson
     * @return void
     */
    protected function check_json($json, $expectedjson) {
        $this->assertNotNull($json->name);
        $this->assertNotEmpty($json->name);
        $this->assertNotNull( $expectedjson->name);
        $this->assertNotEmpty($expectedjson->name);
        $this->assertEquals($json->name, $expectedjson->name);

        $this->assertNotNull($json->children);
        $this->assertIsArray($json->children);
        $this->assertNotNull($expectedjson->children);
        $this->assertIsArray($expectedjson->children);
        $this->assertEquals(count($json->children), count($expectedjson->children));

        foreach ($expectedjson->children as $topicidx => $expectedtopic) {
            $expectedranking = $expectedtopic->ranking;
            $expectedtopicid = $expectedtopic->topicid;
            $expectedtopicname = $expectedtopic->name;
            $expectedshortname = $expectedtopic->keyword;
            $expectedurl = $expectedtopic->link;
            $expectedgoals = $expectedtopic->children;
            $this->assertEquals($expectedranking, $json->children[$topicidx]->ranking);
            $this->assertEquals($expectedtopicid, $json->children[$topicidx]->topicid);
            $this->assertEquals($expectedtopicname, $json->children[$topicidx]->name);
            $this->assertEquals($expectedshortname, $json->children[$topicidx]->keyword);
            $this->assertEquals($expectedurl, $json->children[$topicidx]->link);

            $this->assertNotNull($expectedgoals);
            $this->assertIsArray($expectedgoals);
            $testedgoals = $json->children[$topicidx]->children;
            $this->assertEquals(count($expectedgoals), count($testedgoals));
            $this->assertNotNull($testedgoals);
            $this->assertIsArray($testedgoals);

            foreach ($expectedgoals as $goalidx => $expectedgoal) {
                $expectedgranking = $expectedgoal->ranking;
                $expectedggoalid = $expectedgoal->goalid;
                $expectedggoalname = $expectedgoal->name;
                $expectedgshortname = $expectedgoal->keyword;
                $expectedgurl = $expectedgoal->link;
                $this->assertEquals($expectedgranking, $testedgoals[$goalidx]->ranking);
                $this->assertEquals($expectedggoalid, $testedgoals[$goalidx]->goalid);
                $this->assertEquals($expectedggoalname, $testedgoals[$goalidx]->name);
                $this->assertEquals($expectedgshortname, $testedgoals[$goalidx]->keyword);
                $this->assertEquals($expectedgurl, $testedgoals[$goalidx]->link);
            }
        }
    }

    /**
     * helper function creating an instance
     *
     * @return \stdClass
     */
    protected function setup_widget() {
        $this->setUp();

        $return = new \stdClass;
        $return->course = $this->getDataGenerator()->create_course();
        $return->instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $res->course->id]);
        $return->user = $this->getDataGenerator()->create_user();
        $this->setUser($return->user);

        return $return;
    }

    /**
     * helper function creating a topic
     *
     * @param [string] $topictitle
     * @param [string] $topicshortname
     * @param [string] $topicurl
     * @return \stdClass
     */
    protected function setup_topic($topictitle, $topicshortname, $topicurl) {
        $res = $this->setup_widget();

        // Create topic in course.
        $res->topic = $this->insert_topic(
            $res->instance->id,
            $topictitle,
            $topicshortname,
            $topicurl,
            1
        );

        return $res;
    }

    /**
     * helper function, create course with topics and two learning goals
     *
     * @return array
     */
    protected function setup_course_and_insert_two_goals() {
        $res = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Insert goal 1 under topic 1.
        $res->goal1 = $this->insert_goal(
            $res->instance->id,
            $res->topic1->id,
            "Goal 1 under Topic 1",
            "Goal 1 shortname",
            "http://goal1.at",
            1
        );

        // Insert goal 2 under topic 1.
        $res->goal2 = $this->insert_goal(
            $res->instance->id,
            $res->topic1->id,
            "Goal 2 under Topic 1",
            "Goal 2 shortname",
            "http://goal2.at",
            2
        );

        return $res;
    }

    /**
     * helper function testing a course with topics
     *
     * @param string $expectedtitle
     * @param string $expectedshortname
     * @param string $expectedurl
     * @param string $expectedprogress
     * @param string $goalid
     * @param string $topicjson
     * @return void
     */
    protected function check_userprogress($expectedtitle, $expectedshortname,
        $expectedurl, $expectedprogress, $goalid, $topicjson) {

        $this->assertNotNull($topicjson);
        $this->assertNotEmpty($topicjson);
        $parsed = json_decode($topicjson);

        $this->assertNotNull($parsed);

        $this->assertNotNull($parsed->name);
        $this->assertNotEmpty($parsed->name);
        $this->assertEquals("name", $parsed->name);

        $this->assertNotNull($parsed->children);
        $this->assertIsArray($parsed->children);
        $this->assertEquals(2, count($parsed->children));

        $topic = $parsed->children[0];
        $topicid = $topic->topicid;
        $title = $topic->name;
        $shortname = $topic->keyword;
        $url = $topic->link;
        $type = $topic->type;
        $goals = $topic->children;

        $this->assertIsNumeric($topicid);
        $this->assertEquals($expectedtitle, $title);
        $this->assertEquals($expectedshortname, $shortname);
        $this->assertEquals($expectedurl, $url);
        $this->assertEquals("topic", $type);
        $this->assertIsArray($goals);
        $this->assertEquals(2, count($goals));

        foreach ($goals as $goal) {
            $goalname = $goal->name;
            $goalshortname = $goal->keyword;
            $goalurl = $goal->link;
            $goaltype = $goal->type;
            $goalprogress = $goal->pro;

            if ($goalname === "Goal 1 under Topic 1" && $goalid === $goal->goalid) {
                $this->assertEquals("Goal 1 shortname", $goalshortname);
                $this->assertEquals("http://goal1.at", $goalurl);
                $this->assertEquals("goal", $goaltype);
                $this->assertEquals($expectedprogress, $goalprogress);
            }
            if ($goalname === "Goal 2 under Topic 1" && $goalid === $goal->goalid) {
                $this->assertEquals("Goal 2 shortname", $goalshortname);
                $this->assertEquals("http://goal2.at", $goalurl);
                $this->assertEquals("goal", $goaltype);
                $this->assertEquals($expectedprogress, $goalprogress);
            }
        }
    }

    /**
     * helper function testing a course with topics
     *
     * @param [string] $topicjson
     * @param [object] $topicrecord1
     * @param [object] $topicrecord2
     * @return void
     */
    protected function check_course_with_topics($topicjson, $topicrecord1, $topicrecord2) {
        $result = $topicjson;
        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $this->assertNotNull($parsed);

        $this->assertNotNull($parsed->name);
        $this->assertNotEmpty($parsed->name);
        $this->assertEquals("Learning Goal's taxonomy", $parsed->name);

        $this->assertNotNull($parsed->children);
        $this->assertIsArray($parsed->children);
        $this->assertEquals(2, count($parsed->children));

        foreach ($parsed->children as $topic) {
            $ranking = $topic->ranking;
            $topicid = $topic->topicid;
            $topicname = $topic->name;
            $shortname = $topic->keyword;
            $url = $topic->link;
            $goals = $topic->children;
            if ($topicname === "Artificial Intelligence Basics Part 1") {
                $this->assertEquals(2, $ranking);
                $this->assertEquals($topicrecord1->id, $topicid);
                $this->assertEquals("AIBasics 1", $shortname);
                $this->assertEquals("http://aibasics1.at", $url);
                $this->assertEquals([], $goals);
            }
            if ($topicname === "Artificial Intelligence Basics Part 2") {
                $this->assertEquals(1, $ranking);
                $this->assertEquals($topicrecord2->id, $topicid);
                $this->assertEquals("AIBasics 2", $shortname);
                $this->assertEquals("http://aibasics2.at", $url);
                $this->assertEquals([], $goals);
            }
        }
    }


    /**
     * Helper function to create the children of a taxonomy
     *
     * @param int $numtopics Number of topics to create
     * @param int $numgoals Number of goals per topic to create
     * @return array of topics with goals in the children prop
     */
    private function create_taxonomy($numtopics, $numgoals): array {
        // Create $numtopics topics with $numgoals goals each.
        $topics = [];
        for ($i = 0; $i < $numtopics; $i++) {
            $goals = [];
            for ($ii = 0; $ii < $numgoals; $ii++) {
                $newgoal = (object) [
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
                'keyword' => 'T' . $i,
                'link' => 'http://topic' . $i . '.com',
                'ranking' => $i + 1,
                'topicid' => $i,
                'children' => $goals,
                'new' => true,
            ];
            $topics[] = $newtopic;
        }
        return $topics;
    }

    /**
     * Helper function to check that a topic contains the expected data
     * The data must be generated with create_taxonomy
     *
     * @param stdClass $topic Topic to check
     * @param number $i Value to use for the check
     * @param number $newranking New ranking of the topic
     * @param number $numgoals Number of goals that the topic should contain
     * @param bool $checkgoals Whether to check the goals of the topic or not
     */
    private function check_topic($topic, $i, $newranking, $numgoals, $checkgoals) {
        $this->assertTrue(isset($topic->name) && is_string($topic->name));
        $this->assertSame($topic->name, 'T' . $i);
        $this->assertTrue(isset($topic->keyword) && is_string($topic->keyword));
        $this->assertSame($topic->keyword, 'T' . $i);
        $this->assertTrue(isset($topic->link) && is_string($topic->link));
        $this->assertSame($topic->link, 'http://topic' . $i . '.com');
        $this->assertTrue(isset($topic->ranking) && is_int($topic->ranking));
        $this->assertSame($topic->ranking, $newranking);
        $this->assertTrue(isset($topic->topicid) && is_int($topic->topicid));
        $this->assertTrue(isset($topic->children) && is_array($topic->children));
        $this->assertTrue(count($topic->children) == $numgoals);
        if (!$checkgoals) {
            return;
        }
        for ($ii = 0; $ii < $numgoals; $ii++) {
            $this->check_goal($topic->children[$ii], $i, $ii);
        }
    }

    /**
     * Helper function to check that a goal contains the expected data
     * The data must be generated with create_taxonomy
     *
     * @param stdClass $goal Goal to check
     * @param number $i Topic-value to use for the check
     * @param number $ii Goal-value to use for the check
     * @param number $newranking New ranking
     */
    private function check_goal($goal, $i, $ii, $newranking = -2) {
        if ($newranking == -2) {
            $newranking = $ii + 1;
        }
        $this->assertTrue(isset($goal->name) && is_string($goal->name));
        $this->assertSame($goal->name, 'T' . $i . 'G' . $ii);
        $this->assertTrue(isset($goal->keyword) && is_string($goal->keyword));
        $this->assertSame($goal->keyword, 'T' . $i . 'G' . $ii);
        $this->assertTrue(isset($goal->link) && is_string($goal->link));
        $this->assertSame($goal->link, 'http://topic' . $i . 'goal' . $ii . '.com');
        $this->assertTrue(isset($goal->ranking) && is_int($goal->ranking));
        $this->assertSame($goal->ranking, $newranking);
        $this->assertTrue(isset($goal->goalid) && is_int($goal->goalid));
    }
}
