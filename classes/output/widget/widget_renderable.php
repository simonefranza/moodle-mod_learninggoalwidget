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

namespace mod_learninggoalwidget\output\widget;

use renderable;
use renderer_base;
use templatable;

/**
 * Learning Goal Widget Renderable
 *
 * @package   mod_learninggoalwidget
 * @copyright University of Technology Graz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class widget_renderable implements renderable, templatable {

    /**
     * course id
     *
     * @var int
     */
    private $courseid;

    /**
     * user id
     *
     * @var int
     */
    private $userid;

    /**
     * course module id
     *
     * @var int
     */
    private $coursemoduleid;

    /**
     * instance id
     *
     * @var int
     */
    private $instanceid;

    /**
     * ctor of widget_renderable
     *
     * @param [type] $courseid
     * @param [type] $userid
     * @param [type] $coursemoduleid
     * @param [type] $instanceid
     */
    public function __construct($courseid, $userid, $coursemoduleid, $instanceid) {
        $this->courseid = $courseid;
        $this->userid = $userid;
        $this->coursemoduleid = $coursemoduleid;
        $this->instanceid = $instanceid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param  \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {

        $contextvariables = [
            "courseid" => $this->courseid,
            "userid" => $this->userid,
            "coursemoduleid" => $this->coursemoduleid,
            "instanceid" => $this->instanceid,
            "contentview" => get_string('contentview', 'learninggoalwidget'),
            "examview" => get_string('examview', 'learninggoalwidget'),
            "progresslabel0" => get_string('progresslabel0', 'learninggoalwidget'),
            "progresslabel50" => get_string('progresslabel50', 'learninggoalwidget'),
            "progresslabel100" => get_string('progresslabel100', 'learninggoalwidget'),
            "progressdialogtitle" => get_string('progressdialogtitle', 'learninggoalwidget'),
            "progresslegendlabel" => get_string('progresslegendlabel', 'learninggoalwidget'),
            "sunburstthumbnail" => $output->image_url('icon', 'learninggoalwidget'),
            "treemapthumbnail" => $output->image_url('treemapthumbnail', 'learninggoalwidget'),
            "textualbulletpointlisttitle" => get_string('textualbulletpointlisttitle', 'learninggoalwidget'),
            "treemapaccessibilitytext" => get_string("treemapaccessibilitytext", 'learninggoalwidget'),
        ];
        return $contextvariables;
    }
}
