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
 * Learning Goal Taxonomy object
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

use stdClass;
use mod_learninggoalwidget\local\topic;

/**
 * Class userTaxonomy
 *
 * hierarchy of topics and goals with a user's progress
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userTaxonomy {

    /**
     * the course module id related with the taxonomy
     *
     * @var int
     */
    private $coursemoduleid;

    /**
     * the course id related with the taxonomy
     *
     * @var int
     */
    private $courseid;

    /**
     * the section id related with the taxonomy
     *
     * @var int
     */
    private $sectionid;

    /**
     * the instance id related with the taxonomy
     *
     * @var int
     */
    private $instanceid;

    /**
     * user id
     *
     * @var int
     */
    private $userid;

    /**
     * c'tor of taxonomy (for a specific instance in a course)
     *
     * @param int $coursemoduleid
     * @param int $courseid
     * @param int $sectionid
     * @param int $instanceid
     * @param int $userid
     */
    public function __construct($coursemoduleid, $courseid, $sectionid, $instanceid, $userid) {
        $this->coursemoduleid = $coursemoduleid;
        $this->courseid = $courseid;
        $this->sectionid = $sectionid;
        $this->instanceid = $instanceid;
        $this->userid = $userid;
    }

    /**
     * return json represenation of the taxonomy
     *
     * @return string
     */
    public function get_taxonomy_as_json(): string {
        $usertaxonomy = new stdClass;
        $usertaxonomy->name = get_string('title', 'mod_learninggoalwidget');
        $usertaxonomy->children = $this->get_topics();
        return json_encode($usertaxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * return the topics of the taxonomy
     *
     * @return array array of topic's, each an array itself [rank, id, title, shortname, url, goals]
     */
    private function get_topics() {
        $topics = [];
        if ($this->coursemoduleid !== null) {
            global $DB;
            $sqlstmt = "SELECT b.id, b.title, b.shortname, b.url, a.course,
                               a.coursemodule, a.instance, a.rank
                          FROM {learninggoalwidget_i_topics} a, {learninggoalwidget_topic} b
                         WHERE a.course = :courseid
                           AND a.coursemodule = :coursemoduleid
                           AND a.instance = :instanceid
                           AND a.topic = b.id
                           AND b.title != :initials
                           AND b.title != :goals
                      ORDER BY a.rank";
            $params = [
                'courseid' => $this->courseid,
                'coursemoduleid' => $this->coursemoduleid,
                'instanceid' => $this->instanceid,
                'initials' => "QUESTIONS_INITIAL",
                'goals' => "QUESTIONS_GOALS",
            ];
            $topicrecords = $DB->get_records_sql($sqlstmt, $params);
            foreach ($topicrecords as $topicrecord) {
                $topic = Topic::from_record($topicrecord);
                $goals = [];
                foreach ($topic->get_goals() as $goal) {
                    $obj = new stdClass;
                    $obj->name = $goal[2];
                    $obj->keyword = $goal[3];
                    $obj->link = $goal[4];
                    $obj->type = "goal";
                    $progress = userprogress::get_progress(
                        $this->courseid,
                        $this->coursemoduleid,
                        $this->instanceid,
                        $this->userid,
                        $topicrecord->id,
                        $goal[1]
                    );
                    $obj->pro = $progress;
                    $obj->goalid = $goal[1];
                    $goals[] = $obj;
                }
                $topics[] = [
                    "topicid" => $topicrecord->id, "name" => $topic->get_title(),
                    "keyword" => $topic->get_shortname(), "link" => $topic->get_url(), "type" => "topic",
                    "children" => $goals,
                ];
            }
        }
        return $topics;
    }
}
