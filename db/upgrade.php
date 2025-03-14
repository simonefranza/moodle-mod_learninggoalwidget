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
 * learninggoalwidget module upgrade
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file keeps track of upgrades to
// the learninggoalwidget module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

/**
 * deletes a foreign key from a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param string $name
 * @param string[] $fields
 * @param string $reftable
 * @param string[] $reffields
 * @return void
 */
function xmldb_learninggoalwidget_delete_foreign_key($dbman, $tablename, $name, $fields, $reftable, $reffields) {
    $table = new xmldb_table($tablename);
    $key = new xmldb_key($name, XMLDB_KEY_FOREIGN, $fields, $reftable, $reffields);
    $dbman->drop_key($table, $key);
}

/**
 * deletes an index from a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param string $indexname
 * @param int $unique
 * @param string[] $fields
 * @return void
 */
function xmldb_learninggoalwidget_delete_index($dbman, $tablename, $indexname, $unique, $fields) {
    $table = new xmldb_table($tablename);
    $index = new xmldb_index($indexname, $unique, $fields);

    // Conditionally launch drop index.
    if ($dbman->index_exists($table, $index)) {
        $dbman->drop_index($table, $index);
    }
}

/**
 * deletes a key from a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param string $fieldname
 * @return void
 */
function xmldb_learninggoalwidget_delete_key($dbman, $tablename, $fieldname) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldname);

    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }
}

/**
 * add a field to a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param object $field
 * @return void
 */
function xmldb_learninggoalwidget_add_field($dbman, $tablename, $field) {
    // Define field learninggoalwidgetid to be added to learninggoalwidget_topics.
    $table = new xmldb_table($tablename);

    // Conditionally launch add field learninggoalwidgetid.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    } else {
        throw new Exception("Cannot add field to '" . $tablename . "'");
    }
}

/**
 * add an index to a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param string $indexname
 * @param string[] $fields
 * @return void
 */
function xmldb_learninggoalwidget_add_notunique_index($dbman, $tablename, $indexname, $fields) {
    $table = new xmldb_table($tablename);
    $index = new xmldb_index($indexname, XMLDB_INDEX_NOTUNIQUE, $fields);

    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    } else {
        throw new Exception("Index '" . $indexname . "' exists already in '" . $tablename . "'");
    }
}

/**
 * rename a field of a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param object $field
 * @param string $newname
 * @return void
 */
function xmldb_learninggoalwidget_rename_field($dbman, $tablename, $field, $newname) {
    $table = new xmldb_table($tablename);
    if ($dbman->field_exists($table, $field)) {
        $dbman->rename_field($table, $field, $newname);
    } else {
        throw new Exception("Cannot rename field to '" . $newname. "' in '" . $tablename . "'");
    }
}

/**
 * rename a table
 *
 * @param object $dbman
 * @param string $tablename
 * @param string $newname
 * @return void
 */
function xmldb_learninggoalwidget_rename_table($dbman, $tablename, $newname) {
    $table = new xmldb_table($tablename);
    if ($dbman->table_exists($table)) {
        $dbman->rename_table($table, $newname);
    } else {
        throw new Exception("Cannot rename table '" . $tablename . "'. It does not exist");
    }
}

/**
 * drop a table
 *
 * @param object $dbman
 * @param string $tablename
 * @return void
 */
function xmldb_learninggoalwidget_drop_table($dbman, $tablename) {
    $table = new xmldb_table($tablename);
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    } else {
        throw new Exception("Cannot drop table '" . $tablename . "'. It does not exist");
    }
}

/**
 * upgrade learning goal widget
 *
 * @param int $oldversion
 * @return void
 */
function xmldb_learninggoalwidget_upgrade($oldversion) {
    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
    $transaction = null;
    try {
        if ($oldversion < 2024042202) {
            $transaction = $DB->start_delegated_transaction();
            xmldb_learninggoalwidget_upgrade1($dbman);
            $transaction->allow_commit();
            $transaction = null;

            // Learninggoalwidget savepoint reached.
            upgrade_mod_savepoint(true, 2024042202, 'learninggoalwidget');
        }

        if ($oldversion < 2025020500) {
            $transaction = $DB->start_delegated_transaction();
            xmldb_learninggoalwidget_upgrade2($dbman);
            $transaction->allow_commit();
            $transaction = null;

            // Learninggoalwidget savepoint reached.
            upgrade_mod_savepoint(true, 2025020500, 'learninggoalwidget');
        }
    } catch (Exception $e) {
        if ($transaction) {
            // Roll back the transaction in case of an error.
            $transaction->rollback($e);
        }
        throw $e;
    }

    return true;
}

