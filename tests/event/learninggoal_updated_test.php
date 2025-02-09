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
 * Learning Goal Taxonomy - Learninggoal Updated Event Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\event;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use mod_learninggoalwidget\event\learninggoal_updated;

/**
 * Learning Goal Taxonomy Goal Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class learninggoal_updated_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * testing class learninggoal_updated
     * @return void
     *
     * @covers \mod_learninggoalwidget\event\learninggoal_updated::init
     * @covers \mod_learninggoalwidget\event\learninggoal_updated::get_description
     * @covers \mod_learninggoalwidget\event\learninggoal_updated::get_name
     * @covers \mod_learninggoalwidget\event\learninggoal_updated::get_url
     */
    public function test_learninggoal_updated(): void {
        $res = $this->setup_widget();
        $lgwid = $res->instance->id;
        $userid = $res->user->id;

        $usercontext = \context_user::instance($userid);
        $eventparams = (object) [
          'instanceid' => $lgwid,
          'userid' => $userid,
          'timestamp' => 10,
        ];

        $params = [
            'contextid' => $usercontext->id,
            'relateduserid' => $userid,
            'other' => $eventparams,
            'userid' => $userid,
        ];

        $event = learninggoal_updated::create($params);
        $this->assertSame($event->get_description(), "The user with id '$userid' updated the progress of a learning goal");
        $this->assertSame($event->get_url(), null);
        $this->assertSame(learninggoal_updated::get_name(), "learninggoal_updated");
    }
}
