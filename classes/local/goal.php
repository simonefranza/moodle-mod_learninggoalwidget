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
 * Learning Goal object
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

/**
 * Class goal representation of a goal
 *
 * which consists of title (mandatory), shortname and a url (optional)
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class goal {
    use \mod_learninggoalwidget\local\shared;
    /**
     * Check that a goal is valid, i.e. it is a valid child and has goalid (int)
     *
     * @param stdClass $goal Goal to check
     * @return bool is goal valid
     */
    public static function validate_goal(&$goal) {
        self::validate_children_properties($goal);
        if (!(isset($goal->goalid) && is_int($goal->goalid))) {
            $goal->valid = false;
            $goal->idvalid = false;
        }
        return $goal->valid;
    }

    /**
     * Updates or inserts a goal into learninggoalwidget_goals
     * NOTE: Only call from taxonomy class. There are no capability checks in here
     *
     * @param int $lgwid ID of the LGW instance
     * @param int $topicid ID of the parent topic
     * @param stdClass $goal Goal to insert into the DB
     * @return int id of the updated goal or error
     */
    public static function update_goal($lgwid, $topicid, $goal) {
        global $DB;
        if (!self::validate_goal($goal)) {
            // Goal is invalid.
            return -2;
        }

        // Ensure parent topic exists.
        $params = [
            'id' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_topics', $params)) {
            return -1;
        }

        $goalnew = isset($goal->new) && $goal->new;
        $goaledit = isset($goal->edit) && $goal->edit;
        $newgoal = (object) [
            'learninggoalwidgetid' => $lgwid,
            'topicid' => $topicid,
            'title' => $goal->name,
            'shortname' => $goal->shortname,
            'url' => $goal->url,
            'ranking' => $goal->ranking,
        ];

        if ($goalnew) {
            $newgoal->id = $DB->insert_record('learninggoalwidget_goals', $newgoal);
            return $newgoal->id;
        }
        // Goal should exist already, check if id exists.
        $params = [
            'id' => $goal->goalid,
            'topicid' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_goals', $params)) {
            return -1;
        }
        if ($goaledit) {
            $newgoal->id = $goal->goalid;
            $DB->update_record('learninggoalwidget_goals', $newgoal);
        }
        return $goal->goalid;
    }

    /**
     * Deletes a goal and all the progresses from the DB
     * NOTE: Only call from taxonomy class. There are no capability checks in here
     *
     * @param int $lgwid ID of the LGW instance
     * @param int $topicid ID of the topic to delete
     * @param int $goalid ID of the goal to delete
     * @return bool whether deletion was successful or not
     */
    public static function delete_goal($lgwid, $topicid, $goalid): bool {
        global $DB;
        // Make sure it is valid goal.
        $params = [
            'id' => $goalid,
            'topicid' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_goals', $params)) {
            return false;
        }

        // Delete all related information.
        $goalsparams = [
            'goalid' => $goalid,
            'topicid' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];

        $DB->delete_records('learninggoalwidget_progs', $goalsparams);
        $DB->delete_records('learninggoalwidget_goals', $params);
        return true;
    }
}
