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
 * Learning Goal user Taxonomy object
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
     * return json represenation of the taxonomy
     *
     * @param int $lgwid id of the instance
     * @return string
     */
    public static function get_taxonomy_as_json($lgwid): string {
        global $DB, $USER;

        if ($lgwid === null) {
            return '{}';
        }

        // Capability check.
        $userid = $USER->id;
        $cm = get_coursemodule_from_instance('learninggoalwidget', $lgwid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        require_capability('mod/learninggoalwidget:view', $context);

        $isstudent = has_capability('mod/learninggoalwidget:updateprogress', $context);

        $instance = $DB->get_record('learninggoalwidget', ['id' => $lgwid]);

        $usertaxonomy = new stdClass;
        $usertaxonomy->name = $instance->name;
        $usertaxonomy->student = $isstudent;
        $usertaxonomy->children = self::get_topics($lgwid, $userid, $isstudent);
        return json_encode($usertaxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * return the topics of the taxonomy
     *
     * @param int $lgwid id of the instance
     * @param int $userid id of the user
     * @param bool $isstudent Whether the user is a student or not
     * @return array array of topic's, each an array itself [ranking, id, title, shortname, url, goals]
     */
    private static function get_topics($lgwid, $userid, $isstudent) {
        $topics = [];
        global $DB;
        // CONCAT to create unique column.
        $sqlstmt = "SELECT CONCAT(COALESCE(t.id, -1), '-', COALESCE(g.id, -1), '-', COALESCE(p.id, -1)) AS id,
                           t.id AS tid, t.learninggoalwidgetid,
                           t.title AS ttitle, t.shortname AS tshortname,
                           t.url AS turl, t.ranking AS tranking,
                           g.id AS gid, g.title AS gtitle, g.shortname AS gshortname,
                           g.url AS gurl, g.ranking AS granking,
                           p.userid, p.progress
                      FROM {learninggoalwidget_topics} t
                 LEFT JOIN {learninggoalwidget_goals} g
                        ON t.id = g.topicid
                 LEFT JOIN {learninggoalwidget_progs} p
                        ON g.id = p.goalid AND p.userid = :userid
                     WHERE t.learninggoalwidgetid = :lgwid
                  ORDER BY tranking, granking";
        $params = [
            'lgwid' => $lgwid,
            'userid' => $userid,
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
                $topic->shortname = $topicrecord->tshortname;
                $topic->url = $topicrecord->turl;
                $topic->ranking = $topicrecord->tranking;
                $topic->type = 'topic';
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
            $goal->shortname = $topicrecord->gshortname;
            $goal->url = $topicrecord->gurl;
            $goal->ranking = $topicrecord->granking;
            $goal->type = 'goal';
            if ($isstudent) {
                $goal->pro = $topicrecord->progress ?? 0;
            }
            $topic->children[] = $goal;
        }
        return $topics;
    }
}
