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
 * Privacy Subsystem implementation for Learning Goals Widget Activity.
 *
 * @package   mod_learninggoalwidget
 * @category  privacy
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use stdClass;

/**
 * Privacy Subsystem for Learning Goals Widget Activity
 *
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {


    /**
     * Returns meta data about this system.
     *
     * @param  collection $items The initialised collection to add items to.
     * @return collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items): collection {
        // The 'block_learninggoals_progress' table stores information about user's learning goal progress.
        $items->add_database_table(
            'learninggoalwidget_i_userpro',
            [
                'course' => 'privacy:metadata:learninggoalwidget_i_userpro:course',
                'coursemodule' => 'privacy:metadata:learninggoalwidget_i_userpro:coursemodule',
                'instance' => 'privacy:metadata:learninggoalwidget_i_userpro:instance',
                'topic' => 'privacy:metadata:learninggoalwidget_i_userpro:topic',
                'goal' => 'privacy:metadata:learninggoalwidget_i_userpro:goal',
                'user' => 'privacy:metadata:learninggoalwidget_i_userpro:user',
                'progress' => 'privacy:metadata:learninggoalwidget_i_userpro:progress',
            ],
            'privacy:metadata:learninggoalwidget_i_userpro'
        );
        return $items;
    }

    /**
     * Get the list of contexts where the specified user has attempted a quiz, or been involved with manual marking
     * and/or grading of a quiz.
     *
     * @param  int $userid The user to search.
     * @return contextlist     $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $resultset = new contextlist();

        // Users who used the widget and set progress values.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm
                    ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {learninggoalwidget} lgw
                    ON lgw.id = cm.instance
                  JOIN {learninggoalwidget_i_userpro} lgwup
                    ON lgwup.instance = lgw.id
                 WHERE lgwup.user = :userid";
        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'learninggoalwidget',
            'userid' => $userid,
        ];
        $resultset->add_from_sql($sql, $params);

        return $resultset;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $params = [
            'cmid' => $context->instanceid,
            'modname' => 'learninggoalwidget',
        ];

        // Users who attempted the quiz.
        $sql = "SELECT lgwup.user as userid
                  FROM {course_modules} cm
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {learninggoalwidget} lgw
                    ON lgw.id = cm.instance
                  JOIN {learninggoalwidget_i_userpro} lgwup
                    ON lgwup.instance = lgw.id
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, $params);

        \core_question\privacy\provider::get_users_in_context_from_sql($userlist, 'lgw', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (!count($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT lgwup.course AS course,
                       lgwup.instance AS instance,
                       lgwup.user AS user,
                       lgwup.progress AS progress,
                       lgwtopic.title AS topictitle,
                       lgwtopic.shortname AS topicshortname,
                       lgwtopic.url AS topicurl,
                       lgwgoal.title AS goaltitle,
                       lgwgoal.shortname AS goalshortname,
                       lgwgoal.url AS goalurl,
                       c.id AS contextid,
                       cm.id AS cmid
                  FROM {context} c
            INNER JOIN {course_modules} cm
                    ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
            INNER JOIN {learninggoalwidget} lgw
                    ON lgw.id = cm.instance
                  JOIN {learninggoalwidget_i_userpro} lgwup
                    ON lgwup.instance = lgw.id AND lgwup.user = :userid
            RIGHT JOIN {learninggoalwidget_topic} lgwtopic
                    ON lgwup.topic = lgwtopic.id
            RIGHT JOIN {learninggoalwidget_goal} lgwgoal
                    ON lgwup.goal = lgwgoal.id AND lgwup.topic = lgwgoal.topic
                 WHERE c.id {$contextsql}";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'learninggoalwidget',
            'userid' => $userid,
        ];
        $params += $contextparams;

        $progressrecords = $DB->get_recordset_sql($sql, $params);
        $data = new stdClass;
        $data->progress = [];
        foreach ($progressrecords as $progressrecord) {
            $context = $contextlist->current();
            $progress = new stdClass;
            $progress->course = format_string($progressrecord->course);
            $progress->instance = format_string($progressrecord->instance);
            $progress->topictitle = format_string($progressrecord->topictitle);
            $progress->goaltitle = format_string($progressrecord->goaltitle);
            $progress->progress = format_string($progressrecord->progress);
            \array_push($data->progress, $progress);

        }
        writer::with_context($context)
            ->export_data(['progress'], $data);
        $progressrecords->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            // Only learninggoalwidget module will be handled.
            return;
        }

        $cm = get_coursemodule_from_id('learninggoalwidget', $context->instanceid);
        if (!$cm) {
            return;
        }

        $DB->delete_records('learninggoalwidget_i_userpro', [
            'coursemodule' => $cm->id,
            'instance' => $cm->instance,
        ]);
        $DB->delete_records('learninggoalwidget_i_goals', [
            'coursemodule' => $cm->id,
            'instance' => $cm->instance,
        ]);
        $DB->delete_records('learninggoalwidget_i_topics', [
            'coursemodule' => $cm->id,
            'instance' => $cm->instance,
        ]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                // Only learninggoalwidget module will be handled.
                continue;
            }

            $cm = get_coursemodule_from_id('learninggoalwidget', $context->instanceid);
            if (!$cm) {
                continue;
            }

            // Fetch the details of the data to be removed.
            $user = $contextlist->get_user();

            $DB->delete_records('learninggoalwidget_i_userpro', [
                'coursemodule' => $cm->id,
                'instance' => $cm->instance,
                'user' => $user->id,
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            // Only learninggoalwidget module will be handled.
            return;
        }

        $cm = get_coursemodule_from_id('learninggoalwidget', $context->instanceid);
        if (!$cm) {
            // Only learninggoalwidget module will be handled.
            return;
        }

        $userids = $userlist->get_userids();

        foreach ($userids as $userid) {
            $DB->delete_records('learninggoalwidget_i_userpro', [
                'coursemodule' => $cm->id,
                'instance' => $cm->instance,
                'user' => $userid,
            ]);
        }
    }
}
