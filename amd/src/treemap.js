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

define(
  [
    "jquery",
    "mod_learninggoalwidget/controller",
    "mod_learninggoalwidget/treemap_module",
    "core/config",
    "core/notification",
  ], function(
    $,
    Controller,
    Treemap,
    Configuration,
    Notification,
  ) {

  /**
   * Intialise the treemap widget.
   * @param {*} treemapId The treemap ID
   * @param {*} courseid The course ID
   * @param {*} coursemoduleid The course module ID
   * @param {*} instanceid The course module instance ID
   * @param {*} treemapaccessibilitytext The accessibility text
   */
  const renderTreemap = (treemapId, courseid, coursemoduleid, instanceid, treemapaccessibilitytext) => {
    require.config({
      paths: {
        d3v7: Configuration.wwwroot + "/mod/learninggoalwidget/js/d3.v7.min"
      }
    });

    // Request learning goals taxonomy
    Controller.getLearningGoals({instanceid: instanceid})
      .then((jsonLearningGoals) => {
        const taxonomy = JSON.parse(jsonLearningGoals);
        if (taxonomy.children.length > 0) {
          renderTreemapView(taxonomy, treemapId, treemapaccessibilitytext, true);
        }
        return;
      }
      )
      .catch(Notification.exception);
  };

  /**
   * Render the treemap.
   * @param {*} taxonomy The learning goal taxonomy
   * @param {*} treemapId The treemap ID
   * @param {*} treemapaccessibilitytext The accessibility text
   * @param {*} showConfirmation True if users should confirm progress value changes
   */
  const renderTreemapView = (taxonomy, treemapId, treemapaccessibilitytext, showConfirmation) => {
    require(["d3v7"], (d3) => {
      Treemap.setupTreemap(taxonomy, d3, treemapId, treemapaccessibilitytext, showConfirmation, (map, obj, progress) => {
        saveProgress(
          getTreemapId(map),
          getInstanceId(map),
          obj.parent.data.topicid,
          obj.data.goalid,
          progress);
      });
      Treemap.setupSvg();
    });
  };

  /**
   * Update the users progress.
   * @param {*} treemapId The treemap ID
   * @param {*} instanceid The course module instance ID
   * @param {*} topicId The topic ID
   * @param {*} goalId The goal ID
   * @param {*} goalProgressValue The user progress
   */
  const saveProgress = (treemapId, instanceid, topicId, goalId, goalProgressValue) => {
    // Learninggoals webservice: save the learning goal progress for a learning goal
    Controller.updateUserProgress(
      {
        instanceid: instanceid,
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
      .catch(Notification.exception);
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The treemap instance ID
   */
  const getTreemapId = (element) => {
    const learningGoalWidgetElement = $(element).closest('div.learninggoalwidget');
    return $(learningGoalWidgetElement).data("treemap-id");
  };

  /**
   *
   * @param {*} element The learning goal widget element
   * @returns {number} The course module instance ID
   */
  const getInstanceId = (element) => {
    const learningGoalWidgetElement = $(element).closest('div.learninggoalwidget');
    return $(learningGoalWidgetElement).data("instance-id");
  };

  return {
    renderTreemap: renderTreemap,
    renderTreemapView: renderTreemapView
  };
});

