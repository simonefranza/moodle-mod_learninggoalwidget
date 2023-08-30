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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/classes/event/learninggoal_updated.php');

use mod_learninggoalwidget\local\taxonomy;
use mod_learninggoalwidget\local\userTaxonomy;

/**
 * Web Service API
 *
 * @package   mod_learninggoalwidget
 * @copyright University of Technology Graz
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
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, ''),
                'instance' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * get the taxonomy
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @return string
     */
    public static function get_taxonomy(
        $course,
        $coursemodule,
        $instance
    ) {
        // Parameter validation.
        self::validate_parameters(
            self::get_taxonomy_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
            )
        );
        return (new taxonomy($coursemodule, $course, null, $instance))->get_taxonomy_as_json();
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function get_taxonomy_for_user_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                'userid' => new external_value(PARAM_INT, 'ID of the logged in user'),
                'coursemoduleid' => new external_value(PARAM_INT, ''),
                'instanceid' => new external_value(PARAM_INT, ''),
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
     * @param [type] $courseid
     * @param [type] $userid
     * @param [type] $coursemoduleid
     * @param [type] $instanceid
     * @return void
     */
    public static function get_taxonomy_for_user(
        $courseid,
        $userid,
        $coursemoduleid,
        $instanceid
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::get_taxonomy_for_user_parameters(),
            array(
                'courseid' => $courseid,
                'userid' => $userid,
                'coursemoduleid' => $coursemoduleid,
                'instanceid' => $instanceid,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        return (new userTaxonomy($coursemoduleid, $courseid, null, $instanceid, $userid))->get_taxonomy_as_json();
    }


    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function update_user_progress_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemoduleid' => new external_value(PARAM_INT, 'ID of the course module'),
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
     * @param [type] $courseid
     * @param [type] $coursemoduleid
     * @param [type] $instanceid
     * @param [type] $userid
     * @param [type] $topicid
     * @param [type] $goalid
     * @param [type] $progress
     * @return void
     */
    public static function update_user_progress(
        $courseid,
        $coursemoduleid,
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
            array(
                'courseid' => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'instanceid' => $instanceid,
                'userid' => $userid,
                'topicid' => $topicid,
                'goalid' => $goalid,
                'progress' => $progress
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $sqlstmt = "SELECT id FROM {learninggoalwidget_i_userpro}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_user = ? AND lgw_topic = ? AND lgw_goal = ?";
        $params = [$courseid, $coursemoduleid, $instanceid, $userid, $topicid, $goalid];
        $userprogressrecord = $DB->get_record_sql($sqlstmt, $params);
        if ($userprogressrecord) {
            $userprogress = new stdClass;
            $userprogress->id = $userprogressrecord->id;
            $userprogress->lgw_progress = $progress;
            $DB->update_record('learninggoalwidget_i_userpro', $userprogress);
        } else {
            $userprogress = new stdClass;
            $userprogress->lgw_course = $courseid;
            $userprogress->lgw_coursemodule = $coursemoduleid;
            $userprogress->lgw_instance = $instanceid;
            $userprogress->lgw_topic = $topicid;
            $userprogress->lgw_goal = $goalid;
            $userprogress->lgw_user = $userid;
            $userprogress->lgw_progress = $progress;
            $DB->insert_record('learninggoalwidget_i_userpro', $userprogress);
        }

        return self::get_taxonomy_for_user($courseid, $userid, $coursemoduleid, $instanceid);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function log_event_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemoduleid' => new external_value(PARAM_INT, 'ID of the course module'),
                'instanceid' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'userid' => new external_value(PARAM_INT, 'ID of the user'),
                'eventparams' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            "name" => new external_value(PARAM_TEXT, 'name'),
                            "value" => new external_value(PARAM_TEXT, 'keyword'),
                        )
                    )
                )
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
     * @param [type] $courseid
     * @param [type] $coursemoduleid
     * @param [type] $instanceid
     * @param [type] $userid
     * @param [type] $eventparams
     * @return void
     */
    public static function log_event(
        $courseid,
        $coursemoduleid,
        $instanceid,
        $userid,
        $eventparams
    ) {

        // Parameter validation.
        $params = self::validate_parameters(
            self::log_event_parameters(),
            array(
                'courseid' => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'instanceid' => $instanceid,
                'userid' => $userid,
                'eventparams' => $eventparams
            )
        );

        self::validate_context(context_user::instance($userid));

        $usercontext = context_user::instance($userid);

        // Left out 'courseid' => $courseid, because it was causing problems
        $params = array(
            'contextid' => $usercontext->id,
            'relateduserid' => $userid,
            'other' => $eventparams,
            'userid' => $userid,
        );

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
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
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
     * Insert a new topic in the topic table and reference it with course and rank from topic instance table
     * without checks, for internal use
     *
     * @param  [type] $course
     * @param  [type] $coursemodule
     * @param  [type] $instance
     * @param  [type] $topicname
     * @param  [type] $topicshortname
     * @param  [type] $topicurl
     * @return id the id of the added topic
     */
    public static function add_topic(
        $course,
        $coursemodule,
        $instance,
        $topicname,
        $topicshortname,
        $topicurl
    ) {
        global $DB;
        // Insert in topic table.
        $topicrecord = new stdClass;
        $topicrecord->lgw_title = $topicname;
        $topicrecord->lgw_shortname = $topicshortname;
        $topicrecord->lgw_url = $topicurl;
        $topicrecord->id = $DB->insert_record('learninggoalwidget_topic', $topicrecord);

        // Link topic with learning goal activity in a course.
        $topicinstancerecord = new stdClass;
        $topicinstancerecord->lgw_course = $course;
        $topicinstancerecord->lgw_coursemodule = $coursemodule;
        $topicinstancerecord->lgw_instance = $instance;
        $topicinstancerecord->lgw_topic = $topicrecord->id;
        $topicinstancerecord->lgw_rank = 1;
        $sqlstmt = "SELECT MAX(lgw_rank) as lgw_maxrank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ?";
        $params = [$course, $coursemodule, $instance];
        $topiccountrecord = $DB->get_record_sql($sqlstmt, $params);
        if ($topiccountrecord) {
            $topicinstancerecord->lgw_rank = $topiccountrecord->lgw_maxrank + 1;
        }
        $topicinstancerecord->id = $DB->insert_record('learninggoalwidget_i_topics', $topicinstancerecord);
        return $topicrecord->id;
    }

    /**
     * Insert a new topic in the topic table and reference it with course and rank from topic instance table
     *
     * @param  [type] $course
     * @param  [type] $coursemodule
     * @param  [type] $instance
     * @param  [type] $topicname
     * @param  [type] $topicshortname
     * @param  [type] $topicurl
     * @return void
     */
    public static function insert_topic(
        $course,
        $coursemodule,
        $instance,
        $topicname,
        $topicshortname,
        $topicurl
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::insert_topic_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicname' => $topicname,
                'topicshortname' => $topicshortname,
                'topicurl' => $topicurl
            )
        );

        self::validate_context(context_user::instance($USER->id));

        self::add_topic($course, $coursemodule, $instance, $topicname, $topicshortname, $topicurl);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function update_topic_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
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
     * @param  [type] $course
     * @param  [type] $coursemodule
     * @param  [type] $instance
     * @param  [type] $topicid
     * @param  [type] $topicname
     * @param  [type] $topicshortname
     * @param  [type] $topicurl
     * @return void
     */
    public static function update_topic(
        $course,
        $coursemodule,
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
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'topicname' => $topicname,
                'topicshortname' => $topicshortname,
                'topicurl' => $topicurl
            )
        );

        self::validate_context(context_user::instance($USER->id));

        // Update in topic table.
        $topicrecord = new stdClass;
        $topicrecord->id = $topicid;
        $topicrecord->lgw_title = $topicname;
        $topicrecord->lgw_shortname = $topicshortname;
        $topicrecord->lgw_url = $topicurl;
        $DB->update_record('learninggoalwidget_topic', $topicrecord);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function delete_topic_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
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
     * delete a topic (including related goals) from the taxonomy
     *
     * @param [type] $course
     * @param [type] $coursemodule
     * @param [type] $instance
     * @param [type] $topicid
     * @return void
     */
    public static function delete_topic(
        $course,
        $coursemodule,
        $instance,
        $topicid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::delete_topic_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $params = [
            'lgw_course' => $course,
            'lgw_coursemodule' => $coursemodule,
            'lgw_instance' => $instance,
            'lgw_topic' => $topicid,
        ];
        $DB->delete_records('learninggoalwidget_i_userpro', $params);
        $DB->delete_records('learninggoalwidget_i_goals', $params);
        $DB->delete_records('learninggoalwidget_i_topics', $params);
        $DB->delete_records('learninggoalwidget_goal', ['lgw_topic' => $topicid]);

        $DB->delete_records('learninggoalwidget_topic', ['id' => $topicid]);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function moveup_topic_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
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
     * move the topic before the preceding one (decrease rank)
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @return void
     */
    public static function moveup_topic(
        $course,
        $coursemodule,
        $instance,
        $topicid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::moveup_topic_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $topicmoveup = new stdClass;
        $topicmoveup->lgw_course = $course;
        $topicmoveup->lgw_coursemodule = $coursemodule;
        $topicmoveup->lgw_instance = $instance;
        $topicmoveup->lgw_topic = $topicid;
        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ?";
        $params = [$course, $coursemodule, $instance, $topicid];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $topicmoveup->id = $topicrecord->id;
        $topicmoveup->lgw_rank = $topicrecord->lgw_rank;
        $sqlstmt = "SELECT MAX(lgw_rank) as lgw_rank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_rank < ?";
        $params = [$course, $coursemodule, $instance, $topicrecord->lgw_rank];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_rank = ?";
        $params = [$course, $coursemodule, $instance, $topicrecord->lgw_rank];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params);

        $topicmovedown = new stdClass;
        $topicmovedown->id = $topicrecord->id;
        $topicmovedown->lgw_rank = $topicmoveup->lgw_rank;

        $topicmoveup->lgw_rank = $topicrecord->lgw_rank;

        $DB->update_record('learninggoalwidget_i_topics', $topicmoveup);
        $DB->update_record('learninggoalwidget_i_topics', $topicmovedown);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function movedown_topic_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
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
     * move topic behind the succeeding one (increase rank)
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @return string
     */
    public static function movedown_topic(
        $course,
        $coursemodule,
        $instance,
        $topicid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::movedown_topic_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $topicmovedown = new stdClass;
        $topicmovedown->lgw_course = $course;
        $topicmovedown->lgw_coursemodule = $coursemodule;
        $topicmovedown->lgw_instance = $instance;
        $topicmovedown->lgw_topic = $topicid;
        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ?";
        $params = [$course, $coursemodule, $instance, $topicid];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $topicmovedown->id = $topicrecord->id;
        $topicmovedown->lgw_rank = $topicrecord->lgw_rank;

        $sqlstmt = "SELECT MIN(lgw_rank) as lgw_rank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_rank > ?";
        $params = [$course, $coursemodule, $instance, $topicrecord->lgw_rank];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_rank = ?";
        $params = [$course, $coursemodule, $instance, $topicrecord->lgw_rank];
        $topicrecord = $DB->get_record_sql($sqlstmt, $params);

        $topicmoveup = new stdClass;
        $topicmoveup->id = $topicrecord->id;
        $topicmoveup->lgw_rank = $topicmovedown->lgw_rank;

        $topicmovedown->lgw_rank = $topicrecord->lgw_rank;

        $DB->update_record('learninggoalwidget_i_topics', $topicmoveup);
        $DB->update_record('learninggoalwidget_i_topics', $topicmovedown);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function insert_goal_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
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
     * @param  [type] $course
     * @param  [type] $coursemodule
     * @param  [type] $instance
     * @param  [type] $topicid
     * @param  [type] $goalname
     * @param  [type] $goalshortname
     * @param  [type] $goalurl
     * @return void
     */
    public static function add_goal(
        $course,
        $coursemodule,
        $instance,
        $topicid,
        $goalname,
        $goalshortname,
        $goalurl
    ) {
        global $DB;
        // Insert in goal table.
        $goalrecord = new stdClass;
        $goalrecord->lgw_title = $goalname;
        $goalrecord->lgw_shortname = $goalshortname;
        $goalrecord->lgw_url = $goalurl;
        $goalrecord->lgw_topic = $topicid;
        $goalrecord->id = $DB->insert_record('learninggoalwidget_goal', $goalrecord);

        // Link goal with learning goal activity in a course.
        $goalinstancerecord = new stdClass;
        $goalinstancerecord->lgw_course = $course;
        $goalinstancerecord->lgw_coursemodule = $coursemodule;
        $goalinstancerecord->lgw_instance = $instance;
        $goalinstancerecord->lgw_topic = $topicid;
        $goalinstancerecord->lgw_goal = $goalrecord->id;
        $goalinstancerecord->lgw_rank = 1;
        $sqlstmt = "SELECT MAX(lgw_rank) as lgw_maxrank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ?";
        $params = [$course, $coursemodule, $instance, $topicid];
        $goalcountrecord = $DB->get_record_sql($sqlstmt, $params);
        if ($goalcountrecord) {
            $goalinstancerecord->lgw_rank = $goalcountrecord->lgw_maxrank + 1;
        }
        $goalinstancerecord->id = $DB->insert_record('learninggoalwidget_i_goals', $goalinstancerecord);
    }


    /**
     * insert a new goal
     *
     * @param  [type] $course
     * @param  [type] $coursemodule
     * @param  [type] $instance
     * @param  [type] $topicid
     * @param  [type] $goalname
     * @param  [type] $goalshortname
     * @param  [type] $goalurl
     * @return void
     */
    public static function insert_goal(
        $course,
        $coursemodule,
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
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalname' => $goalname,
                'goalshortname' => $goalshortname,
                'goalurl' => $goalurl,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        self::add_goal($course, $coursemodule, $instance,
            $topicid, $goalname, $goalshortname, $goalurl);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function update_goal_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
                'instance' => new external_value(PARAM_INT, 'ID of the course module instance'),
                'topicid' => new external_value(PARAM_INT, 'ID of the topic'),
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
     * @param [type] $course
     * @param [type] $coursemodule
     * @param [type] $instance
     * @param [type] $topicid
     * @param [type] $goalid
     * @param [type] $goalname
     * @param [type] $goalshortname
     * @param [type] $goalurl
     * @return void
     */
    public static function update_goal(
        $course,
        $coursemodule,
        $instance,
        $topicid,
        $goalid,
        $goalname,
        $goalshortname,
        $goalurl
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::update_goal_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
                'goalname' => $goalname,
                'goalshortname' => $goalshortname,
                'goalurl' => $goalurl,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        // Update in goal table.
        $goalrecord = new stdClass;
        $goalrecord->id = $goalid;
        $goalrecord->lgw_title = $goalname;
        $goalrecord->lgw_shortname = $goalshortname;
        $goalrecord->lgw_url = $goalurl;
        $DB->update_record('learninggoalwidget_goal', $goalrecord);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function delete_goal_parameters() {
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
    public static function delete_goal_returns() {
        return new external_value(PARAM_TEXT, 'learning topics taxonomy');
    }

    /**
     * delete goal from the taxonomy
     *
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param int $topicid
     * @param int $goalid
     * @return string
     */
    public static function delete_goal(
        $course,
        $coursemodule,
        $instance,
        $topicid,
        $goalid
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::delete_goal_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $params = [
            'lgw_course' => $course,
            'lgw_coursemodule' => $coursemodule,
            'lgw_instance' => $instance,
            'lgw_topic' => $topicid,
            'lgw_goal' => $goalid,
        ];
        $DB->delete_records('learninggoalwidget_i_userpro', $params);
        $DB->delete_records('learninggoalwidget_i_goals', $params);
        $DB->delete_records('learninggoalwidget_goal', ['id' => $goalid]);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function delete_taxonomy_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
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
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @return string
     */
    public static function delete_taxonomy(
        $course,
        $coursemodule,
        $instance
    ) {
        global $DB, $USER;

        // Parameter validation.
        self::validate_parameters(
            self::delete_taxonomy_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $params = [
            'lgw_course' => $course,
            'lgw_coursemodule' => $coursemodule,
            'lgw_instance' => $instance,
        ];

        $sqlstmt = "SELECT lgw_topic FROM {learninggoalwidget_i_topics}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ?";
        $params1 = [$course, $coursemodule, $instance];
        $topicrecords = $DB->get_records_sql($sqlstmt, $params1);
        $sqlstmt = "SELECT lgw_goal FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ?";
        $params2 = [$course, $coursemodule, $instance];
        $goalrecords = $DB->get_records_sql($sqlstmt, $params2);

        $DB->delete_records('learninggoalwidget_i_userpro', $params);
        $DB->delete_records('learninggoalwidget_i_goals', $params);
        $DB->delete_records('learninggoalwidget_i_topics', $params);
        foreach ($topicrecords as $topicrecord) {
            $DB->delete_records('learninggoalwidget_topic', ['id' => $topicrecord->lgw_topic]);
        }
        foreach ($goalrecords as $goalrecord) {
            $DB->delete_records('learninggoalwidget_goal', ['id' => $goalrecord->lgw_goal]);
        }

        return self::get_taxonomy($course, $coursemodule, $instance);
    }

    /**
     * parameter definition
     *
     * @return external_function_parameters service function parameter definition
     */
    public static function add_taxonomy_parameters() {
        return new external_function_parameters(
            [
                'course' => new external_value(PARAM_INT, 'ID of the course'),
                'coursemodule' => new external_value(PARAM_INT, 'ID of the course module'),
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
     * @param int $course
     * @param int $coursemodule
     * @param int $instance
     * @param json $taxonomy
     * @return string
     */
    public static function add_taxonomy(
        $course,
        $coursemodule,
        $instance,
        $taxonomy
    ) {
        global $USER;

        // Parameter validation.
        self::validate_parameters(
            self::add_taxonomy_parameters(),
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'taxonomy' => $taxonomy,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $intaxonomy = json_decode($taxonomy);

        foreach ($intaxonomy->children as $topic) {
            $topicid = self::add_topic($course, $coursemodule, $instance,
                $topic->name, $topic->keyword, $topic->link);
            foreach ($topic->children as $goal) {
                self::add_goal($course, $coursemodule, $instance,
                    $topicid, $goal->name, $goal->keyword, $goal->link);
            }
        }

        return self::get_taxonomy($course, $coursemodule, $instance);
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
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $goalmoveup = new stdClass;
        $goalmoveup->lgw_course = $course;
        $goalmoveup->lgw_coursemodule = $coursemodule;
        $goalmoveup->lgw_instance = $instance;
        $goalmoveup->lgw_topic = $topicid;
        $goalmoveup->lgw_goal = $goalid;
        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ? AND lgw_goal = ?";
        $params = [$course, $coursemodule, $instance, $topicid, $goalid];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $goalmoveup->id = $goalrecord->id;
        $goalmoveup->lgw_rank = $goalrecord->lgw_rank;
        $sqlstmt = "SELECT MAX(lgw_rank) as lgw_rank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ? AND lgw_rank < ?";
        $params = [$course, $coursemodule, $instance, $topicid, $goalrecord->lgw_rank];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ? AND lgw_rank = ?";
        $params = [$course, $coursemodule, $instance, $topicid, $goalrecord->lgw_rank];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params);

        $goalmovedown = new stdClass;
        $goalmovedown->id = $goalrecord->id;
        $goalmovedown->lgw_rank = $goalmoveup->lgw_rank;

        $goalmoveup->lgw_rank = $goalrecord->lgw_rank;

        $DB->update_record('learninggoalwidget_i_goals', $goalmoveup);
        $DB->update_record('learninggoalwidget_i_goals', $goalmovedown);

        return self::get_taxonomy($course, $coursemodule, $instance);
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
            array(
                'course' => $course,
                'coursemodule' => $coursemodule,
                'instance' => $instance,
                'topicid' => $topicid,
                'goalid' => $goalid,
            )
        );

        self::validate_context(context_user::instance($USER->id));

        $goalmovedown = new stdClass;
        $goalmovedown->lgw_course = $course;
        $goalmovedown->lgw_coursemodule = $coursemodule;
        $goalmovedown->lgw_instance = $instance;
        $goalmovedown->lgw_topic = $topicid;
        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ? AND lgw_goal = ?";
        $params = [$course, $coursemodule, $instance, $topicid, $goalid];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $goalmovedown->id = $goalrecord->id;
        $goalmovedown->lgw_rank = $goalrecord->lgw_rank;

        $sqlstmt = "SELECT MIN(lgw_rank) as lgw_rank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ? AND lgw_rank > ?";
        $params = [$course, $coursemodule, $instance, $topicid, $goalrecord->lgw_rank];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params, MUST_EXIST);

        $sqlstmt = "SELECT id, lgw_rank FROM {learninggoalwidget_i_goals}
        WHERE lgw_course = ? AND lgw_coursemodule = ? AND lgw_instance = ? AND lgw_topic = ? AND lgw_rank = ?";
        $params = [$course, $coursemodule, $instance, $topicid, $goalrecord->lgw_rank];
        $goalrecord = $DB->get_record_sql($sqlstmt, $params);

        $topicmoveup = new stdClass;
        $topicmoveup->id = $goalrecord->id;
        $topicmoveup->lgw_rank = $goalmovedown->lgw_rank;

        $goalmovedown->lgw_rank = $goalrecord->lgw_rank;

        $DB->update_record('learninggoalwidget_i_goals', $topicmoveup);
        $DB->update_record('learninggoalwidget_i_goals', $goalmovedown);

        return self::get_taxonomy($course, $coursemodule, $instance);
    }
}
