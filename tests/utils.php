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
use mod_learninggoalwidget\local\taxonomy;
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
     * helper function creating an instance
     *
     * @return \stdClass
     */
    protected function setup_widget() {
        $this->setUp();

        $return = new \stdClass;
        $return->course = $this->getDataGenerator()->create_course();
        $return->user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($return->user->id, $return->course->id, 'editingteacher');
        $this->setUser($return->user);
        $return->instance = $this->getDataGenerator()->create_module('learninggoalwidget', ['course' => $return->course->id]);

        return $return;
    }

    /**
     * helper function to create a user with the desired role
     *
     * @param string $role of the user to be created
     * @param int $courseid id of the course where the user should be enrolled
     * @param bool $activate whether the user should be set as current user
     * @return \stdClass
     */
    protected function create_user($role, $courseid, $activate) {
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $courseid, $role);
        if ($activate) {
            $this->setUser($user);
        }

        return $user;
    }

    /**
     * helper function, create course with topics and two learning goals
     *
     * @param int $lgwid ID of LGW
     * @return array
     */
    protected function insert_two_goals($lgwid) {
        $taxonomy = new \stdClass;
        $taxonomy->name = 'name';
        $taxonomy->children = $this->create_taxonomy(2, 2);

        taxonomy::update_taxonomy($lgwid, $taxonomy);
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
                    'shortname' => 'T' . $i . 'G' . $ii,
                    'url' => 'http://topic' . $i . 'goal' . $ii . '.com',
                    'ranking' => $ii + 1,
                    'goalid' => $i * $numtopics + $ii,
                    'new' => true,
                ];
                $goals[] = $newgoal;
            }
            $newtopic = (object) [
                'name' => 'T' . $i,
                'shortname' => 'T' . $i,
                'url' => 'http://topic' . $i . '.com',
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
     * @param int $i Value to use for the check
     * @param int $newranking New ranking of the topic
     * @param int $numgoals Number of goals that the topic should contain
     * @param bool $checkgoals Whether to check the goals of the topic or not
     */
    private function check_topic($topic, $i, $newranking, $numgoals, $checkgoals) {
        $this->assertTrue(isset($topic->name) && is_string($topic->name));
        $this->assertSame($topic->name, 'T' . $i);
        $this->assertTrue(isset($topic->shortname) && is_string($topic->shortname));
        $this->assertSame($topic->shortname, 'T' . $i);
        $this->assertTrue(isset($topic->url) && is_string($topic->url));
        $this->assertSame($topic->url, 'http://topic' . $i . '.com');
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
     * @param int $i Topic-value to use for the check
     * @param int $ii Goal-value to use for the check
     * @param int $newranking New ranking
     */
    private function check_goal($goal, $i, $ii, $newranking = -2) {
        if ($newranking == -2) {
            $newranking = $ii + 1;
        }
        $this->assertTrue(isset($goal->name) && is_string($goal->name));
        $this->assertSame($goal->name, 'T' . $i . 'G' . $ii);
        $this->assertTrue(isset($goal->shortname) && is_string($goal->shortname));
        $this->assertSame($goal->shortname, 'T' . $i . 'G' . $ii);
        $this->assertTrue(isset($goal->url) && is_string($goal->url));
        $this->assertSame($goal->url, 'http://topic' . $i . 'goal' . $ii . '.com');
        $this->assertTrue(isset($goal->ranking) && is_int($goal->ranking));
        $this->assertSame($goal->ranking, $newranking);
        $this->assertTrue(isset($goal->goalid) && is_int($goal->goalid));
    }
}
