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
        $topicrecord1 = new stdClass;
        $topicrecord1->title = $topic1title;
        $topicrecord1->shortname = $topic1shortname;
        $topicrecord1->url = $topic1url;
        $topicrecord1->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord1);

        // Link topic 1 with widget instance.
        $topicinstancerecord1 = new stdClass;
        $topicinstancerecord1->course = $course1->id;
        $topicinstancerecord1->coursemodule = $coursemodule->id;
        $topicinstancerecord1->instance = $widgetinstance->id;
        $topicinstancerecord1->topic = $topicrecord1->id;
        $topicinstancerecord1->rank = 1;
        $topicinstancerecord1->id = $DB->insert_record('learninggoalwidget_i_topics', $topicinstancerecord1);

        // Create topic 2 in course.
        $topicrecord2 = new stdClass;
        $topicrecord2->title = $topic2title;
        $topicrecord2->shortname = $topic2shortname;
        $topicrecord2->url = $topic2url;
        $topicrecord2->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord2);

        // Link topic 2 with widget instance.
        $topicinstancerecord2 = new stdClass;
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
        $goalrecord = new stdClass;
        $goalrecord->title = "Goal under Topic 1 to be updated";
        $goalrecord->shortname = "Goal 1 shortname to be updated";
        $goalrecord->url = "http://goal1.updateme.at";
        $goalrecord->topic = $resultcourse[3]->id;
        $goalrecord->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord);

        // Link goal with learning goal activity in a course.
        $goalinstancerecord = new stdClass;
        $goalinstancerecord->course = $resultcourse[0]->id;
        $goalinstancerecord->coursemodule = $resultcourse[1]->id;
        $goalinstancerecord->instance = $resultcourse[2]->id;
        $goalinstancerecord->topic = $resultcourse[3]->id;
        $goalinstancerecord->goal = $goalrecord->id;
        $goalinstancerecord->rank = 1;
        $goalinstancerecord->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord);

        return [$resultcourse, $goalrecord, $goalinstancerecord];
    }
}
