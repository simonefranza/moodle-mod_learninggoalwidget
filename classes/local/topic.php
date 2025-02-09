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
 * Learning Goal Widget topic
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

use mod_learninggoalwidget\local\goal;

/**
 * Topics class
 *
 * a topic consists of a title, shortname and url
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class topic {
    use \mod_learninggoalwidget\local\shared;
    /**
     * Check that a topic is valid, i.e. it is a valid child and has topicid (int)
     *
     * @param stdClass $topic Topic to check
     * @return is topic valid
     */
    public static function validate_topic(&$topic) {
        self::validate_children_properties($topic);
        if (!(isset($topic->topicid) && is_int($topic->topicid))) {
            $topic->valid = false;
        } else if (!(isset($topic->children) && is_array($topic->children))) {
            $topic->valid = false;
        }
        return $topic->valid;
    }

    /**
     * Updates or inserts a topic into learninggoalwidget_topics
     *
     * @param int $lgwid ID of the LGW instance
     * @param stdClass $topic Topic to insert into the DB
     * @return id of the updated topic or -1
     */
    public static function update_topic($lgwid, $topic) {
        global $DB;
        if (!self::validate_topic($topic)) {
            // Topic is invalid.
            return -2;
        }

        // Ensure lgw instance exists.
        $params = [
            'id' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget', $params)) {
            return -1;
        }

        $topicnew = isset($topic->new) && $topic->new;
        $topicedit = isset($topic->edit) && $topic->edit;
        $newtopic = (object) [
            'learninggoalwidgetid' => $lgwid,
            'title' => $topic->name,
            'shortname' => $topic->keyword,
            'url' => $topic->link,
            'ranking' => $topic->ranking,
        ];

        if ($topicnew) {
            $newtopic->id = $DB->insert_record('learninggoalwidget_topics', $newtopic);
            return $newtopic->id;
        }
        // Topic should exist already, check if id exists.
        $params = [
            'id' => $topic->topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_topics', $params)) {
            return -1;
        }
        if ($topicedit) {
            $newtopic->id = $topic->topicid;
            $DB->update_record('learninggoalwidget_topics', $newtopic);
        }
        return $topic->topicid;
    }

    /**
     * Deletes a topic and all the goals and progress from the DB
     *
     * @param number $lgwid ID of the LGW instance
     * @param number $topicid ID of the topic to delete
     */
    public static function delete_topic($lgwid, $topicid) {
        global $DB;
        // Make sure it is valid topic.
        $params = [
            'id' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_topics', $params)) {
            return false;
        }

        // Delete all related information.
        $goalsparams = [
            'topicid' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];

        $DB->delete_records('learninggoalwidget_progs', $goalsparams);
        $DB->delete_records('learninggoalwidget_goals', $goalsparams);
        $DB->delete_records('learninggoalwidget_topics', $params);

        return true;
    }
}
