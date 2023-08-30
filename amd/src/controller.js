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
     * Insert a new topic.
     *
     * Valid args are:
     * int courseid             id of course this taxonomy belongs
     *
     * @method insertTopic
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const insertTopic = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_insert_topic',
        args: args
      };
      return Ajax.call([request])[0];
    };


    /**
     * Update a topic
     *
     * @method updateTopic
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const updateTopic = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_update_topic',
        args: args
      };
      return Ajax.call([request])[0];
    };


    /**
     * Delete a topic
     *
     * @method deleteTopic
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const deleteTopic = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_delete_topic',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Move up topic
     *
     * @method moveUpTopic
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const moveUpTopic = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_moveup_topic',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Move down topic
     *
     * @method moveDownTopic
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const moveDownTopic = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_movedown_topic',
        args: args
      };
      return Ajax.call([request])[0];
    };


    /**
     * Insert a new learning goal
     *
     * @method insertGoal
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const insertGoal = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_insert_goal',
        args: args
      };
      return Ajax.call([request])[0];
    };


    /**
     * Update a goal
     *
     * @method updateGoal
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const updateGoal = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_update_goal',
        args: args
      };
      return Ajax.call([request])[0];
    };


    /**
     * Delete a goal
     *
     * @method deleteGoal
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const deleteGoal = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_delete_goal',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Delete the whole taxonomy
     *
     * @method deleteTaxonomy
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const deleteTaxonomy = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_delete_taxonomy',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Add the whole taxonomy
     *
     * @method addTaxonomy
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const addTaxonomy = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_add_taxonomy',
        args: args
      };
      return Ajax.call([request])[0];
    };

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
     * Move up goal
     *
     * @method moveUpGoal
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const moveUpGoal = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_moveup_goal',
        args: args
      };
      return Ajax.call([request])[0];
    };

    /**
     * Move down goal
     *
     * @method moveDownGoal
     * @param  {object} args The request arguments
     * @return {promise} ajax call
     */
    const moveDownGoal = (args) => {
      const request = {
        methodname: 'mod_learninggoalwidget_movedown_goal',
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
      deleteTaxonomy: deleteTaxonomy,
      addTaxonomy: addTaxonomy,
      getTaxonomy: getTaxonomy,
      insertTopic: insertTopic,
      updateTopic: updateTopic,
      deleteTopic: deleteTopic,
      moveUpTopic: moveUpTopic,
      moveDownTopic: moveDownTopic,
      insertGoal: insertGoal,
      updateGoal: updateGoal,
      deleteGoal: deleteGoal,
      moveUpGoal: moveUpGoal,
      moveDownGoal: moveDownGoal,
      getLearningGoals: getLearningGoals,
      updateUserProgress: updateUserProgress,
      logEvent: logEvent,
    };
  }
);