/**
 * upgrade learning goal widget for oldversion < 2024042202
 *
 * @param xmldb $dbman
 * @return void
 */
function xmldb_learninggoalwidget_upgrade1($dbman) {
    // Change learninggoalwidget_i_userpro.user to userid .
    $table = new xmldb_table('learninggoalwidget_i_userpro');
    $field = new xmldb_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'user');

    if ($dbman->field_exists($table, $field)) {
        // Remove index user .
        $index = new xmldb_index('user', XMLDB_INDEX_NOTUNIQUE, ['user']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Remove fk_user .
        $key = new xmldb_key('fk_user', XMLDB_KEY_FOREIGN, ['user'], 'user', ['id']);
        $dbman->drop_key($table, $key);

        // Rename user -> userid .
        $field = new xmldb_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'user');
        $dbman->rename_field($table, $field, 'userid');

        // Add fk_userid .
        $key = new xmldb_key('fk_userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $dbman->add_key($table, $key);
    }

    // Change learninggoalwidget_i_topics.rank to ranking .
    $table = new xmldb_table('learninggoalwidget_i_topics');
    $field = new xmldb_field('rank', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'rank');
    if ($dbman->field_exists($table, $field)) {
        $dbman->rename_field($table, $field, 'ranking');
    }

    // Change learninggoalwidget_i_topics.rank to ranking .
    $table = new xmldb_table('learninggoalwidget_i_goals');
    if ($dbman->field_exists($table, $field)) {
        $dbman->rename_field($table, $field, 'ranking');
    }
}

/**
 * upgrade learning goal widget for oldversion < 2025020500
 * delete foreign keys
 *
 * @param xmldb $dbman
 * @return void
 */
function xmldb_learninggoalwidget_upgrade2_delete_foreign_keys($dbman) {
    // Remove all foreign keys.
    // Remove learninggoalwidget_goal->fk_topic.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_goal',
        'fk_topic', ['topic'], 'learninggoalwidget_topic', ['id']);

    // Remove learninggoalwidget_i_topics->fk_topic.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_topics',
        'fk_topic', ['topic'], 'learninggoalwidget_topic', ['id']);
    // Remove learninggoalwidget_i_topics->fk_course.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_topics',
        'fk_course', ['course'], 'course', ['id']);

    // Remove learninggoalwidget_i_goals->fk_topic.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_goals',
        'fk_topic', ['topic'], 'learninggoalwidget_topic', ['id']);
    // Remove learninggoalwidget_i_goals->fk_goal.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_goals',
        'fk_goal', ['goal'], 'learninggoalwidget_goal', ['id']);
    // Remove learninggoalwidget_i_goals->fk_course.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_goals',
        'fk_course', ['course'], 'course', ['id']);

    // Remove learninggoalwidget_i_userpro->fk_topic.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_userpro',
        'fk_topic', ['topic'], 'learninggoalwidget_topic', ['id']);
    // Remove learninggoalwidget_i_userpro->fk_goal.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_userpro',
        'fk_goal', ['goal'], 'learninggoalwidget_goal', ['id']);
    // Remove learninggoalwidget_i_userpro->fk_course.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_userpro',
        'fk_course', ['course'], 'course', ['id']);
    // Remove learninggoalwidget_i_userpro->fk_userid.
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget_i_userpro',
        'fk_userid', ['userid'], 'user', ['id']);

    // Remove learninggoalwidget->fk_course .
    xmldb_learninggoalwidget_delete_foreign_key($dbman, 'learninggoalwidget',
        'fk_course', ['course'], 'course', ['id']);
}

/**
 * upgrade learning goal widget for oldversion < 2025020500
 * delete indexes
 *
 * @param xmldb $dbman
 * @return void
 */
