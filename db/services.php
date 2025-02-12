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
];
