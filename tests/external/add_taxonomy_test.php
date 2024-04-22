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
 * Unit tests for the add_taxonomy function.
 *
 * @package    mod_learninggoalwidget
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use externallib_advanced_testcase;
use core_external\external_api;

/**
 * Unit tests for the add_taxonomy function.
 *
 * @package    mod_learninggoalwidget
 * @category   external
 * @copyright  2023 Know Center GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
class add_taxonomy_test extends externallib_advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test add_taxonomy
     * @return void
     *
     * @covers \mod_learninggoalwidget\external\add_taxonomy::execute
     * @covers \mod_learninggoalwidget\external\add_taxonomy::execute_returns
     * @covers \mod_learninggoalwidget\external\add_taxonomy::execute_parameters
     */
    public function test_add_taxonomy(): void {
        $this->setUp();

        $course = $this->getDataGenerator()->create_course();
        $instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $coursemodule = get_coursemodule_from_instance('learninggoalwidget', $instance->id, $course->id);

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
        $result = add_taxonomy::execute(
            $course->id,
            $coursemodule->id,
            $instance->id,
            json_encode($taxonomy)
        );

        // We need to execute the return values cleaning process to simulate the web service server.
        $result = external_api::clean_returnvalue(add_taxonomy::execute_returns(), $result);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result);
        $parsed = json_decode($result);

        $expectedjson = new \stdClass();
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
}
