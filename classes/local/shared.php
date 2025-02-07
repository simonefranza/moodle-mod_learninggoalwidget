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
   * @param stdClass child Child to check
   * @returns is child valid
   */
  public static function validate_children_properties(&$child) {
      // Ensure required properties exist and have correct types.
      $is_valid = true;
      if (!(isset($child->name) && is_string($child->name))) {
          $is_valid = false;
      } else if (!(isset($child->keyword) && is_string($child->keyword))) {
          $is_valid = false;
      } else if (!(isset($child->link) && is_string($child->link))) {
          $is_valid = false;
      } else if (!(isset($child->ranking) && is_int($child->ranking))) {
          $is_valid = false;
      }

      $child->valid = $is_valid;
      return $child->valid;
  }

}
