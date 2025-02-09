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
 * Trait shared by topics and goals
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

/**
 * Trait shared by topics and goals
 *
 * @package   mod_learninggoalwidget
 * @copyright 2025 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait shared {
    /**
     * Check that a child obj has the properties
     * - name (str)
     * - keyword (str)
     * - link (str)
     * - ranking (int)
     *
     * @param stdClass $child Child to check
     * @return is child valid
     */
    public static function validate_children_properties(&$child) {
        // Ensure required properties exist and have correct types.
        $isvalid = true;
        if (!(isset($child->name) && is_string($child->name))) {
            $isvalid = false;
        } else if (!(isset($child->keyword) && is_string($child->keyword))) {
            $isvalid = false;
        } else if (!(isset($child->link) && is_string($child->link))) {
            $isvalid = false;
        } else if (!(isset($child->ranking) && is_int($child->ranking))) {
            $isvalid = false;
        }

        $child->valid = $isvalid;
        return $child->valid;
    }
}