function xmldb_learninggoalwidget_upgrade2_delete_indexes($dbman) {
    // Remove all indexes.
    // Remove index learninggoalwidget_goal->topic.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_goal',
        'topic', XMLDB_INDEX_NOTUNIQUE, ['topic']);

    // Remove index learninggoalwidget_i_topics->topic.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_topics',
        'topic', XMLDB_INDEX_NOTUNIQUE, ['topic']);
    // Remove index learninggoalwidget_i_topics->course.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_topics',
        'course', XMLDB_INDEX_NOTUNIQUE, ['course']);
    // Remove index learninggoalwidget_i_topics->coursemodule.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_topics',
        'coursemodule', XMLDB_INDEX_NOTUNIQUE, ['coursemodule']);

    // Remove index learninggoalwidget_i_goals->topic.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_goals',
        'topic', XMLDB_INDEX_NOTUNIQUE, ['topic']);
    // Remove index learninggoalwidget_i_goals->goal.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_goals',
        'goal', XMLDB_INDEX_NOTUNIQUE, ['goal']);
    // Remove index learninggoalwidget_i_goals->course.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_goals',
        'course', XMLDB_INDEX_NOTUNIQUE, ['course']);
    // Remove index learninggoalwidget_i_goals->coursemodule.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_goals',
        'coursemodule', XMLDB_INDEX_NOTUNIQUE, ['coursemodule']);

    // Remove index learninggoalwidget_i_userpro->topic.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_userpro',
        'topic', XMLDB_INDEX_NOTUNIQUE, ['topic']);
    // Remove index learninggoalwidget_i_userpro->goal.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_userpro',
        'goal', XMLDB_INDEX_NOTUNIQUE, ['goal']);
    // Remove index learninggoalwidget_i_userpro->course.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_userpro',
        'course', XMLDB_INDEX_NOTUNIQUE, ['course']);
    // Remove index learninggoalwidget_i_userpro->coursemodule.
    xmldb_learninggoalwidget_delete_index($dbman, 'learninggoalwidget_i_userpro',
        'coursemodule', XMLDB_INDEX_NOTUNIQUE, ['coursemodule']);
}

/**
 * upgrade learning goal widget for oldversion < 2025020500
 * rename old tables
 *
 * @param xmldb $dbman
 * @return void
 */
function xmldb_learninggoalwidget_upgrade2_rename_tables($dbman) {
    // Rename learninggoalwidget_topic -> learninggoalwidget_topics.
    xmldb_learninggoalwidget_rename_table($dbman, 'learninggoalwidget_topic', 'learninggoalwidget_topics');
    // Rename learninggoalwidget_goal -> learninggoalwidget_goals.
    xmldb_learninggoalwidget_rename_table($dbman, 'learninggoalwidget_goal', 'learninggoalwidget_goals');
    // Rename learninggoalwidget_i_userpro -> learninggoalwidget_progs.
    xmldb_learninggoalwidget_rename_table($dbman, 'learninggoalwidget_i_userpro', 'learninggoalwidget_progs');
}

/**
 * upgrade learning goal widget for oldversion < 2025020500
 * add/rename new fields
 *
 * @param xmldb $dbman
 * @return void
 */
