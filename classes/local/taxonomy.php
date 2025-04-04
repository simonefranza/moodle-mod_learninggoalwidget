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
use mod_learninggoalwidget\local\goal;

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
     * return json represenation of the taxonomy
     *
     * @param int $lgwid id of the instance
     * @return string
     */
    public static function get_taxonomy_as_json($lgwid): string {
        global $DB;
        $taxonomy = new stdClass;
        $taxonomy->name = '';
        $taxonomy->children = [];
        if (!$lgwid) {
            return json_encode($taxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        }

        // Capability check.
        $cm = get_coursemodule_from_instance('learninggoalwidget', $lgwid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        require_capability('mod/learninggoalwidget:view', $context);

        $instance = $DB->get_record('learninggoalwidget', ['id' => $lgwid]);

        $taxonomy->name = $instance->name;
        $taxonomy->children = self::get_topics($lgwid);
        return json_encode($taxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * return the topics of the taxonomy
     *
     * @param int $lgwid id of the lgw instance
     * @return array array of topic's, each an array itself [ranking, id, title, shortname, url, goals]
     */
    private static function get_topics($lgwid) {
        $topics = [];
        global $DB;
        // CONCAT to create unique column.
        $sqlstmt = "SELECT CONCAT(COALESCE(t.id, -1), '-', COALESCE(g.id, -1)) AS id,
                           g.id AS gid, t.id AS tid, t.learninggoalwidgetid,
                           t.title AS ttitle, t.shortname AS tshortname,
                           t.url AS turl, t.ranking AS tranking,
                           g.title AS gtitle, g.shortname AS gshortname,
                           g.url AS gurl, g.ranking AS granking
                      FROM {learninggoalwidget_topics} t
                 LEFT JOIN {learninggoalwidget_goals} g
                        ON t.id = g.topicid
                     WHERE t.learninggoalwidgetid = :lgwid
                  ORDER BY tranking, granking";
        $params = [
            'lgwid' => $lgwid,
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
            $topic->children[] = $goal;
        }
        return $topics;
    }

    /**
     * Sorts an array by ranking
     *
     * @param array $array Array to sort
     */
    public static function sort_by_ranking(&$array) {
        usort($array, function ($a, $b) {
            return $a->ranking <=> $b->ranking;
        });
    }

    /**
     * Reassigns rankings to the elements in the array making sure they start from 1
     * and are contiguous
     *
     * @param array $array Array with the elements to reassing the rankings to
     */
    public static function reassign_rankings(&$array) {
        // Start ranking from 1.
        $ranking = 1;
        foreach ($array as &$child) {
            if (isset($child->valid) && !$child->valid) {
                $child->ranking = -1;
            }
            if ($child->ranking !== -1) {
                $child->ranking = $ranking++;
            }
        }
    }

    /**
     * Validates the taxonomy, by checking that all topics and goals are valid.
     * It also reassings the rankings to make sure
     * they are contiguous
     *
     * @param stdClass $taxonomy Taxonomy to validate
     */
    public static function validate_taxonomy(&$taxonomy) {
        if (!property_exists($taxonomy, 'children') || !is_array($taxonomy->children)) {
            $taxonomy->children = [];
        }

        for ($i = count($taxonomy->children) - 1; $i >= 0; $i--) {
            $topic = &$taxonomy->children[$i];
            // Validate topic.
            if (!is_object($topic)) {
                $taxonomy->children[$i] = new stdClass;
                $taxonomy->children[$i]->valid = false;
                $taxonomy->children[$i]->namevalid = false;
                $taxonomy->children[$i]->ranking = -1;
                $taxonomy->children[$i]->children = [];
                continue;
            }
            if (!topic::validate_topic($topic)) {
                // Topic is not valid. Skip.
                $topic->ranking = -1;
                continue;
            }

            for ($ii = count($topic->children) - 1; $ii >= 0; $ii--) {
                $goal = &$topic->children[$ii];
                // Validate goals.
                if (!is_object($goal)) {
                    $topic->children[$ii] = new stdClass;
                    $topic->children[$ii]->valid = false;
                    $topic->children[$ii]->namevalid = false;
                    $topic->children[$ii]->ranking = -1;
                    continue;
                }
                if (!goal::validate_goal($goal)) {
                    // Goal is not valid.
                    $goal->ranking = -1;
                }
            }
            self::sort_by_ranking($topic->children);
            self::reassign_rankings($topic->children);
        }
        self::sort_by_ranking($taxonomy->children);
        self::reassign_rankings($taxonomy->children);
    }

    /**
     * Updates the taxonomy in the DB given an ID and a new taxonomy
     *
     * @param int $lgwid Instance id to update
     * @param stdClass $taxonomy New taxonomy
     */
    public static function update_taxonomy($lgwid, &$taxonomy) {
        // Capability check.
        $cm = get_coursemodule_from_instance('learninggoalwidget', $lgwid, 0, false, MUST_EXIST);
        require_capability('mod/learninggoalwidget:addinstance', \context_module::instance($cm->id));

        self::manage_taxonomy($lgwid, $taxonomy);
    }
    /**
     * Manages the taxonomy in the DB given an ID and a new taxonomy
     * without capability check because used by "add_instance"
     *
     * @param int $lgwid Instance id to update
     * @param stdClass $taxonomy New taxonomy
     */
    public static function manage_taxonomy($lgwid, &$taxonomy) {
        // Capability check.
        self::validate_taxonomy($taxonomy);

        // Add all topics and goals to db.
        foreach ($taxonomy->children as $topic) {
            // Check if topic is deleted or added.
            $topicvalid = !isset($topic->valid) || $topic->valid;
            // Topic isn't valid. Skip.
            if (!$topicvalid) {
                continue;
            }

            $topicdeleted = isset($topic->deleted) && $topic->deleted;
            $topicnew = isset($topic->new) && $topic->new;

            // New + deleted = was never in the DB -> ignore.
            if ($topicdeleted && $topicnew) {
                continue;
            }
            if ($topicdeleted) {
                topic::delete_topic($lgwid, $topic->topicid);
                continue;
            }

            // Add/update topic.
            $topic->id = topic::update_topic($lgwid, $topic);
            self::update_topic_goals($lgwid, $topic);
        }
    }

    /**
     * Updates the taxonomy in the DB given the children of a topic
     *
     * @param int $lgwid Instance id to update
     * @param stdClass $topic Topic whose goals should be added
     */
    private static function update_topic_goals($lgwid, &$topic) {
        foreach ($topic->children as $goal) {
            $goalvalid = !isset($goal->valid) || $goal->valid;
            // Goal isn't valid. Skip.
            if (!$goalvalid) {
                continue;
            }
            // Check if goal is deleted or added.
            $goaldeleted = isset($goal->deleted) && $goal->deleted;
            $goalnew = isset($goal->new) && $goal->new;

            // New + deleted = was never in the DB -> ignore.
            if ($goaldeleted && $goalnew) {
                continue;
            }
            if ($goaldeleted) {
                goal::delete_goal($lgwid, $topic->id, $goal->goalid);
                continue;
            }

            // Add goal to db.
            $goal->id = goal::update_goal($lgwid, $topic->id, $goal);
        }
    }

}
