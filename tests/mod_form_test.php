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
 * Learning Goal Taxonomy Mod Form Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');
require_once($CFG->dirroot . '/mod/learninggoalwidget/mod_form.php');

use mod_learninggoalwidget_mod_form;
use mod_learninggoalwidget\local\taxonomy;

/**
 * Learning Goal Taxonomy Mod Form Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2023 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class mod_form_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing class mod_learninggoalwidget_mod_form
     * @return void
     *
     * @covers \mod_learninggoalwidget_mod_form::__construct
     * @covers \mod_learninggoalwidget_mod_form::definition
     */
    public function test_definition(): void {
        global $COURSE;
        $res = $this->setup_widget();
        $course = $res->course;
        $lgwid = $res->instance->id;

        $COURSE->id = $course->id;

        $cm = get_coursemodule_from_instance('learninggoalwidget', $lgwid, $course->id, true);

        $data = new \stdClass();
        $data->instance = $lgwid;
        $modform = new mod_learninggoalwidget_mod_form($data, $cm->sectionnum, $cm, $course);
        $this->assertDebuggingNotCalled();
        $modform->definition();
    }

    /**
     * testing taxonomy validation
     * @return void
     *
     * @covers \mod_learninggoalwidget_mod_form::__construct
     * @covers \mod_learninggoalwidget_mod_form::validation
     * @covers \mod_learninggoalwidget\local\taxonomy::reassign_rankings
     * @covers \mod_learninggoalwidget\local\taxonomy::validate_taxonomy
     * @covers \mod_learninggoalwidget\local\taxonomy::manage_taxonomy
     * @covers \mod_learninggoalwidget\local\taxonomy::update_topic_goals
     */
    public function test_validate_taxonomy(): void {
        global $COURSE;
        $res = $this->setup_widget();
        $course = $res->course;
        $lgwid = $res->instance->id;

        $COURSE->id = $course->id;

        $cm = get_coursemodule_from_instance('learninggoalwidget', $lgwid, $course->id, true);

        $data = new \stdClass();
        $data->instance = $lgwid;
        $modform = new mod_learninggoalwidget_mod_form($data, $cm->sectionnum, $cm, $course);

        $data = [
            'completionunlocked' => 1,
            'course' => $course->id,
            'coursemodule' => $cm->id,
            'cmidnumber' => '',
            'section' => $cm->section,
            'module' => $cm->module,
            'modulename' => 'learninggoalwidget',
            'add' => 0,
            'update' => 82,
            'return' => 0,
            'sr' => -1,
            'name' => 'LGW',
            'lang' => '',
            'instance' => $lgwid,
            'availabilityconditionsjson' => '{"op":"&","c":[],"showc":[]}',
        ];

        $this->validate_invalid_goals($data, $modform);
        $this->validate_invalid_topics($data, $modform);
    }

    /**
     * Helper function to check goals validation
     *
     * @param array $data for mod_form::validation
     * @param mod_learninggoalwidget_mod_form $modform modform object
     */
    private function validate_invalid_goals($data, $modform): void {
        // Topic without learning goals.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(1, 0);
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        $this->assertStringContainsString(
            get_string('validation:missinggoal', 'mod_learninggoalwidget', "'" . $taxonomy->children[0]->name . "'"),
            $errors['errorfield'],
        );

        // Unset goal props.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(1, 2);
        $goal1 = &$taxonomy->children[0]->children[0];
        $goal1name = $goal1->name;
        $goal2 = &$taxonomy->children[0]->children[1];
        $goal2name = $goal2->name;
        unset($goal1->shortname);
        unset($goal1->url);
        unset($goal1->ranking);
        unset($goal1->goalid);
        unset($goal2->shortname);
        unset($goal2->url);
        unset($goal2->ranking);
        unset($goal2->goalid);
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid shortname.
        $this->assertStringContainsString(
            get_string('validation:goal:shortnameinvalid', 'mod_learninggoalwidget', $goal1name),
            $errors['errorfield'],
        );
        $this->assertStringContainsString(
            get_string('validation:goal:shortnameinvalid', 'mod_learninggoalwidget', $goal2name),
            $errors['errorfield'],
        );

        // Invalid url.
        $this->assertStringContainsString(
            get_string('validation:goal:urlinvalid', 'mod_learninggoalwidget', $goal1name),
            $errors['errorfield'],
        );
        $this->assertStringContainsString(
            get_string('validation:goal:urlinvalid', 'mod_learninggoalwidget', $goal2name),
            $errors['errorfield'],
        );

        // Invalid ranking.
        $this->assertStringContainsString(
            get_string('validation:goal:rankinginvalid', 'mod_learninggoalwidget', $goal1name),
            $errors['errorfield'],
        );
        $this->assertStringContainsString(
            get_string('validation:goal:rankinginvalid', 'mod_learninggoalwidget', $goal2name),
            $errors['errorfield'],
        );

        // Invalid id.
        $this->assertStringContainsString(
            get_string('validation:goal:idinvalid', 'mod_learninggoalwidget', $goal1name),
            $errors['errorfield'],
        );
        $this->assertStringContainsString(
            get_string('validation:goal:idinvalid', 'mod_learninggoalwidget', $goal2name),
            $errors['errorfield'],
        );

        // Unset name.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(1, 1);
        $goal1 = &$taxonomy->children[0]->children[0];
        unset($goal1->name);
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid name.
        $this->assertStringContainsString(
            get_string('validation:goal:nameinvalid', 'mod_learninggoalwidget', '??'),
            $errors['errorfield'],
        );
    }

    /**
     * Helper function to check topics validation
     *
     * @param array $data for mod_form::validation
     * @param mod_learninggoalwidget_mod_form $modform modform object
     */
    private function validate_invalid_topics($data, $modform): void {
        // Add and delete goal.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(2, 1);
        $taxonomy->children[0]->children[0]->deleted = true;
        // No goals in second topic.
        $taxonomy->children[1]->children = [];
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // No children.
        $this->assertStringContainsString(
            get_string('validation:missinggoal',
                'mod_learninggoalwidget',
                "'" . $taxonomy->children[0]->name . "', '" . $taxonomy->children[1]->name . "'"
            ),
            $errors['errorfield'],
        );

        // Unset topic props.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(1, 2);
        $topic1 = &$taxonomy->children[0];
        $topic1name = $topic1->name;
        unset($topic1->shortname);
        unset($topic1->url);
        unset($topic1->ranking);
        unset($topic1->topicid);
        unset($topic1->children);
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid shortname.
        $this->assertStringContainsString(
            get_string('validation:topic:shortnameinvalid', 'mod_learninggoalwidget', $topic1name),
            $errors['errorfield'],
        );

        // Invalid url.
        $this->assertStringContainsString(
            get_string('validation:topic:urlinvalid', 'mod_learninggoalwidget', $topic1name),
            $errors['errorfield'],
        );

        // Invalid ranking.
        $this->assertStringContainsString(
            get_string('validation:topic:rankinginvalid', 'mod_learninggoalwidget', $topic1name),
            $errors['errorfield'],
        );

        // Invalid id.
        $this->assertStringContainsString(
            get_string('validation:topic:idinvalid', 'mod_learninggoalwidget', $topic1name),
            $errors['errorfield'],
        );

        // Unset topic name.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(1, 2);
        $topic1 = &$taxonomy->children[0];
        unset($topic1->name);
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid name.
        $this->assertStringContainsString(
            get_string('validation:topic:nameinvalid', 'mod_learninggoalwidget', '??'),
            $errors['errorfield'],
        );

        // Delete topic.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(2, 1);
        $taxonomy->children[0]->deleted = true;
        $taxonomy->children[0]->children[0]->deleted = true;
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid name.
        $this->assertFalse(array_key_exists('errorfield', $errors));

        // Empty taxonomy.
        $taxonomy = new \stdClass;
        $taxonomy->children = [];
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid name.
        $this->assertFalse(array_key_exists('errorfield', $errors));

        // Empty taxonomy.
        $taxonomy = new \stdClass;
        $taxonomy->children = $this->create_taxonomy(2, 1);
        $taxonomy->children[0] = [];
        $taxonomy->children[1]->children[0] = [];
        $data['taxonomy'] = json_encode($taxonomy);
        $errors = $modform->validation($data, []);

        // Invalid name.
        $this->assertStringContainsString(
            get_string('validation:topic:nameinvalid', 'mod_learninggoalwidget', '??'),
            $errors['errorfield'],
        );
        $this->assertStringContainsString(
            get_string('validation:goal:nameinvalid', 'mod_learninggoalwidget', '??'),
            $errors['errorfield'],
        );
        taxonomy::manage_taxonomy($data['instance'], $taxonomy);
    }
}
