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
 * Learning Goal Taxonomy - Taxonomy Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\output\widget;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/learninggoalwidget/tests/utils.php');

use mod_learninggoalwidget\output\widget\renderer;
use mod_learninggoalwidget\output\widget\widget_renderable;
use core_renderer;

/**
 * Learning Goal Taxonomy Widget Renderable Test
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 */
final class widget_renderable_test extends \advanced_testcase {
    use \mod_learninggoalwidget\utils;
    /**
     * Test rendering a widget.
     *
     * @covers \mod_learninggoalwidget\output\widget\renderer
     * @covers \mod_learninggoalwidget\output\widget\widget_renderable::__construct
     * @covers \mod_learninggoalwidget\output\widget\widget_renderable::export_for_template
     */
    public function test_render_widget() {
        $res = $this->setup_widget();
        $renderer = $this->get_renderer();
        // Create a mock of the renderable object
        $mockrenderable = $this->createMock(widget_renderable::class);

        // Mock the export_for_template method to return expected data
        $mockrenderable->method('export_for_template')->willReturn([
            'courseid' => 0,
            'coursemoduleid' => 0,
            'instanceid' => 0,
        ]);
        $output = $renderer->render_widget($mockrenderable);

        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('learninggoals-widget-', $output);

        $cm = get_coursemodule_from_id('learninggoalwidget', $res->instance->id);
        $widget = new widget_renderable(
            $res->course->id,
            $res->user->id,
            0,
            $res->instance->id
        );
        $widget->export_for_template($output);
    }

    /**
     * Helper function to get the renderer.
     */
    private function get_renderer() {
        global $PAGE;
        return $PAGE->get_renderer('mod_learninggoalwidget', 'widget');
    }
}
