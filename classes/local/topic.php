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
     * title of the topic
     *
     * @var string
     */
    private $title;

    /**
     * shortname of the topic
     *
     * @var string
     */
    private $shortname;

    /**
     * url of the topic
     *
     * @var string
     */
    private $url;

    /**
     * array of goals related to the topic
     *
     * @var array
     */
    private $goals;

    /**
     * c'tor of a topic
     *
     * @param string $title
     * @param string $shortname
     * @param string $url
     * @param array $goals
     */
    public function __construct($title, $shortname, $url, $goals) {
        $this->title = $title;
        $this->shortname = $shortname;
        $this->url = $url;
        $this->goals = $goals;
    }

    /**
     * return the topic title
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * return the topic shortname
     *
     * @return string
     */
    public function get_shortname() {
        return $this->shortname;
    }

    /**
     * return the topic url
     *
     * @return string
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * return the topic goals
     *
     * @return array
     */
    public function get_goals() {
        return $this->goals;
    }

    /**
     * returns the topic DB entry given an id
     *
     * @param int $id
     * @return dbentry
     */
    public static function get_db_entry_by_id($id) {
        global $DB;
        $sqlstmt = "SELECT *
                      FROM {learninggoalwidget_topics}
                     WHERE id = :id";
        $params = [
            'id' => $id,
        ];
        return $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);
    }

    /**
     * returns the topic DB entry given the learninggoalwidgetid and ranking
     *
     * @param int $learninggoalwidgetid
     * @param int $ranking
     * @return dbentry
     */
    public static function get_db_entry_by_ranking($learninggoalwidgetid, $ranking) {
        global $DB;
        $sqlstmt = "SELECT *
                      FROM {learninggoalwidget_topics}
                     WHERE learninggoalwidgetid = :instance
                       AND ranking = :ranking";
        $params = [
            'instance' => $learninggoalwidgetid,
            'ranking' => $ranking,
        ];
        return $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);
    }

    /**
     * Check that a topic is valid, i.e. it is a valid child and has topicid (int)
     *
     * @param stdClass topic Topic to check
     * @returns is topic valid
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
     * @param int lgwid ID of the LGW instance
     * @param stdClass topic Topic to insert into the DB
     * @returns id of the updated topic or -1
     */
    public static function update_topic($lgwid, $topic) {
        global $DB;
        if (!self::validate_topic($topic)) {
            print_error("Topic is invalid");
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
        // Topic should exist already, check if id exists
        $params = [
            'id' => $topic->topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_topics', $params)) {
            return -1;
        }
        if ($topicedit)  {
          $newtopic->id = $topic->topicid;
          $DB->update_record('learninggoalwidget_topics', $newtopic);
        }
        return $topic->topicid;
    }

    /**
     * Deletes a topic and all the goals and progress from the DB
     *
     * @param number lgwid ID of the LGW instance
     * @param number topicid ID of the topic to delete
     */
    public static function delete_topic($lgwid, $topicid) {
        global $DB;
        // Make sure it is valid topic
        $params = [
            'id' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];
        if (!$DB->record_exists('learninggoalwidget_topics', $params)) {
            return;
        }

        // Delete all related information.
        $goals_params = [
            'topicid' => $topicid,
            'learninggoalwidgetid' => $lgwid,
        ];

        $DB->delete_records('learninggoalwidget_progs', $goals_params);
        $DB->delete_records('learninggoalwidget_goals', $goals_params);
        $DB->delete_records('learninggoalwidget_topics', $params);
    }
}
