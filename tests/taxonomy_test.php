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
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

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

    /**
     * testing class taxonomy: inserting a topic
     *
     * @return void
     */
    public function test_inserttopic() {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $course1 = $this->getDataGenerator()->create_course();
        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course1->id);

        $result = mod_learninggoalwidget_external::insert_topic(
            $course1->id,
            $coursemodule->id,
            $widgetinstance->id,
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::insert_topic_returns(), $result);

        $resulttopic = $this->check_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at",
            $result
        );

        $this->assertEquals([], $resulttopic[0]);
    }

    /**
     * testing class taxonomy: updating a topic
     *
     * @return void
     */
    public function test_updatetopic() {

        $result1 = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // Update topic.
        $result = mod_learninggoalwidget_external::update_topic(
            $result1[0]->id,
            $result1[1]->id,
            $result1[2]->id,
            $result1[3]->id,
            "Updated Name",
            "Updated Shortname",
            "http://updated.at"
        );

        $this->check_updatetopic(
            "Updated Name",
            "Updated Shortname",
            "http://updated.at",
            $result
        );

        $goals = $this->check_updatetopic_getgoals($result);
        $this->assertEquals([], $goals);
    }

    /**
     * testing class taxonomy: delete a topic
     *
     * @return void
     */
    public function test_deletetopic() {
        global $DB;

        $result1 = $this->setup_topic(
            "Artificial Intelligence Basics",
            "AIBasics",
            "http://aibasics.at"
        );

        // Delete topic.
        $result = mod_learninggoalwidget_external::delete_topic(
            $result1[0]->id,
            $result1[1]->id,
            $result1[2]->id,
            $result1[3]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::delete_topic_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $this->assertNotNull($parsed);

        $this->assertNotNull($parsed->name);
        $this->assertNotEmpty($parsed->name);
        $this->assertEquals("Learning Goal's Taxonomy", $parsed->name);

        $this->assertNotNull($parsed->children);
        $this->assertIsArray($parsed->children);
        $this->assertEquals(0, count($parsed->children));

        $this->assertFalse($DB->record_exists('learninggoalwidget_topic', ['id' => $result1[3]->id]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_i_topics', ['id' => $result1[4]->id]));
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
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[4]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::moveup_topic_returns(), $result);

        $this->check_course_with_topics($result, $resultcourse[3], $resultcourse[4]);
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
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::movedown_topic_returns(), $result);

        $this->check_course_with_topics($result, $resultcourse[3], $resultcourse[4]);
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
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            "Knowing theoretical foundations of AI",
            "TheoreticalFoundationsAI",
            "http://aibasics.goal1.at"
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::insert_goal_returns(), $result);

        $this->check_updatetopic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            $result
        );

        $goals = $this->check_updatetopic_getgoals($result);

        $this->assertIsArray($goals);
        $this->assertEquals(1, count($goals));
        $this->assertEquals(5, count($goals[0]));

        $goalrank = $goals[0][0];
        $goalid = $goals[0][1];
        $goalname = $goals[0][2];
        $goalshortname = $goals[0][3];
        $goalurl = $goals[0][4];

        $this->assertEquals(1, $goalrank);
        $this->assertIsNumeric($goalid);
        $this->assertTrue($goalid > 0);
        $this->assertEquals("Knowing theoretical foundations of AI", $goalname);
        $this->assertEquals("TheoreticalFoundationsAI", $goalshortname);
        $this->assertEquals("http://aibasics.goal1.at", $goalurl);
    }

    /**
     * testing class taxonomy: updating a goal
     *
     * @return void
     */
    public function test_updategoal() {
        [$resultcourse, $goalrecord, ] = $this->setup_course_and_insert_goals();

        // Update goal under topic 1.
        $result = mod_learninggoalwidget_external::update_goal(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            $goalrecord->id,
            "Updated Goalname",
            "Updated Goal Shortname",
            "http://goal1.updated.at"
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::update_goal_returns(), $result);

        $resulttopic = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            $result
        );

        $this->assertEquals(1, count($resulttopic[0]));
        $this->assertEquals(5, count($resulttopic[0][0]));

        $goalrank = $resulttopic[0][0][0];
        $goalid = $resulttopic[0][0][1];
        $goalname = $resulttopic[0][0][2];
        $goalshortname = $resulttopic[0][0][3];
        $goalurl = $resulttopic[0][0][4];

        $this->assertEquals(1, $goalrank);
        $this->assertIsNumeric($goalid);
        $this->assertEquals($goalrecord->id, $goalid);
        $this->assertEquals("Updated Goalname", $goalname);
        $this->assertEquals("Updated Goal Shortname", $goalshortname);
        $this->assertEquals("http://goal1.updated.at", $goalurl);
    }

    /**
     * testing class taxonomy: deleting a goal
     *
     * @return void
     */
    public function test_deletegoal() {
        global $DB;

        [$resultcourse, $goalrecord, $goalinstancerecord] = $this->setup_course_and_insert_goals();

        // Update goal under topic 1.
        $result = mod_learninggoalwidget_external::delete_goal(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            $goalrecord->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::delete_goal_returns(), $result);

        $resulttopic = $this->check_topic(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            $result
        );

        $this->assertIsArray($resulttopic[0]);
        $this->assertEquals(0, count($resulttopic[0]));

        $this->assertTrue($DB->record_exists('learninggoalwidget_topic', ['id' => $resultcourse[3]->id]));
        $this->assertTrue($DB->record_exists('learninggoalwidget_i_topics', ['id' => $resultcourse[5]->id]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_goal', ['id' => $goalrecord->id]));
        $this->assertFalse($DB->record_exists('learninggoalwidget_i_goals', ['id' => $goalinstancerecord->id]));
    }

    /**
     * testing class taxonomy: move up a goal
     *
     * @return void
     */
    public function test_moveupgoal() {

        [$resultcourse, $goalrecord1, , $goalrecord2, ] =
            $this->setup_course_and_insert_two_goals();

        // Move goal 2 before goal 1 under topic 1.
        $result = mod_learninggoalwidget_external::moveup_goal(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            $goalrecord2->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::moveup_goal_returns(), $result);

        $this->check_goal($result, $goalrecord1, $goalrecord2);
    }

    /**
     * testing class taxonomy: move down a goal
     *
     * @return void
     */
    public function test_movedowngoal() {

        [$resultcourse, $goalrecord1, , $goalrecord2, ] =
            $this->setup_course_and_insert_two_goals();

        // Move goal 1 behind goal 2 under topic 1.
        $result = mod_learninggoalwidget_external::movedown_goal(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[3]->id,
            $goalrecord1->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::movedown_goal_returns(), $result);

        $this->check_goal($result, $goalrecord1, $goalrecord2);
    }

    /**
     * testing class taxonomy: get users progress
     *
     * @return void
     */
    public function test_getuserprogress() {

        [$resultcourse, $goalrecord1, , $goalrecord2, ] =
            $this->setup_course_and_insert_two_goals();

        // Get taxonomy with user progress values.
        $result = mod_learninggoalwidget_external::get_taxonomy_for_user(
            $resultcourse[0]->id,
            $resultcourse[7]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_for_user_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            0,
            $goalrecord1->id,
            $result
        );
        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            0,
            $goalrecord2->id,
            $result
        );
    }

    /**
     * testing class taxonomy: update users learning goal progress
     *
     * @return void
     */
    public function test_updateuserprogress() {

        [$resultcourse, $goalrecord1, , $goalrecord2, ] =
            $this->setup_course_and_insert_two_goals();

        // Update learning goal 1 progess to 99.
        $result = mod_learninggoalwidget_external::update_user_progress(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[7]->id,
            $resultcourse[3]->id,
            $goalrecord1->id,
            99
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::update_user_progress_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            99,
            $goalrecord1->id,
            $result
        );

        // Update learning goal 1 progess to 50.
        $result = mod_learninggoalwidget_external::update_user_progress(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[7]->id,
            $resultcourse[3]->id,
            $goalrecord1->id,
            50
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_for_user_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            50,
            $goalrecord1->id,
            $result
        );

        // Update learning goal 2 progess to 100.
        $result = mod_learninggoalwidget_external::update_user_progress(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id,
            $resultcourse[7]->id,
            $resultcourse[3]->id,
            $goalrecord2->id,
            100
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_for_user_returns(), $result);

        $this->check_userprogress(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            100,
            $goalrecord2->id,
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
        $coursedata = $res[0];
        $course1 = $coursedata[0];
        $coursemodule = $coursedata[1];
        $widgetinstance = $coursedata[2];
        $topicrecord = $coursedata[3];
        $user1 = $coursedata[7];
        $goalrecord = $res[1];
        $cmcontext = \context_module::instance($coursemodule->id);
        $coursecontext = \context_course::instance($course1->id);
        $progress = 50;
        $timestamp = 12345678;

        mod_learninggoalwidget_external::update_user_progress(
            $course1->id,
            $coursemodule->id,
            $widgetinstance->id,
            $user1->id,
            $topicrecord->id,
            $goalrecord->id,
            $progress,
        );

        $eventparams = [];
        $eventname = "\\mod_learninggoalwidget\\event\\learninggoal_updated";
        $eventparams[1] = ["name" => "courseid", "value" => $course1->id];
        $eventparams[2] = ["name" => "coursemoduleid", "value" => $coursemodule->id];
        $eventparams[3] = ["name" => "instanceid", "value" => $widgetinstance->id];
        $eventparams[4] = ["name" => "userid", "value" => $user1->id];
        $eventparams[5] = ["name" => "timestamp", "value" => $timestamp];
        $eventparams[6] = ["name" => "goalname", "value" => $goalrecord->title];
        $eventparams[7] = ["name" => "goalprogress", "value" => $progress];

        // Update learning goal 2 progess to 50.
        $result = mod_learninggoalwidget_external::log_event(
            $course1->id,
            $coursemodule->id,
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
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);
        $resultcourse = $this->setup_course_with_topics(
            "Artificial Intelligence Basics Part 1",
            "AIBasics 1",
            "http://aibasics1.at",
            "Artificial Intelligence Basics Part 2",
            "AIBasics 2",
            "http://aibasics2.at"
        );

        // Get taxonomy.
        $result = mod_learninggoalwidget_external::get_taxonomy(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            $resultcourse[2]->id
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::get_taxonomy_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $expectedjson = new stdClass();
        $expectedjson->name = "Learning Goal's Taxonomy";
        $expectedjson->children = [
            [
                $resultcourse[5]->rank,
                $resultcourse[3]->id,
                "Artificial Intelligence Basics Part 1",
                "AIBasics 1",
                "http://aibasics1.at",
                [],
            ],
            [
                $resultcourse[6]->rank,
                $resultcourse[4]->id,
                "Artificial Intelligence Basics Part 2",
                "AIBasics 2",
                "http://aibasics2.at",
                [],
            ],
        ];
        $this->check_json($parsed, $expectedjson);
    }

    /**
     * testing add_taxonomy
     *
     * @return void
     */
    public function test_addtaxonomy() {
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $instance->id, $course->id);

        $taxonomy = (object) [
            "name" => "Learning Goal's Taxonomy",
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
            $course->id,
            $coursemodule->id,
            $instance->id,
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
            $expectedjson->children[] = [
                $parsed->children[$topicidx][0],
                $parsed->children[$topicidx][1],
                $topic->name,
                $topic->keyword,
                $topic->link,
                [],
            ];
            foreach ($topic->children as $goalidx => $goal) {
                $expectedjson->children[$topicidx][5][] = [
                    $parsed->children[$topicidx][5][$goalidx][0],
                    $parsed->children[$topicidx][5][$goalidx][1],
                    $goal->name,
                    $goal->keyword,
                    $goal->link,
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
        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $instance->id, $course->id);

        $taxonomy = (object) [
            "name" => "Learning Goal's Taxonomy",
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
            $course->id,
            $coursemodule->id,
            $instance->id,
            json_encode($taxonomy)
        );

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $result = mod_learninggoalwidget_external::delete_taxonomy(
            $course->id,
            $coursemodule->id,
            $instance->id,
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
     * @return void
     */

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
     * helper function testing updating a topic
     *
     * @param [string] $updatedtopicjson
     * @return array
     */
    protected function check_updatetopic_getgoals($updatedtopicjson) {
        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(mod_learninggoalwidget_external::update_topic_returns(), $updatedtopicjson);
        $parsed = json_decode($result);
        $topic = $parsed->children[0];
        return $topic[5];
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

        // Reset all changes automatically after this test.
        $this->resetAfterTest(true);

        $course1 = $this->getDataGenerator()->create_course();
        $widgetinstance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course1->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $widgetinstance->id, $course1->id);

        // Create topic in course.
        $topicrecord = new stdClass;
        $topicrecord->title = $topictitle;
        $topicrecord->shortname = $topicshortname;
        $topicrecord->url = $topicurl;
        $topicrecord->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord);

        // Link topic with widget instance.
        $topicinstancerecord = new stdClass;
        $topicinstancerecord->course = $course1->id;
        $topicinstancerecord->coursemodule = $coursemodule->id;
        $topicinstancerecord->instance = $widgetinstance->id;
        $topicinstancerecord->topic = $topicrecord->id;
        $topicinstancerecord->rank = 1;
        $topicinstancerecord->id = $DB->insert_record('learninggoalwidget_i_topics', $topicinstancerecord);

        return [$course1, $coursemodule, $widgetinstance, $topicrecord, $topicinstancerecord];
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
        $this->assertEquals("Learning Goal's Taxonomy", $parsed->name);

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
        $this->assertEquals("Learning Goal's Taxonomy", $parsed->name);

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
        $goalrecord1 = new stdClass;
        $goalrecord1->title = "Goal 1 under Topic 1";
        $goalrecord1->shortname = "Goal 1 shortname";
        $goalrecord1->url = "http://goal1.at";
        $goalrecord1->topic = $resultcourse[3]->id;
        $goalrecord1->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord1);

        // Link goal 1 with learning goal activity in a course.
        $goalinstancerecord1 = new stdClass;
        $goalinstancerecord1->course = $resultcourse[0]->id;
        $goalinstancerecord1->coursemodule = $resultcourse[1]->id;
        $goalinstancerecord1->instance = $resultcourse[2]->id;
        $goalinstancerecord1->topic = $resultcourse[3]->id;
        $goalinstancerecord1->goal = $goalrecord1->id;
        $goalinstancerecord1->rank = 1;
        $goalinstancerecord1->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord1);

        // Insert in goal 2.
        $goalrecord2 = new stdClass;
        $goalrecord2->title = "Goal 2 under Topic 1";
        $goalrecord2->shortname = "Goal 2 shortname";
        $goalrecord2->url = "http://goal2.at";
        $goalrecord2->topic = $resultcourse[3]->id;
        $goalrecord2->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord2);

        // Link goal 2 with learning goal activity in a course.
        $goalinstancerecord2 = new stdClass;
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
     * check learning goals
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

    /**
     * check some topic properties
     *
     * @param object $topic
     * @return array
     */
    protected function check_topic_properties($topic) {
        $this->assertNotNull($topic);

        $this->assertNotNull($topic->name);
        $this->assertNotEmpty($topic->name);
        $this->assertEquals("Learning Goal's Taxonomy", $topic->name);

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
}
