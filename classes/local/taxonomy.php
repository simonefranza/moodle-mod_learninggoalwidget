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

namespace mod_learninggoalwidget\local;

use stdClass;
use mod_learninggoalwidget\local\topic;

/**
 * Class taxonomy
 *
 * hierarchy of topics and goals
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class taxonomy {

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
     * c'tor of taxonomy (for a specific instance in a course)
     *
     * @param int $coursemoduleid
     * @param int $courseid
     * @param int $sectionid
     * @param int $instanceid
     */
    public function __construct($coursemoduleid, $courseid, $sectionid, $instanceid) {
        $this->coursemoduleid = $coursemoduleid;
        $this->courseid = $courseid;
        $this->sectionid = $sectionid;
        $this->instanceid = $instanceid;
    }

    /**
     * return json represenation of the taxonomy
     *
     * @return string
     */
    public function get_taxonomy_as_json(): string {
        $taxonomy = new stdClass;
        $taxonomy->name = get_string('title', 'mod_learninggoalwidget');
        $taxonomy->children = $this->get_topics();
        return json_encode($taxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
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
                      ORDER BY a.rank";
            $params = [
                'courseid' => $this->courseid, 
                'coursemoduleid' => $this->coursemoduleid, 
                'instanceid' => $this->instanceid
            ];
            $topicrecords = $DB->get_records_sql($sqlstmt, $params);
            foreach ($topicrecords as $topicrecord) {
                $topic = Topic::from_record($topicrecord);
                $topics[] = [$topicrecord->rank, $topicrecord->id, $topic->get_title(), $topic->get_shortname(),
                $topic->get_url(), $topic->get_goals(), ];
            }
        }
        return $topics;
    }
}
