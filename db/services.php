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
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = [
    // Taxonomy services.
    'mod_learninggoalwidget_get_taxonomy' => [
        'classname'   => 'mod_learninggoalwidget\external\get_taxonomy',
        'description' => 'Retrieves the taxonomy.',
        'type'        => 'read',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_get_taxonomy_for_user' => [
        'classname'   => 'mod_learninggoalwidget\external\get_taxonomy_for_user',
        'description' => 'Retrieves the taxonomy of a specific user.',
        'type'        => 'read',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_update_user_progress' => [
        'classname'   => 'mod_learninggoalwidget\external\update_user_progress',
        'description' => 'Change the progress of a user on a goal',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    // Topic services.
    'mod_learninggoalwidget_insert_topic' => [
        'classname'   => 'mod_learninggoalwidget\external\insert_topic',
        'description' => 'Inserts a topic into the taxonomy.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_update_topic' => [
        'classname'   => 'mod_learninggoalwidget\external\update_topic',
        'description' => 'Update a topic of the taxonomy.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_delete_topic' => [
        'classname'   => 'mod_learninggoalwidget\external\delete_topic',
        'description' => 'Delete a topic from the taxonomy.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_moveup_topic' => [
        'classname'   => 'mod_learninggoalwidget\external\moveup_topic',
        'description' => 'Move a topic before the preceding one (decrease ranking)',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_movedown_topic' => [
        'classname'   => 'mod_learninggoalwidget\external\movedown_topic',
        'description' => 'Move a topic behind the succeeding one (increase ranking)',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    // Goal services.
    'mod_learninggoalwidget_insert_goal' => [
        'classname'   => 'mod_learninggoalwidget\external\insert_goal',
        'description' => 'Inserts a goal into the taxonomy.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_update_goal' => [
        'classname'   => 'mod_learninggoalwidget\external\update_goal',
        'description' => 'Update a goal of the taxonomy.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_delete_goal' => [
        'classname'   => 'mod_learninggoalwidget\external\delete_goal',
        'description' => 'Delete a goal from the taxonomy.',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_moveup_goal' => [
        'classname'   => 'mod_learninggoalwidget\external\moveup_goal',
        'description' => 'Move a goal before the preceding one (decrease ranking)',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_movedown_goal' => [
        'classname'   => 'mod_learninggoalwidget\external\movedown_goal',
        'description' => 'Move a goal behind the succeeding one (increase ranking)',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    // Taxonomy Control with JSON files.
    'mod_learninggoalwidget_add_taxonomy' => [
        'classname'   => 'mod_learninggoalwidget\external\add_taxonomy',
        'description' => 'Add a whole taxonomy',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'mod_learninggoalwidget_delete_taxonomy' => [
        'classname'   => 'mod_learninggoalwidget\external\delete_taxonomy',
        'description' => 'Deletes the entire taxonomy',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    // Events.
    'mod_learninggoalwidget_log_event' => [
        'classname'   => 'mod_learninggoalwidget\external\log_event',
        'description' => 'Logs user interactions in the learning goals widget',
        'type'        => 'write',
        'ajax'        => true,
        'services' => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],

];
