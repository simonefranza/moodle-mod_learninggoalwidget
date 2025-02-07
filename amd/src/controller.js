// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// It under the terms of the GNU General Public License as published by
// The Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// But WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// Along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright University of Technology Graz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint "require-jsdoc": ["error", {
    "require": {
        "FunctionDeclaration": false,
        "MethodDefinition": false,
        "ClassDeclaration": false,
        "ArrowFunctionExpression": false,
        "FunctionExpression": false
    }
}] */

define(
  ['core/ajax'], function(Ajax) {
    /**
     * Get the taxonomy
     *
     * @method getTaxonomy
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const getTaxonomy = (args) => {
      var request = {
        methodname: 'mod_learninggoalwidget_get_taxonomy',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Get taxonomy with user's progress
     *
     * @method getLearningGoals
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const getLearningGoals = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_get_taxonomy_for_user',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Update learning progress for a goal and user
     *
     * @method updateUserProgress
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const updateUserProgress = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_update_user_progress',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Log user interaction event
     *
     * @method logEvent
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const logEvent = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_log_event',
        args: args
      };
      return Ajax.call([request])[0];
    };

    return {
      getTaxonomy: getTaxonomy,
      getLearningGoals: getLearningGoals,
      updateUserProgress: updateUserProgress,
      logEvent: logEvent,
    };
  }
);
