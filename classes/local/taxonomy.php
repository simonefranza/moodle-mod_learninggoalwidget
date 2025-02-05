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
 * Learning Goal Taxonomy
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
     * the instance id related with the taxonomy
     *
     * @var int
     */
    private $instanceid;

    /**
     * c'tor of taxonomy (for a specific instance in a course)
     *
     * @param int $instanceid
     */
    public function __construct($instanceid) {
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
     * @return array array of topic's, each an array itself [ranking, id, title, shortname, url, goals]
     */
    private function get_topics() {
        if ($this->instanceid === null) {
            return [];
        }
        $topics = [];
        global $DB;
        // CONCAT to create unique column
        $sqlstmt = "SELECT CONCAT(IFNULL(t.id, 'miss'), '-', IFNULL(g.id, 'miss')) as id,
                           g.id as gid, t.id as tid, t.learninggoalwidgetid,
                           t.title as ttitle, t.shortname as tshortname,
                           t.url as turl, t.ranking as tranking,
                           g.title as gtitle, g.shortname as gshortname,
                           g.url as gurl, g.ranking as granking
                      FROM {learninggoalwidget_topics} t
                 LEFT JOIN {learninggoalwidget_goals} g
                        ON t.id = g.topicid
                     WHERE t.learninggoalwidgetid = :instanceid
                  ORDER BY tranking, granking";
        $params = [
            'instanceid' => $this->instanceid,
        ];
        $topicrecords = $DB->get_records_sql($sqlstmt, $params);
        $numrecords = count($topicrecords);
        if ($numrecords === 0) {
          return [];
        }
        $numtopics = 0;
        foreach ($topicrecords as $topicrecord) {
            $topic;
            if (!$numtopics || $topics[$numtopics - 1]->topicid !== $topicrecord->tid) {
                $topic = new stdClass;
                $topic->topicid = $topicrecord->tid;
                $topic->name = $topicrecord->ttitle;
                $topic->keyword = $topicrecord->tshortname;
                $topic->link = $topicrecord->turl;
                $topic->type = "topic";
                $topic->children = [];
                $topics[] = $topic;
                $numtopics++;
            } else {
                $topic = $topics[$numtopics - 1];
            }
            if ($topicrecord->gid === null) {
                continue;
            }
            $goal = new stdClass;
            $goal->goalid = $topicrecord->gid;
            $goal->name = $topicrecord->gtitle;
            $goal->keyword = $topicrecord->gshortname;
            $goal->link = $topicrecord->gurl;
            $goal->type = "goal";
            $topic->children[] = $goal;
        }
        return $topics;
    }
}
