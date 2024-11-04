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
 * Web Service API
 *
 * @package   mod_learninggoalwidget
 * @category  external
 * @copyright 2021 Know Cener GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/classes/event/learninggoal_updated.php');

use mod_learninggoalwidget\local\topic;
use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\userTaxonomy;

/**
 * Web Service API
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Cener GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_learninggoalwidget_external extends external_api {
    /**
     * return type definition
     *
     * @return external_value
     */
    public static function get_taxonomy_returns() {
        return new external_value(PARAM_TEXT, 'taxonomy for user in json format');
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function get_taxonomy_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * get the taxonomy
     *
     * @param int $instance
     * @return string
     */
    public static function get_taxonomy(
        $instance
    ) {
        // Parameter validation.
        self::validate_parameters(
            self::get_taxonomy_parameters(),
            [
                'instance' => $instance,
            ]
        );
        return (new taxonomy($instance))->get_taxonomy_as_json();
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function get_taxonomy_for_user_parameters() {
        return new external_function_parameters(
            [
                'instanceid' => new external_value(PARAM_INT, ''),
                'userid' => new external_value(PARAM_INT, 'ID of the logged in user'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function get_taxonomy_for_user_returns() {
        return new external_value(PARAM_TEXT, 'taxonomy for user in json format');
    }

    /**
     * get learning goal taxonomy as json
     *
     * @param number $instanceid
     * @param number $userid
     * @return void
     */
    public static function get_taxonomy_for_user(
        $instanceid,
        $userid
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::get_taxonomy_for_user_parameters(),
            [
                'instanceid' => $instanceid,
                'userid' => $userid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        return (new userTaxonomy($instanceid, $userid))->get_taxonomy_as_json();
    }


    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function update_user_progress_parameters() {
        return new external_function_parameters(
            [
                'instanceid' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'userid' => new external_value(PARAM_INT, 'ID of the user'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
                'progress' => new external_value(PARAM_INT, 'progress value for the learning goal'),
            ]
        );
    }
    /**
     * return type definition
     *
     * @return external_value
     */
    public static function update_user_progress_returns() {
        return new external_value(PARAM_TEXT, 'the taxonomy with the updated learning goal progress for a user');
    }


    /**
     * update user's progress for a goal
     *
     * @param number $instanceid
     * @param number $userid
     * @param number $topicid
     * @param number $goalid
     * @param number $progress
     * @return void
     */
    public static function update_user_progress(
        $instanceid,
        $userid,
        $topicid,
        $goalid,
        $progress
    ) {

        global $USER, $DB;

        // Parameter validation.
        self::validate_parameters(
            self::update_user_progress_parameters(),
            [
                'instanceid' => $instanceid,
                'userid' => $userid,
                'topicid' => $topicid,
                'goalid' => $goalid,
                'progress' => $progress,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $sqlstmt = "SELECT id
                      FROM {learninggoalwidget_progs}
                     WHERE learninggoalwidgetid = :instanceid
                       AND userid = :userid
                       AND topicid = :topicid
                       AND goalid = :goalid";
        $params = [
            'instanceid' => $instanceid,
            'userid' => $userid,
            'topicid' => $topicid,
            'goalid' => $goalid,
        ];
        $userprogressrecord = $DB->get_record_sql($sqlstmt, $params);

        $userprogress = new stdClass;
        $userprogress->progress = $progress;

        if ($userprogressrecord) {
            $userprogress->id = $userprogressrecord->id;
            $DB->update_record('learninggoalwidget_progs', $userprogress);
        } else {
            $userprogress->learninggoalwidgetid = $instanceid;
            $userprogress->topicid = $topicid;
            $userprogress->goalid = $goalid;
            $userprogress->userid = $userid;
            $DB->insert_record('learninggoalwidget_progs', $userprogress);
        }

        return self::get_taxonomy_for_user($instanceid, $userid);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function log_event_parameters() {
        return new external_function_parameters(
            [
                'instanceid' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'userid' => new external_value(PARAM_INT, 'ID of the user'),
                'eventparams' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            "name" => new external_value(PARAM_TEXT, 'name'),
                            "value" => new external_value(PARAM_TEXT, 'keyword'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function log_event_returns() {
        return new external_value(PARAM_INT, 'true if storing the event succeeded');
    }

    /**
     * save an event in the moodle logstore
     *
     * @param [type] $instanceid
     * @param [type] $userid
     * @param [type] $eventparams
     * @return void
     */
    public static function log_event(
        $instanceid,
        $userid,
        $eventparams
    ) {

        // Parameter validation.
        $params = self::validate_parameters(
            self::log_event_parameters(),
            [
                'instanceid' => $instanceid,
                'userid' => $userid,
                'eventparams' => $eventparams,
            ]
        );

        self::validate_context(context_user::instance($userid));

        $usercontext = context_user::instance($userid);

        // Left out 'courseid' => $courseid, because it was causing problems.
        $params = [
            'contextid' => $usercontext->id,
            'relateduserid' => $userid,
            'other' => $eventparams,
            'userid' => $userid,
        ];

        $eventclass = 'mod_learninggoalwidget\event\learninggoal_updated';
        $event = $eventclass::create($params);
        $event->trigger();

        return true;
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function insert_topic_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicname' => new external_value(PARAM_TEXT, 'topic name'),
                'topicshortname' => new external_value(PARAM_TEXT, 'topic shortname'),
                'topicurl' => new external_value(PARAM_TEXT, 'topic url'),
            ]
        );
    }
    /**
     * return type definition
     *
     * @return external_value
     */
    public static function insert_topic_returns() {
        return new external_value(PARAM_TEXT, 'learning goals taxonomy');
    }

    /**
     * Insert a new topic in the topic table and reference it with course and ranking from topic instance table
     * without checks, for internal use
     *
     * @param  number $instance
     * @param  string $topicname
     * @param  string $topicshortname
     * @param  string $topicurl
     * @return id the id of the added topic
     */
    public static function add_topic(
        $instance,
        $topicname,
        $topicshortname,
        $topicurl
    ) {
        global $DB;

        // Find max existing ranking.
        $sqlstmt = "SELECT MAX(ranking) as maxranking
                      FROM {learninggoalwidget_topics}
                     WHERE learninggoalwidgetid = :instance";
        $params = [
            'instance' => $instance,
        ];
        $maxrankingrecord = $DB->get_record_sql($sqlstmt, $params);

        // Insert in topic table.
        $topicrecord = new stdClass;
        $topicrecord->learninggoalwidgetid = $instance;
        $topicrecord->title = $topicname;
        $topicrecord->shortname = $topicshortname;
        $topicrecord->url = $topicurl;
        $topicrecord->ranking = $maxrankingrecord ? $maxrankingrecord->maxranking + 1 : 1;

        $topicrecord->id = $DB->insert_record('learninggoalwidget_topics', $topicrecord);

        return $topicrecord->id;
    }

    /**
     * Insert a new topic in the topic table and reference it with course and ranking from topic instance table
     *
     * @param  [type] $instance
     * @param  [type] $topicname
     * @param  [type] $topicshortname
     * @param  [type] $topicurl
     * @return void
     */
    public static function insert_topic(
        $instance,
        $topicname,
        $topicshortname,
        $topicurl
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::insert_topic_parameters(),
            [
                'instance' => $instance,
                'topicname' => $topicname,
                'topicshortname' => $topicshortname,
                'topicurl' => $topicurl,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        self::add_topic($instance, $topicname, $topicshortname, $topicurl);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function update_topic_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'topicname' => new external_value(PARAM_TEXT, 'topic name'),
                'topicshortname' => new external_value(PARAM_TEXT, 'topic shortname'),
                'topicurl' => new external_value(PARAM_TEXT, 'topic url'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function update_topic_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * Update a topic in the topic table
     *
     * @param  number $instance
     * @param  number $topicid
     * @param  string $topicname
     * @param  string $topicshortname
     * @param  string $topicurl
     * @return void
     */
    public static function update_topic(
        $instance,
        $topicid,
        $topicname,
        $topicshortname,
        $topicurl
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::update_topic_parameters(),
            [
                'instance' => $instance,
                'topicid' => $topicid,
                'topicname' => $topicname,
                'topicshortname' => $topicshortname,
                'topicurl' => $topicurl,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        // Update in topic table.
        $topicrecord = new stdClass;
        $topicrecord->id = $topicid;
        $topicrecord->learninggoalwidgetid = $instance;
        $topicrecord->title = $topicname;
        $topicrecord->shortname = $topicshortname;
        $topicrecord->url = $topicurl;
        $DB->update_record('learninggoalwidget_topics', $topicrecord);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function delete_topic_parameters() {
        return new external_function_parameters(
            [
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function delete_topic_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * delete a topic (including related goals and progress) from the taxonomy
     *
     * @param number $topicid
     * @return void
     */
    public static function delete_topic(
        $topicid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::delete_topic_parameters(),
            [
                'topicid' => $topicid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $params = [
            'topicid' => $topicid,
        ];
        $DB->delete_records('learninggoalwidget_progs', $params);
        $DB->delete_records('learninggoalwidget_goal', $params);
        $DB->delete_records('learninggoalwidget_topic', ['id' => $topicid]);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function moveup_topic_parameters() {
        return new external_function_parameters(
            [
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function moveup_topic_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * move the topic before the preceding one (decrease ranking)
     *
     * @param int $topicid
     * @return void
     */
    public static function moveup_topic(
        $topicid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::moveup_topic_parameters(),
            [
                'topicid' => $topicid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $topicmoveup = topic::get_db_entry_by_id($topicid);

        if ($topicmoveup->ranking == '1') {
            // No need to update, as it's already lowest rank.
            return self::get_taxonomy($topicmoveup->learninggoalwidgetid);
        }
        $topicmovedown = topic::get_db_entry_by_ranking($topicmoveup->learninggoalwidgetid, $topicmoveup->ranking - 1);

        $topicmoveup->ranking--;
        $topicmovedown->ranking++;
        $DB->update_record('learninggoalwidget_topics', $topicmoveup);
        $DB->update_record('learninggoalwidget_topics', $topicmovedown);

        return self::get_taxonomy($topicmoveup->learninggoalwidgetid);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function movedown_topic_parameters() {
        return new external_function_parameters(
            [
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function movedown_topic_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * move topic behind the succeeding one (increase ranking)
     *
     * @param int $topicid
     * @return string
     */
    public static function movedown_topic(
        $topicid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::movedown_topic_parameters(),
            [
                'topicid' => $topicid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $topicmovedown = topic::get_db_entry_by_id($topicid);

        // Find highest rank.
        $sqlstmt = "SELECT MAX(ranking) as maxranking
                      FROM {learninggoalwidget_topics}
                     WHERE learninggoalwidgetid = :instance";
        $params = [
            'instance' => $topicmovedown->learninggoalwidgetid,
        ];
        $recordresult = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        if (!$recordresult || $topicmovedown->ranking === $recordresult->maxranking) {
            // No need to update, as it's already highest rank.
            return self::get_taxonomy($topicmovedown->learninggoalwidgetid);
        }
        $topicmoveup = topic::get_db_entry_by_ranking($topicmovedown->learninggoalwidgetid, $topicmovedown->ranking + 1);

        $topicmovedown->ranking++;
        $topicmoveup->ranking--;
        $DB->update_record('learninggoalwidget_topics', $topicmovedown);
        $DB->update_record('learninggoalwidget_topics', $topicmoveup);

        return self::get_taxonomy($topicmovedown->learninggoalwidgetid);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function insert_goal_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalname' => new external_value(PARAM_TEXT, 'goal name'),
                'goalshortname' => new external_value(PARAM_TEXT, 'goal shortname'),
                'goalurl' => new external_value(PARAM_TEXT, 'goal url'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function insert_goal_returns() {
        return new external_value(PARAM_TEXT, 'learning goals taxonomy');
    }

    /**
     * insert a new goal, internal use only
     *
     * @param  [type] $instance
     * @param  [type] $topicid
     * @param  [type] $goalname
     * @param  [type] $goalshortname
     * @param  [type] $goalurl
     * @return id the id of the added goal
     */
    public static function add_goal(
        $instance,
        $topicid,
        $goalname,
        $goalshortname,
        $goalurl
    ) {
        global $DB;

        // Find max existing ranking.
        $sqlstmt = "SELECT MAX(ranking) as maxranking
                      FROM {learninggoalwidget_goals}
                     WHERE learninggoalwidgetid = :instance";
        $params = [
            'instance' => $instance,
        ];
        $maxrankingrecord = $DB->get_record_sql($sqlstmt, $params);

        // Insert in goal table.
        $goalrecord = new stdClass;
        $goalrecord->learninggoalwidgetid = $instance;
        $goalrecord->topicid = $topicid;
        $goalrecord->title = $goalname;
        $goalrecord->shortname = $goalshortname;
        $goalrecord->url = $goalurl;
        $goalrecord->ranking = $maxrankingrecord ? $maxrankingrecord->maxranking + 1 : 1;

        $goalrecord->id = $DB->insert_record('learninggoalwidget_goals', $goalrecord);

        return $goalrecord->id;
    }


    /**
     * insert a new goal
     *
     * @param  [type] $instance
     * @param  [type] $topicid
     * @param  [type] $goalname
     * @param  [type] $goalshortname
     * @param  [type] $goalurl
     * @return void
     */
    public static function insert_goal(
        $instance,
        $topicid,
        $goalname,
        $goalshortname,
        $goalurl
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::insert_goal_parameters(),
            [
                'instance' => $instance,
                'topicid' => $topicid,
                'goalname' => $goalname,
                'goalshortname' => $goalshortname,
                'goalurl' => $goalurl,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        self::add_goal($instance, $topicid, $goalname, $goalshortname, $goalurl);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function update_goal_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
                'goalname' => new external_value(PARAM_TEXT, 'goal name'),
                'goalshortname' => new external_value(PARAM_TEXT, 'goal shortname'),
                'goalurl' => new external_value(PARAM_TEXT, 'goal url'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function update_goal_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * Update a goal in the topic table
     *
     * @param [type] $instance
     * @param [type] $goalid
     * @param [type] $goalname
     * @param [type] $goalshortname
     * @param [type] $goalurl
     * @return void
     */
    public static function update_goal(
        $instance,
        $goalid,
        $goalname,
        $goalshortname,
        $goalurl
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::update_goal_parameters(),
            [
                'goalid' => $goalid,
                'goalname' => $goalname,
                'goalshortname' => $goalshortname,
                'goalurl' => $goalurl,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        // Update in goal table.
        $goalrecord = new stdClass;
        $goalrecord->id = $goalid;
        $goalrecord->learninggoalwidgetid = $instance;
        $goalrecord->title = $goalname;
        $goalrecord->shortname = $goalshortname;
        $goalrecord->url = $goalurl;
        $DB->update_record('learninggoalwidget_goals', $goalrecord);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function delete_goal_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function delete_goal_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * delete goal from the taxonomy
     *
     * @param int $instance
     * @param int $topicid
     * @param int $goalid
     * @return string
     */
    public static function delete_goal(
        $instance,
        $topicid,
        $goalid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::delete_goal_parameters(),
            [
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $params = [
            'learninggoalwidgetid' => $instance,
            'topicid' => $topicid,
            'goalid' => $goalid,
        ];
        $DB->delete_records('learninggoalwidget_progs', $params);
        $params = [
            'id' => $goalid,
            'learninggoalwidgetid' => $instance,
            'topicid' => $topicid,
        ];
        $DB->delete_records('learninggoalwidget_goals', $params);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function delete_taxonomy_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function delete_taxonomy_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * delete the entire taxonomy
     *
     * @param int $instance
     * @return string
     */
    public static function delete_taxonomy(
        $instance
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::delete_taxonomy_parameters(),
            [
                'instance' => $instance,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $params = [
            'learninggoalwidgetid' => $instance,
        ];


        $DB->delete_records('learninggoalwidget_progs', $params);
        $DB->delete_records('learninggoalwidget_goals', $params);
        $DB->delete_records('learninggoalwidget_topics', $params);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function add_taxonomy_parameters() {
        return new external_function_parameters(
            [
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'taxonomy' => new external_value(PARAM_TEXT, 'The taxonomy'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function add_taxonomy_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * add the entire taxonomy
     *
     * @param int $instance
     * @param json $taxonomy
     * @return string
     */
    public static function add_taxonomy(
        $instance,
        $taxonomy
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::add_taxonomy_parameters(),
            [
                'instance' => $instance,
                'taxonomy' => $taxonomy,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $intaxonomy = json_decode($taxonomy);

        foreach ($intaxonomy->children as $topic) {
            $topicid = self::add_topic($instance, $topic->name, $topic->keyword, $topic->link);
            foreach ($topic->children as $goal) {
                self::add_goal($instance, $topicid, $goal->name, $goal->keyword, $goal->link);
            }
        }

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function moveup_goal_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function moveup_goal_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * move goal in front of previous one
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @param int $goalid
     * @return string
     */
    public static function moveup_goal(
        $course,
        $coursemodule,
        $instance,
        $topicid,
        $goalid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::moveup_goal_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $goalmoveup = new stdClass;
        $goalmoveup->course = $course;
        $goalmoveup->coursemodule = $coursemodule;
        $goalmoveup->instance = $instance;
        $goalmoveup->topic = $topicid;
        $goalmoveup->goal = $goalid;
        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND goal = :goalid";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalid' => $goalid,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $goalmoveup->id = $goalrecord->id;
        $goalmoveup->ranking = $goalrecord->ranking;
        $sqlstmt = "SELECT MAX(ranking) as ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND ranking < :goalranking";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalranking' => $goalrecord->ranking,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND ranking = :goalranking";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalranking' => $goalrecord->ranking,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params);

        $goalmovedown = new stdClass;
        $goalmovedown->id = $goalrecord->id;
        $goalmovedown->ranking = $goalmoveup->ranking;

        $goalmoveup->ranking = $goalrecord->ranking;

        $DB->update_record('learninggoalwidget_i_goals', $goalmoveup);
        $DB->update_record('learninggoalwidget_i_goals', $goalmovedown);

        return self::get_taxonomy($instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function movedown_goal_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
                'goalid' => new external_value(PARAM_INT, 'ID of the goal'),
            ]
        );
    }

    /**
     * return type definition
     *
     * @return external_value
     */
    public static function movedown_goal_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * move goal behind succeeding one
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @param int $goalid
     * @return string
     */
    public static function movedown_goal(
        $course,
        $coursemodule,
        $instance,
        $topicid,
        $goalid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::movedown_goal_parameters(),
            [
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            ]
        );

        self::validate_context(context_user::instance($USER->id));

        $goalmovedown = new stdClass;
        $goalmovedown->course = $course;
        $goalmovedown->coursemodule = $coursemodule;
        $goalmovedown->instance = $instance;
        $goalmovedown->topic = $topicid;
        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND goal = :goalid";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalid' => $goalid,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $goalmovedown->id = $goalrecord->id;
        $goalmovedown->ranking = $goalrecord->ranking;

        $sqlstmt = "SELECT MIN(ranking) as ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND ranking > :goalranking";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalranking' => $goalrecord->ranking,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, ranking
                      FROM {learninggoalwidget_i_goals}
                     WHERE course = :course
                       AND coursemodule = :coursemodule
                       AND instance = :instance
                       AND topic = :topicid
                       AND ranking = :goalranking";
        $params = [
            'course' => $course,
            'coursemodule' => $coursemodule,
            'instance' => $instance,
            'topicid' => $topicid,
            'goalranking' => $goalrecord->ranking,
        ];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params);

        $topicmoveup = new stdClass;
        $topicmoveup->id = $goalrecord->id;
        $topicmoveup->ranking = $goalmovedown->ranking;

        $goalmovedown->ranking = $goalrecord->ranking;

        $DB->update_record('learninggoalwidget_i_goals', $topicmoveup);
        $DB->update_record('learninggoalwidget_i_goals', $goalmovedown);

        return self::get_taxonomy($instance);
    }
}