function xmldb_learninggoalwidget_upgrade2_new_fields($dbman) {
    // Add field learninggoalwidget_topics->learninggoalwidgetid.
    $field = new xmldb_field('learninggoalwidgetid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
    xmldb_learninggoalwidget_add_field($dbman, 'learninggoalwidget_topics', $field);
    // Add field learninggoalwidget_topics->ranking.
    $field = new xmldb_field('ranking', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'url');
    xmldb_learninggoalwidget_add_field($dbman, 'learninggoalwidget_topics', $field);

    // Add field learninggoalwidget_goals->learninggoalwidgetid.
    $field = new xmldb_field('learninggoalwidgetid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
    xmldb_learninggoalwidget_add_field($dbman, 'learninggoalwidget_goals', $field);
    // Rename field learninggoalwidget_goals->topic -> topicid.
    $field = new xmldb_field('topic', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'learninggoalwidgetid');
    xmldb_learninggoalwidget_rename_field($dbman, 'learninggoalwidget_goals', $field, 'topicid');
    // Add field learninggoalwidget_goals->ranking.
    $field = new xmldb_field('ranking', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'url');
    xmldb_learninggoalwidget_add_field($dbman, 'learninggoalwidget_goals', $field);

    // Add field learninggoalwidget_progs->learninggoalwidgetid.
    $field = new xmldb_field('instance', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'coursemodule');
    xmldb_learninggoalwidget_rename_field($dbman, 'learninggoalwidget_progs', $field, 'learninggoalwidgetid');
    // Rename field learninggoalwidget_progs->topic -> topicid.
    $field = new xmldb_field('topic', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'learninggoalwidgetid');
    xmldb_learninggoalwidget_rename_field($dbman, 'learninggoalwidget_progs', $field, 'topicid');
    // Rename field learninggoalwidget_progs->goal -> goalid.
    $field = new xmldb_field('goal', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'topicid');
    xmldb_learninggoalwidget_rename_field($dbman, 'learninggoalwidget_progs', $field, 'goalid');
}

/**
 * upgrade learning goal widget for oldversion < 2025020500
 *
 * @param xmldb $dbman
 * @return void
 */
function xmldb_learninggoalwidget_upgrade2($dbman) {
    global $DB;

    // Delete unneded foreign keys.
    xmldb_learninggoalwidget_upgrade2_delete_foreign_keys($dbman);

    // Delete unneded indexes.
    xmldb_learninggoalwidget_upgrade2_delete_indexes($dbman);

    // Rename tables.
    xmldb_learninggoalwidget_upgrade2_rename_tables($dbman);

    // Add/rename new fields.
    xmldb_learninggoalwidget_upgrade2_new_fields($dbman);

    // Consolidate topics.
    $sqlstmt = "SELECT id, instance, topic, ranking
                  FROM {learninggoalwidget_i_topics}";
    $params = [];
    $topicrecords = $DB->get_records_sql($sqlstmt, $params);
    $topicstmt = "SELECT id, learninggoalwidgetid, ranking
                    FROM {learninggoalwidget_topics}
                   WHERE id = :topicid";
    foreach ($topicrecords as $topicrecord) {
        $params = [
            'topicid' => $topicrecord->topic,
        ];
        $record = $DB->get_record_sql($topicstmt, $params);
        $record->learninggoalwidgetid = $topicrecord->instance;
        $record->ranking = $topicrecord->ranking;
        $DB->update_record('learninggoalwidget_topics', $record);
    }

    // Consolidate goals.
    $sqlstmt = "SELECT id, instance, goal, ranking
                  FROM {learninggoalwidget_i_goals}";
    $params = [];
    $goalrecords = $DB->get_records_sql($sqlstmt, $params);
    $goalstmt = "SELECT id, learninggoalwidgetid, ranking
                   FROM {learninggoalwidget_goals}
                  WHERE id = :goalid";
    foreach ($goalrecords as $goalrecord) {
        $params = [
            'goalid' => $goalrecord->goal,
        ];
        $record = $DB->get_record_sql($goalstmt, $params);
        $record->learninggoalwidgetid = $goalrecord->instance;
        $record->ranking = $goalrecord->ranking;
        $DB->update_record('learninggoalwidget_goals', $record);
    }

    // Add new indexes.
    // Add index learninggoalwidget_topics->learninggoalwidgetid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_topics',
        'learninggoalwidgetid', ['learninggoalwidgetid']);

    // Add index learninggoalwidget_goals->learninggoalwidgetid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_goals',
        'learninggoalwidgetid', ['learninggoalwidgetid']);
    // Add index learninggoalwidget_goals->topicid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_goals', 'topicid', ['topicid']);

    // Add index learninggoalwidget_progs->learninggoalwidgetid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_progs',
        'learninggoalwidgetid', ['learninggoalwidgetid']);
    // Add index learninggoalwidget_progs->topicid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_progs', 'topicid', ['topicid']);
    // Add index learninggoalwidget_progs->goalid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_progs', 'goalid', ['goalid']);
    // Add index learninggoalwidget_progs->userid.
    xmldb_learninggoalwidget_add_notunique_index($dbman, 'learninggoalwidget_progs', 'userid', ['userid']);

    // Delete unneeded fields
    // Delete learninggoalwidget_progs->course.
    xmldb_learninggoalwidget_delete_key($dbman, 'learninggoalwidget_progs', 'course');
    // Delete learninggoalwidget_progs->coursemodule.
    xmldb_learninggoalwidget_delete_key($dbman, 'learninggoalwidget_progs', 'coursemodule');

    // Delete unneeded tables.
    // Delete learninggoalwidget_i_topics.
    xmldb_learninggoalwidget_drop_table($dbman, 'learninggoalwidget_i_topics');

    // Delete learninggoalwidget_i_goals.
    xmldb_learninggoalwidget_drop_table($dbman, 'learninggoalwidget_i_goals');
}
