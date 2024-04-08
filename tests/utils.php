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
        global $CFG;
        $this->resetAfterTest(true);
        $this->setAdminUser();
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
     * @return array
     */
    protected function setup_course_with_topics($topic1title, $topic1shortname, $topic1url,
        $topic2title, $topic2shortname, $topic2url) {
        global $DB;

        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $course1 = $this->getDataGenerator()->create_course();
        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course1->id);

        // Create topic 1 in course.
        $topicrecord1 = new \stdClass;
        $topicrecord1->title = $topic1title;
        $topicrecord1->shortname = $topic1shortname;
        $topicrecord1->url = $topic1url;
        $topicrecord1->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord1);

        // Link topic 1 with widget instance.
        $topicinstancerecord1 = new \stdClass;
        $topicinstancerecord1->course = $course1->id;
        $topicinstancerecord1->coursemodule = $coursemodule->id;
        $topicinstancerecord1->instance = $widgetinstance->id;
        $topicinstancerecord1->topic = $topicrecord1->id;
        $topicinstancerecord1->rank = 1;
        $topicinstancerecord1->id = $DB->insert_record('learninggoalwidget_i_topics', $topicinstancerecord1);

        // Create topic 2 in course.
        $topicrecord2 = new \stdClass;
        $topicrecord2->title = $topic2title;
        $topicrecord2->shortname = $topic2shortname;
        $topicrecord2->url = $topic2url;
        $topicrecord2->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord2);

        // Link topic 2 with widget instance.
        $topicinstancerecord2 = new \stdClass;
        $topicinstancerecord2->course = $course1->id;
        $topicinstancerecord2->coursemodule = $coursemodule->id;
        $topicinstancerecord2->instance = $widgetinstance->id;
        $topicinstancerecord2->topic = $topicrecord2->id;
        $topicinstancerecord2->rank = 2;
        $topicinstancerecord2->id = $DB->insert_record('learninggoalwidget_i_topics', $topicinstancerecord2);

        return [$course1, $coursemodule, $widgetinstance, $topicrecord1,
            $topicrecord2, $topicinstancerecord1, $topicinstancerecord2, $user1, ];
    }

    /**
     * create course with topics and one learing goal
     *
     * @return array
     */
    protected function setup_course_and_insert_goals() {
        global $DB;

        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Insert goal under topic 1.
        // Insert in goal table.
        $goalrecord = new \stdClass;
        $goalrecord->title = "Goal under Topic 1 to be updated";
        $goalrecord->shortname = "Goal 1 shortname to be updated";
        $goalrecord->url = "http://goal1.updateme.at";
        $goalrecord->topic = $resultcourse[3]->id;
        $goalrecord->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord);

        // Link goal with learning goal activity in a course.
        $goalinstancerecord = new \stdClass;
        $goalinstancerecord->course = $resultcourse[0]->id;
        $goalinstancerecord->coursemodule = $resultcourse[1]->id;
        $goalinstancerecord->instance = $resultcourse[2]->id;
        $goalinstancerecord->topic = $resultcourse[3]->id;
        $goalinstancerecord->goal = $goalrecord->id;
        $goalinstancerecord->rank = 1;
        $goalinstancerecord->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord);

        return [$resultcourse, $goalrecord, $goalinstancerecord];
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
            $expectedrank = $expectedtopic[0];
            $expectedtopicid = $expectedtopic[1];
            $expectedtopicname = $expectedtopic[2];
            $expectedshortname = $expectedtopic[3];
            $expectedurl = $expectedtopic[4];
            $expectedgoals = $expectedtopic[5];
            $this->assertEquals($expectedrank, $json->children[$topicidx][0]);
            $this->assertEquals($expectedtopicid, $json->children[$topicidx][1]);
            $this->assertEquals($expectedtopicname, $json->children[$topicidx][2]);
            $this->assertEquals($expectedshortname, $json->children[$topicidx][3]);
            $this->assertEquals($expectedurl, $json->children[$topicidx][4]);

            $this->assertNotNull($expectedgoals);
            $this->assertIsArray($expectedgoals);
            $testedgoals = $json->children[$topicidx][5];
            $this->assertEquals(count($expectedgoals), count($testedgoals));
            $this->assertNotNull($testedgoals);
            $this->assertIsArray($testedgoals);

            foreach ($expectedgoals as $goalidx => $expectedgoal) {
                $expectedgoalrank = $expectedgoal[0];
                $expectedgoaltopicid = $expectedgoal[1];
                $expectedgoaltopicname = $expectedgoal[2];
                $expectedgoalshortname = $expectedgoal[3];
                $expectedgoalurl = $expectedgoal[4];
                $this->assertEquals($expectedgoalrank, $testedgoals[$goalidx][0]);
                $this->assertEquals($expectedgoaltopicid, $testedgoals[$goalidx][1]);
                $this->assertEquals($expectedgoaltopicname, $testedgoals[$goalidx][2]);
                $this->assertEquals($expectedgoalshortname, $testedgoals[$goalidx][3]);
                $this->assertEquals($expectedgoalurl, $testedgoals[$goalidx][4]);
            }
        }
    }

    /**
     * helper function inserting a learning goal
     *
     * @param [type] $expectedtitle
     * @param [type] $expectedshortname
     * @param [type] $expectedurl
     * @param [type] $updatedtopicjson
     * @return void
     */
    protected function check_updatetopic($expectedtitle, $expectedshortname, $expectedurl, $updatedtopicjson) {
        // We need to execute the return values cleaning process to simulate the web service server.
        $result = $updatedtopicjson;

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        [$title, $shortname, $url, ] =
            $this->check_topic_properties($parsed);

        $this->assertEquals($expectedtitle, $title);
        $this->assertEquals($expectedshortname, $shortname);
        $this->assertEquals($expectedurl, $url);
    }

    /**
     * helper function testing a course with topics
     *
     * @param [string] $expectedtitle
     * @param [string] $expectedshortname
     * @param [string] $expectedurl
     * @param string $topicjson
     * @return array
     */
    protected function check_topic($expectedtitle, $expectedshortname, $expectedurl, $topicjson) {

        $this->assertNotNull($topicjson);
        $this->assertNotEmpty($topicjson);
        $parsed = json_decode($topicjson);

        [$title, $shortname, $url, $goals] =
            $this->check_topic_properties($parsed);

        $this->assertEquals($expectedtitle, $title);
        $this->assertEquals($expectedshortname, $shortname);
        $this->assertEquals($expectedurl, $url);
        return [$goals];
    }

    /**
     * helper function creating a topic
     *
     * @param [string] $topictitle
     * @param [string] $topicshortname
     * @param [string] $topicurl
     * @return array
     */
    protected function setup_topic($topictitle, $topicshortname, $topicurl) {
        global $DB;

        $course1 = $this->getDataGenerator()->create_course();
        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course1->id);

        // Create topic in course.
        $topicrecord = new \stdClass;
        $topicrecord->title = $topictitle;
        $topicrecord->shortname = $topicshortname;
        $topicrecord->url = $topicurl;
        $topicrecord->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord);

        // Link topic with widget instance.
        $topicinstancerecord = new \stdClass;
        $topicinstancerecord->course = $course1->id;
        $topicinstancerecord->coursemodule = $coursemodule->id;
        $topicinstancerecord->instance = $widgetinstance->id;
        $topicinstancerecord->topic = $topicrecord->id;
        $topicinstancerecord->rank = 1;
        $topicinstancerecord->id = $DB->insert_record('learninggoalwidget_i_topics', $topicinstancerecord);

        return [$course1, $coursemodule, $widgetinstance, $topicrecord, $topicinstancerecord];
    }

    /**
     * helper function, create course with topics and two learning goals
     *
     * @return array
     */
    protected function setup_course_and_insert_two_goals() {
        global $DB;

        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Insert goal 1 under topic 1.
        // Insert in goal 1 table.
        $goalrecord1 = new \stdClass;
        $goalrecord1->title = "Goal 1 under Topic 1";
        $goalrecord1->shortname = "Goal 1 shortname";
        $goalrecord1->url = "http://goal1.at";
        $goalrecord1->topic = $resultcourse[3]->id;
        $goalrecord1->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord1);

        // Link goal 1 with learning goal activity in a course.
        $goalinstancerecord1 = new \stdClass;
        $goalinstancerecord1->course = $resultcourse[0]->id;
        $goalinstancerecord1->coursemodule = $resultcourse[1]->id;
        $goalinstancerecord1->instance = $resultcourse[2]->id;
        $goalinstancerecord1->topic = $resultcourse[3]->id;
        $goalinstancerecord1->goal = $goalrecord1->id;
        $goalinstancerecord1->rank = 1;
        $goalinstancerecord1->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord1);

        // Insert in goal 2.
        $goalrecord2 = new \stdClass;
        $goalrecord2->title = "Goal 2 under Topic 1";
        $goalrecord2->shortname = "Goal 2 shortname";
        $goalrecord2->url = "http://goal2.at";
        $goalrecord2->topic = $resultcourse[3]->id;
        $goalrecord2->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord2);

        // Link goal 2 with learning goal activity in a course.
        $goalinstancerecord2 = new \stdClass;
        $goalinstancerecord2->course = $resultcourse[0]->id;
        $goalinstancerecord2->coursemodule = $resultcourse[1]->id;
        $goalinstancerecord2->instance = $resultcourse[2]->id;
        $goalinstancerecord2->topic = $resultcourse[3]->id;
        $goalinstancerecord2->goal = $goalrecord2->id;
        $goalinstancerecord2->rank = 2;
        $goalinstancerecord2->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord2);

        return [$resultcourse, $goalrecord1, $goalinstancerecord1, $goalrecord2, $goalinstancerecord2];
    }

    /**
     * helper function, check some topic properties
     *
     * @param object $topic
     * @return array
     */
    protected function check_topic_properties($topic) {
        $this->assertNotNull($topic);

        $this->assertNotNull($topic->name);
        $this->assertNotEmpty($topic->name);
        $this->assertEquals("Learning Goal's taxonomy", $topic->name);

        $this->assertNotNull($topic->children);
        $this->assertIsArray($topic->children);
        $this->assertTrue(count($topic->children) > 0);

        $topic = $topic->children[0];
        $this->assertIsArray($topic);
        $this->assertEquals(6, count($topic));

        $rank = $topic[0];
        $topicid = $topic[1];
        $title = $topic[2];
        $shortname = $topic[3];
        $url = $topic[4];
        $goals = $topic[5];

        $this->assertEquals(1, $rank);
        $this->assertIsNumeric($topicid);
        $this->assertTrue($topicid > 0);

        return [$title, $shortname, $url, $goals];
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
        $this->assertEquals("Learning Goal's taxonomy", $parsed->name);

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
     * helper function testing updating a topic
     *
     * @param [string] $updatedtopicjson
     * @return array
     */
    protected function check_updatetopic_getgoals($updatedtopicjson) {
        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(update_topic::execute_returns(), $updatedtopicjson);
        $parsed = json_decode($result);
        $topic = $parsed->children[0];
        return $topic[5];
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
            $rank = $topic[0];
            $topicid = $topic[1];
            $topicname = $topic[2];
            $shortname = $topic[3];
            $url = $topic[4];
            $goals = $topic[5];
            if ($topicname === "Artificial Intelligence Basics Part 1") {
                $this->assertEquals(2, $rank);
                $this->assertEquals($topicrecord1->id, $topicid);
                $this->assertEquals("AIBasics 1", $shortname);
                $this->assertEquals("http://aibasics1.at", $url);
                $this->assertEquals([], $goals);
            }
            if ($topicname === "Artificial Intelligence Basics Part 2") {
                $this->assertEquals(1, $rank);
                $this->assertEquals($topicrecord2->id, $topicid);
                $this->assertEquals("AIBasics 2", $shortname);
                $this->assertEquals("http://aibasics2.at", $url);
                $this->assertEquals([], $goals);
            }
        }
    }

    /**
     * helper function, check learning goals
     *
     * @param string $topicjson
     * @param object $goalrecord1
     * @param object $goalrecord2
     * @return void
     */
    protected function check_goal($topicjson, $goalrecord1, $goalrecord2) {
        $resulttopic = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            $topicjson
        );

        $this->assertEquals(2, count($resulttopic[0]));

        foreach ($resulttopic[0] as $goal) {
            $rank = $goal[0];
            $goalid = $goal[1];
            $goalname = $goal[2];
            $goalshortname = $goal[3];
            $goalurl = $goal[4];

            if ($goalname === "Goal 1 under Topic 1") {
                $this->assertEquals(2, $rank);
                $this->assertEquals($goalrecord1->id, $goalid);
                $this->assertEquals("Goal 1 shortname", $goalshortname);
                $this->assertEquals("http://goal1.at", $goalurl);
            }
            if ($goalname === "Goal 2 under Topic 1") {
                $this->assertEquals(1, $rank);
                $this->assertEquals($goalrecord2->id, $goalid);
                $this->assertEquals("Goal 2 shortname", $goalshortname);
                $this->assertEquals("http://goal2.at", $goalurl);
            }
        }
    }
}
