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
 * Web Service Definition for the learninggoals service.
 *
 * @package   mod_learninggoalwidget
 * @copyright University of Technology Graz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(
    'mod_learninggoalwidget_insert_topic' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'insert_topic',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_update_topic' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'update_topic',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_delete_topic' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'delete_topic',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_moveup_topic' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'moveup_topic',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_movedown_topic' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'movedown_topic',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_insert_goal' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'insert_goal',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_update_goal' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'update_goal',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_delete_goal' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'delete_goal',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_delete_taxonomy' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'delete_taxonomy',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Deletes the entire taxonomy',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_add_taxonomy' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'add_taxonomy',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Adds a whole taxonomy',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_get_taxonomy' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'get_taxonomy',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'gets the taxonomy',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_moveup_goal' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'moveup_goal',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_movedown_goal' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'movedown_goal',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_get_taxonomy_for_user' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'get_taxonomy_for_user',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'read',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_update_user_progress' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'update_user_progress',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_learninggoalwidget_log_event' => array(
        'classname' => 'mod_learninggoalwidget_external',
        'methodname' => 'log_event',
        'classpath' => 'mod/learninggoalwidget/externallib.php',
        'description' => 'Logs user interactions in the learning goals widget',
        'type' => 'write',
        'ajax' => true,
    ),

);
