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
use stdClass;

/**
 * Learning Goal Taxonomy Test Utils
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait utils {
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
        $topic = new stdClass;
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
        $goal = new stdClass;
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

        // Create topic 1 in course.
        $topicrecord1 = insert_topic($widgetinstance->id, $topic1title, $topic1shortname, $topic1url, 1);

        // Create topic 2 in course.
        $topicrecord2 = insert_topic($widgetinstance->id, $topic2title, $topic2shortname, $topic2url, 2);

        return [$widgetinstance, $topicrecord1, $topicrecord2, $user1];
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
        $goalrecord = insert_record(
            $resultcourse[0]->id,
            $resultcourse[1]->id,
            "Goal under Topic 1 to be updated",
            "Goal 1 shortname to be updated",
            "http://goal1.updateme.at",
            1);

        return [...$resultcourse, $goalrecord];
    }
}
