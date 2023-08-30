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

/* eslint no-eval: 0 */
/* eslint no-bitwise: 0 */

// TODO
// Zoomed text jammed when going back after zoomed whil isZoomedIn

define(
  [
    "jquery",
    "mod_learninggoalwidget/controller",
    "mod_learninggoalwidget/treemap_module",
    "core/config"
  ], function(
    $,
    Controller,
    Treemap,
    Configuration
  ) {

  /**
   * Intialise the treemap widget.
   * @param {*} treemapId The treemap ID
   * @param {*} userId The user ID
   * @param {*} courseId The course ID
   * @param {*} courseModuleId The course module ID
   * @param {*} instanceId The course module instance ID
   * @param {*} treemapAccessibilityText The accessibility text
   */
  const renderTreemap = (treemapId, userId, courseId, courseModuleId, instanceId, treemapAccessibilityText) => {
    require.config({
      paths: {
        d3v7: Configuration.wwwroot + "/mod/learninggoalwidget/js/d3.v7.min"
      }
    });

    // Request learning goals taxonomy
    Controller.getLearningGoals({courseid: courseId, userid: userId, coursemoduleid: courseModuleId, instanceid: instanceId})
      .then((jsonLearningGoals) => {
        const taxonomy = JSON.parse(jsonLearningGoals);
        if (taxonomy.children.length > 0) {
          renderTreemapView(taxonomy, treemapId, treemapAccessibilityText, true);
        }
        return;
      }
      )
      .catch(() => {
        // Do nothing
      });

  };

  /**
   * Render the treemap.
   * @param {*} taxonomy The learning goal taxonomy
   * @param {*} treemapId The treemap ID
   * @param {*} treemapAccessibilityText The accessibility text
   * @param {*} showConfirmation True if users should confirm progress value changes
   */
  const renderTreemapView = (taxonomy, treemapId, treemapAccessibilityText, showConfirmation) => {
    require(["d3v7"], (d3) => {
      Treemap.setupTreemap(taxonomy, d3, treemapId, treemapAccessibilityText, showConfirmation, (map, obj, progress) => {
        saveProgress(
          getTreemapId(map),
          getCourseId(map),
          getCourseModuleId(map),
          getInstanceId(map),
          getUserId(map),
          obj.parent.data.topicid,
          obj.data.goalid,
          obj.data.name,
          progress);
      });
      Treemap.setupSvg();
    });
  };

  /**
   * Update the users progress.
   * @param {*} treemapId The treemap ID
   * @param {*} courseId The course ID
   * @param {*} courseModuleId The course module ID
   * @param {*} instanceId The course module instance ID
   * @param {*} userId The user ID
   * @param {*} topicId The topic ID
   * @param {*} goalId The goal ID
   * @param {*} goalName The name of the goal
   * @param {*} goalProgressValue The user progress
   */
  const saveProgress = (treemapId, courseId, courseModuleId, instanceId, userId, topicId,
    goalId, goalName, goalProgressValue) => {
    // Learninggoals webservice: save the learning goal progress for a learning goal
    Controller.updateUserProgress(
      {
        courseid: courseId,
        coursemoduleid: courseModuleId,
        instanceid: instanceId,
        userid: userId,
        topicid: topicId,
        goalid: goalId,
        progress: goalProgressValue
      }
    )
      .then((taxonomy) => {
        const loadedTaxonomy = JSON.parse(taxonomy);
        if (loadedTaxonomy.children.length === 0) {
          return;
        }
        const updateLearningGoalProgressEvent = new CustomEvent('update_learning_goal_progress', {
          bubbles: true,
          detail: {
            sender: "treemap",
            taxonomy: loadedTaxonomy,
            treemapId: treemapId
          }
        });

        $('#' + treemapId)[0].dispatchEvent(updateLearningGoalProgressEvent);
        return;
      }
      )
      .catch(() => {
        // Do nothing
      });

    // Log save progress event
    let learningGoalEvent = createLearningGoalEvent(
      "preparationSaveProgress",
      courseId,
      courseModuleId,
      instanceId,
      userId);
    let eventGoalParam = {name: "goalname", value: goalName};
    let eventGoalProgressParam = {name: "goalprogress", value: goalProgressValue};
    learningGoalEvent.push(eventGoalParam);
    learningGoalEvent.push(eventGoalProgressParam);
    logLearningGoalEvent(courseId, courseModuleId, instanceId, userId, learningGoalEvent);
  };

  /**
   * Create learning goal event parameters
   * @param {*} courseId The course ID
   * @param {*} courseModuleId The course module ID
   * @param {*} instanceId The course module instance ID
   * @param {*} userId The user ID
   * @returns {Array} The array of learning goal event parameters
   */
  const createLearningGoalEvent = (courseId, courseModuleId, instanceId, userId) => {
    const eventParam = {};
    const eventCourseParam = {name: "courseid", value: courseId};
    const eventCourseModuleParam = {name: "coursemoduleid", value: courseModuleId};
    const eventInstanceParam = {name: "instanceid", value: instanceId};
    const eventUserParam = {name: "userid", value: userId};
    const timestampParam = {name: "timestamp", value: Math.trunc(new Date().getTime() / 1000)};

    return [eventParam, eventCourseParam, eventCourseModuleParam, eventInstanceParam, eventUserParam, timestampParam];
  };

  /**
   * Logs learning goal events into moodles standard log store
   * @param {*} courseId The course ID
   * @param {*} courseModuleId The course module ID
   * @param {*} instanceId The course module instance ID
   * @param {*} userId The user ID
   * @param {*} eventParams The learning goal event parameters
   */
  const logLearningGoalEvent = (courseId, courseModuleId, instanceId, userId, eventParams) => {
    Controller.logEvent(
      {
        courseid: courseId,
        coursemoduleid: courseModuleId,
        instanceid: instanceId,
        userid: userId,
        eventparams: eventParams
      }
    );
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The treemap instance ID
   */
  const getTreemapId = (element) => {
    const learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
    return $(learningGoalWidgetElement).data("treemap-id");
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The course ID
   */
  const getCourseId = (element) => {
    const learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
    return $(learningGoalWidgetElement).data("course-id");
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The course module ID
   */
  const getCourseModuleId = (element) => {
    const learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
    return $(learningGoalWidgetElement).data("coursemodule-id");
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The course module instance ID
   */
  const getInstanceId = (element) => {
    const learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
    return $(learningGoalWidgetElement).data("instance-id");
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The user ID
   */
  const getUserId = (element) => {
    let learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
    return $(learningGoalWidgetElement).data("user-id");
  };

  return {
    renderTreemap: renderTreemap,
    renderTreemapView: renderTreemapView
  };
});

