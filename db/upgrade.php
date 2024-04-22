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
 * @copyright 2021 Know Cener GmbH
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
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2024042202) {
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

        // Learninggoalwidget savepoint reached.
        upgrade_mod_savepoint(true, 2024042202, 'learninggoalwidget');
    }

    return true;
}
