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
 * Learning Goal UserProgress object
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

/**
 * Class UserProgress
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userprogress {

    /**
     * get user's progress for a goal
     *
     * @param [type] $courseid
     * @param [type] $coursemoduleid
     * @param [type] $instanceid
     * @param [type] $userid
     * @param [type] $topicid
     * @param [type] $goalid
     * @return int progress 0 - 100
     */
    public static function get_progress($courseid, $coursemoduleid, $instanceid, $userid, $topicid, $goalid) {
        global $DB;

        $sqlstmt = "SELECT progress
                      FROM {learninggoalwidget_i_userpro}
                     WHERE course = :courseid
                       AND coursemodule = :coursemoduleid
                       AND instance = :instanceid
                       AND userid = :userid
                       AND topic = :topicid
                       AND goal = :goalid";
        $params = [
            'courseid' => $courseid,
            'coursemoduleid' => $coursemoduleid,
            'instanceid' => $instanceid,
            'userid' => $userid,
            'topicid' => $topicid,
            'goalid' => $goalid,
        ];
        $userprogressrecord = $DB->get_record_sql($sqlstmt, $params);
        if ($userprogressrecord) {
            return $userprogressrecord->progress;
        }
        // No record means no progress.
        return 0;
    }
}
