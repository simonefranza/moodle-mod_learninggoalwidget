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
 * Learning Goal Taxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/externallib.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use mod_learninggoalwidget\local\taxonomy;

/**
 * Learning Goal Taxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class taxonomy_test extends \advanced_testcase {
    use mod_learninggoalwidget\utils;

    /**
     * testing class taxonomy
     *
     * @return void
     */
    public function test_emptytaxonomy() {
        $res = $this->setup_widget();

        $emptytaxonomy = new \stdClass;
        $emptytaxonomy->name = "Learning Goal's taxonomy";
        $emptytaxonomy->children = [];
        $jsonemptytaxonomy = json_encode($emptytaxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        $taxonomy = new taxonomy($res->instance->id);
        $this->assertNotNull($taxonomy);
        $json = $taxonomy->get_taxonomy_as_json();
        $this->assertNotNull($json);
        $this->assertNotEmpty($json);
        $this->assertEquals($jsonemptytaxonomy, $json);
    }

    /**
     * testing class taxonomy: inserting a topic
     *
     * @return void
     */
    public function test_inserttopic() {
        $res = $this->setup_widget();

        $title = "Artificial Intelligence Basics";
        $shorttitle = "AIBasics";
        $url = "http://aibasics.at";

        $result = mod_learninggoalwidget_external::insert_topic(
            $res->instance->id,
            $title,
            $shorttitle,
            $url
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::insert_topic_returns(), $result);

        $goals = $this->check_topic($title, $shorttitle, $url, 1, $result);

        $this->assertEquals([], $goals);
    }

    /**
     * testing class taxonomy: updating a topic
     *
     * @return void
     */
    public function test_updatetopic() {
        $result = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        $newtitle = "new Name";
        $newshorttitle = "new Shortname";
        $newurl = "http://new.at";

        // Update topic.
        $update = mod_learninggoalwidget_external::update_topic(
            $result->instance->id,
            $result->topic->id,
            $newtitle,
            $newshorttitle,
            $newurl
        );

        $goals = $this->check_topic($newtitle, $newshorttitle, $newurl, 1, $update);

        $this->assertEquals([], $goals);
    }

    /**
     * testing class taxonomy: delete a topic
     *
     * @return void
     */
    public function test_deletetopic() {
        global $DB;

        $result = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // Delete topic.
        $deleted = mod_learninggoalwidget_external::delete_topic($result->topic->id);

        // We need to execute the return values cleaning process to simulate the web service server.
        $deleted = external_api::clean_returnvalue(mod_learninggoalwidget_external::delete_topic_returns(), $deleted);

        $this->assertNotNull($deleted);
        $this->assertNotEmpty($deleted);
        $parsed = json_decode($deleted);

        $this->assertNotNull($parsed);

        $this->assertNotNull($parsed->name);
        $this->assertNotEmpty($parsed->name);
        $this->assertEquals("Learning Goal's taxonomy", $parsed->name);

        $this->assertNotNull($parsed->children);
        $this->assertIsArray($parsed->children);
        $this->assertEquals(0, count($parsed->children));

        $this->assertFalse($DB->record_exists('learninggoalwidget_topics', ['id' => $result->topic->id]));
    }

    /**
     * testing class taxonomy: move topic before another topic
     *
     * @return void
     */
    public function test_moveuptopic() {

        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Move topic 2 up.
        $result = mod_learninggoalwidget_external::moveup_topic(
            $resultcourse->topic2->id,
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::moveup_topic_returns(), $result);

        $this->check_course_with_topics($result, $resultcourse->topic1, $resultcourse->topic2);
    }

    /**
     * testing class taxonomy: move topic behind another topic
     *
     * @return void
     */
    public function test_movedowntopic() {

        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Move topic 1 behind topic 2.
        $result = mod_learninggoalwidget_external::movedown_topic(
            $resultcourse->topic1->id,
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::movedown_topic_returns(), $result);

        $this->check_course_with_topics($result, $resultcourse->topic1, $resultcourse->topic2);
    }

    /**
     * testing class taxonomy: inserting a goal
     *
     * @return void
     */
    public function test_insertgoal() {
        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Insert goal under topic 1.
        $result = mod_learninggoalwidget_external::insert_goal(
            $resultcourse->instance->id,
            $resultcourse->topic1->id,
            "Knowing theoretical foundations of AI",
            "TheoreticalFoundationsAI",
            "http://aibasics.goal1.at"
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::insert_goal_returns(), $result);

        $goals = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            1,
            $result
        );

        $this->assertIsArray($goals);
        $this->assertEquals(1, count($goals));

        $goal = $goals[0];

        $this->assertIsNumeric($goal->goalid);
        $this->assertTrue($goal->goalid > 0);
        $this->assertEquals("Knowing theoretical foundations of AI", $goal->name);
        $this->assertEquals("TheoreticalFoundationsAI", $goal->keyword);
        $this->assertEquals("http://aibasics.goal1.at", $goal->link);
        $this->assertEquals(1, $goal->ranking);
    }

    /**
     * testing class taxonomy: updating a goal
     *
     * @return void
     */
    public function test_updategoal() {
        $res = $this->setup_course_and_insert_goals();

        // Update goal under topic 1.
        $result = mod_learninggoalwidget_external::update_goal(
            $res->instance->id,
            $res->goal->id,
            "Updated Goalname",
            "Updated Goal Shortname",
            "http://goal1.updated.at"
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::update_goal_returns(), $result);

        $goals = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            1,
            $result
        );

        $this->assertEquals(1, count($goals));

        $goal = $goals[0];

        $this->assertIsNumeric($goal->goalid);
        $this->assertEquals($res->goal->id, $goal->goalid);
        $this->assertEquals("Updated Goalname", $goal->name);
        $this->assertEquals("Updated Goal Shortname", $goal->keyword);
        $this->assertEquals("http://goal1.updated.at", $goal->link);
        $this->assertEquals(1, $goal->ranking);
    }

    /**
     * testing class taxonomy: deleting a goal
     *
     * @return void
     */
    public function test_deletegoal() {
        global $DB;

        $res = $this->setup_course_and_insert_goals();

        // Delete goal under topic 1.
        $result = mod_learninggoalwidget_external::delete_goal(
            $res->instance->id,
            $res->topic1->id,
            $res->goal->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::delete_goal_returns(), $result);

        $goals = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            1,
            $result
        );

        $this->assertIsArray($goals);
        $this->assertEquals(0, count($goals));

        $this->assertTrue($DB->record_exists('learninggoalwidget_topics', ['id' => $res->topic1->id]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_goals', ['id' => $res->goal->id]));
    }

    /**
     * testing class taxonomy: move up a goal
     *
     * @return void
     */
    public function test_moveupgoal() {
        $res = $this->setup_course_and_insert_two_goals();

        // Move goal 2 before goal 1 under topic 1.
        $result = mod_learninggoalwidget_external::moveup_goal($res->goal2->id);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::moveup_goal_returns(), $result);

        $this->check_goal($result, $res->goal1, $res->goal2);
    }

    /**
     * testing class taxonomy: move down a goal
     *
     * @return void
     */
    public function test_movedowngoal() {
        $res = $this->setup_course_and_insert_two_goals();

        // Move goal 1 behind goal 2 under topic 1.
        $result = mod_learninggoalwidget_external::movedown_goal($res->goal1->id);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::movedown_goal_returns(), $result);

        $this->check_goal($result, $res->goal1, $res->goal2);
    }

    /**
     * testing class taxonomy: get users progress
     *
     * @return void
     */
    public function test_getuserprogress() {
        $res = $this->setup_course_and_insert_two_goals();

        // Get taxonomy with user progress values.
        $result = mod_learninggoalwidget_external::get_taxonomy_for_user(
            $res->instance->id,
            $res->user->id,
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_for_user_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            0,
            $res->goal1->id,
            $result
        );
        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            0,
            $res->goal2->id,
            $result
        );
    }

    /**
     * testing class taxonomy: update users learning goal progress
     *
     * @return void
     */
    public function test_updateuserprogress() {
        $res = $this->setup_course_and_insert_two_goals();

        // Update learning goal 1 progess to 99.
        $result = mod_learninggoalwidget_external::update_user_progress(
            $res->instance->id,
            $res->user->id,
            $res->topic1->id,
            $res->goal1->id,
            99
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::update_user_progress_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            99,
            $res->goal1->id,
            $result
        );

        // Update learning goal 1 progess to 50.
        $result = mod_learninggoalwidget_external::update_user_progress(
            $res->instance->id,
            $res->user->id,
            $res->topic1->id,
            $res->goal1->id,
            50
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_for_user_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            50,
            $res->goal1->id,
            $result
        );

        // Update learning goal 2 progess to 100.
        $result = mod_learninggoalwidget_external::update_user_progress(
            $res->instance->id,
            $res->user->id,
            $res->topic1->id,
            $res->goal2->id,
            100
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_for_user_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            100,
            $res->goal2->id,
            $result
        );
    }

    /**
     * testing class taxonomy: update users learning goal progress
     *
     * @return void
     */
    public function test_logevent() {
        global $DB;
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
        get_log_manager(true);
        $res = $this->setup_course_and_insert_goals();
        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $res->instance->id);
        $course1 = $coursemodule->course;
        $coursecontext = \context_course::instance($coursemodule->course);
        $cmcontext = \context_module::instance($coursemodule->id);
        $widgetinstance = $res->instance;
        $topicrecord = $res->topic1;
        $user1 = $res->user;
        $goalrecord = $res->goal;
        $progress = 50;
        $timestamp = 12345678;

        mod_learninggoalwidget_external::update_user_progress(
            $widgetinstance->id,
            $user1->id,
            $topicrecord->id,
            $goalrecord->id,
            $progress,
        );

        $eventparams = [];
        $eventname = "\\mod_learninggoalwidget\\event\\learninggoal_updated";
        $eventparams[3] = ["name" => "instanceid", "value" => $widgetinstance->id];
        $eventparams[4] = ["name" => "userid", "value" => $user1->id];
        $eventparams[5] = ["name" => "timestamp", "value" => $timestamp];
        $eventparams[6] = ["name" => "goalname", "value" => $goalrecord->title];
        $eventparams[7] = ["name" => "goalprogress", "value" => $progress];

        // Update learning goal 2 progess to 50.
        $result = mod_learninggoalwidget_external::log_event(
            $widgetinstance->id,
            $user1->id,
            $eventparams
        );

        $sqlstmt = 'SELECT id, eventname, other, userid
                      FROM {logstore_standard_log}
                     WHERE eventname = :eventname
                       AND userid = :userid';
        $params = [
            'eventname' => $eventname,
            'userid' => $user1->id,
        ];
        $res = $DB->get_record_sql($sqlstmt, $params);
        $this->assertTrue($res !== false);
        $otherdata = json_decode($res->other);
        $output = new stdClass;
        foreach ($otherdata as $key => $value) {
            $output->{$value->name} = $value->value;
        }
        $this->assertTrue($output->courseid == $course1->id);
        $this->assertTrue($output->coursemoduleid == $coursemodule->id);
        $this->assertTrue($output->instanceid == $widgetinstance->id);
        $this->assertTrue($output->userid == $user1->id);
        $this->assertTrue($output->timestamp == $timestamp);
        $this->assertTrue($output->goalname == $goalrecord->title);
        $this->assertTrue($output->goalprogress == $progress);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::log_event_returns(), $result);
    }

    /**
     * testing get_taxonomy
     *
     * @return void
     */
    public function test_gettaxonomy() {
        $res = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Get taxonomy.
        $result = mod_learninggoalwidget_external::get_taxonomy($res->instance->id);

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $expectedjson = new stdClass();
        $expectedjson->name = "Learning Goal's taxonomy";
        $topic1 = new stdClass();
        $topic1->topicid = $res->topic1->id;
        $topic1->name = "Artificial Intelligence Basics Part 1";
        $topic1->keyword = "AIBasics 1";
        $topic1->link = "http://aibasics1.at";
        $topic1->children = [];

        $topic2 = new stdClass();
        $topic2->topicid = $res->topic2->id;
        $topic2->name = "Artificial Intelligence Basics Part 2";
        $topic2->keyword = "AIBasics 2";
        $topic2->link = "http://aibasics2.at";
        $topic2->children = [];
        $expectedjson->children = [$topic1, $topic2];

        $this->check_json($parsed, $expectedjson);
    }

    /**
     * testing add_taxonomy
     *
     * @return void
     */
    public function test_addtaxonomy() {
        $res = $this->setup_widget();

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $res->instance->id);

        $taxonomy = (object) [
            "name" => "Learning Goal's taxonomy",
            "children" => [
                (object) [
                    "name" => "topic1",
                    "keyword" => "topic1keyword",
                    "link" => "http://topic1.com",
                    "children" => [
                        (object) [
                            "name" => "goal1topic1",
                            "keyword" => "goal1topic1keyword",
                            "link" => "http://goal1.topic1.com",
                        ],
                        (object) [
                            "name" => "goal2topic1",
                            "keyword" => "goal2topic1keyword",
                            "link" => "http://goal2.topic1.com",
                        ],
                    ],
                ],
                (object) [
                    "name" => "topic2",
                    "keyword" => "topic2keyword",
                    "link" => "http://topic2.com",
                    "children" => [
                        (object) [
                            "name" => "goal1topic2",
                            "keyword" => "goal1topic2keyword",
                            "link" => "http://goal1.topic2.com",
                        ],
                        (object) [
                            "name" => "goal2topic2",
                            "keyword" => "goal2topic2keyword",
                            "link" => "http://goal2.topic2.com",
                        ],
                    ],
                ],
            ],
        ];
        $result = mod_learninggoalwidget_external::add_taxonomy(
            $res->instance->id,
            json_encode($taxonomy)
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::add_taxonomy_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $expectedjson = new stdClass();
        $expectedjson->name = $taxonomy->name;
        $expectedjson->children = [];
        foreach ($taxonomy->children as $topicidx => $topic) {
            $expectedjson->children[] = (object) [
                "topicid" => $parsed->children[$topicidx]->topicid,
                "ranking" => $parsed->children[$topicidx]->ranking,
                "name" => $topic->name,
                "keyword" => $topic->keyword,
                "link" => $topic->link,
                "children" => [],
            ];
            foreach ($topic->children as $goalidx => $goal) {
                $expectedjson->children[$topicidx][5][] = (object) [
                    "goalid" => $parsed->children[$topicidx]->children[$goalidx]->goalid,
                    "ranking" => $parsed->children[$topicidx]->children[$goalidx]->ranking,
                    "name" => $goal->name,
                    "keyword" => $goal->keyword,
                    "link" => $goal->link,
                ];
            }
        }
        $this->check_json($parsed, $expectedjson);
    }

    /**
     * testing delete_taxonomy
     *
     * @return void
     */
    public function test_deletetaxonomy() {
        $res = $this->setup_widget();

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $res->instance->id);

        $taxonomy = (object) [
            "name" => "Learning Goal's taxonomy",
            "children" => [
                (object) [
                    "name" => "topic",
                    "keyword" => "topickeyword",
                    "link" => "http://topic.com",
                    "children" => [
                        (object) [
                            "name" => "goal1topic",
                            "keyword" => "goal1topickeyword",
                            "link" => "http://goal1.topic.com",
                        ],
                        (object) [
                            "name" => "goal2topic",
                            "keyword" => "goal2topickeyword",
                            "link" => "http://goal2.topic.com",
                        ],
                    ],
                ],
            ],
        ];
        $result = mod_learninggoalwidget_external::add_taxonomy(
            $res->instance->id,
            json_encode($taxonomy)
        );

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $result = mod_learninggoalwidget_external::delete_taxonomy(
            $res->instance->id,
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::delete_taxonomy_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $expectedjson = new stdClass();
        $expectedjson->name = $taxonomy->name;
        $expectedjson->children = [];
        $this->check_json($parsed, $expectedjson);
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
            $expectedranking = $expectedtopic[0];
            $expectedtopicid = $expectedtopic[1];
            $expectedtopicname = $expectedtopic[2];
            $expectedshortname = $expectedtopic[3];
            $expectedurl = $expectedtopic[4];
            $expectedgoals = $expectedtopic[5];
            $this->assertEquals($expectedranking, $json->children[$topicidx][0]);
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
                $expectedgoalranking = $expectedgoal[0];
                $expectedgoaltopicid = $expectedgoal[1];
                $expectedgoaltopicname = $expectedgoal[2];
                $expectedgoalshortname = $expectedgoal[3];
                $expectedgoalurl = $expectedgoal[4];
                $this->assertEquals($expectedgoalranking, $testedgoals[$goalidx][0]);
                $this->assertEquals($expectedgoaltopicid, $testedgoals[$goalidx][1]);
                $this->assertEquals($expectedgoaltopicname, $testedgoals[$goalidx][2]);
                $this->assertEquals($expectedgoalshortname, $testedgoals[$goalidx][3]);
                $this->assertEquals($expectedgoalurl, $testedgoals[$goalidx][4]);
            }
        }
    }

    /**
     * helper function creating an instance
     *
     * @return stdClass
     */
    protected function setup_widget() {
        global $DB;

        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $return = new stdClass;
        $course = $this->getDataGenerator()->create_course();
        $return->instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course->id]);
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
     * @return stdClass
     */
    protected function setup_topic($topictitle, $topicshortname, $topicurl) {
        global $DB;
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
     * helper function testing a course with topics
     *
     * @param [string] $expectedtitle
     * @param [string] $expectedshortname
     * @param [string] $expectedurl
     * @param [number] $expectedranking
     * @param string $taxonomy
     * @return array
     */
    protected function check_topic($expectedtitle, $expectedshortname, $expectedurl, $expectedranking, $taxonomy) {
        $this->assertNotNull($taxonomy);
        $this->assertNotEmpty($taxonomy);
        $parsed = json_decode($taxonomy);

        $topic = $this->check_topic_properties($parsed);

        $this->assertEquals($expectedtitle, $topic->name);
        $this->assertEquals($expectedshortname, $topic->keyword);
        $this->assertEquals($expectedurl, $topic->link);
        $this->assertEquals($expectedranking, $topic->ranking);
        return $topic->children;
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
     * create course with topics and two learning goals
     *
     * @return array
     */
    protected function setup_course_and_insert_two_goals() {
        global $DB;

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
     * check learning goals
     *
     * @param string $topicjson
     * @param object $goalrecord1
     * @param object $goalrecord2
     * @return void
     */
    protected function check_goal($topicjson, $goalrecord1, $goalrecord2) {
        $goals = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            1,
            $topicjson
        );

        $this->assertEquals(2, count($goals));

        foreach ($goals as $goal) {
            $ranking = $goal->ranking;
            $goalid = $goal->goalid;
            $goalname = $goal->name;
            $goalshortname = $goal->keyword;
            $goalurl = $goal->link;

            if ($goalname === "Goal 1 under Topic 1") {
                $this->assertEquals(2, $ranking);
                $this->assertEquals($goalrecord1->id, $goalid);
                $this->assertEquals("Goal 1 shortname", $goalshortname);
                $this->assertEquals("http://goal1.at", $goalurl);
            }
            if ($goalname === "Goal 2 under Topic 1") {
                $this->assertEquals(1, $ranking);
                $this->assertEquals($goalrecord2->id, $goalid);
                $this->assertEquals("Goal 2 shortname", $goalshortname);
                $this->assertEquals("http://goal2.at", $goalurl);
            }
        }
    }

    /**
     * check some topic properties
     *
     * @param object $taxonomy
     * @return array
     */
    protected function check_topic_properties($taxonomy) {
        $this->assertNotNull($taxonomy);

        $this->assertNotNull($taxonomy->name);
        $this->assertNotEmpty($taxonomy->name);
        $this->assertEquals("Learning Goal's taxonomy", $taxonomy->name);

        $this->assertNotNull($taxonomy->children);
        $this->assertIsArray($taxonomy->children);
        $this->assertTrue(count($taxonomy->children) > 0);

        $topic = $taxonomy->children[0];

        $this->assertIsNumeric($topic->topicid);
        $this->assertTrue($topic->topicid > 0);
        $this->assertIsNumeric($topic->ranking);
        $this->assertEquals(1, $topic->ranking);

        return $topic;
    }
}
