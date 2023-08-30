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
  [
    "jquery",
    "mod_learninggoalwidget/sunburst",
    "mod_learninggoalwidget/treemap"
  ], function(
    $,
    Sunburst,
    Treemap
  ) {

  /**
   * Intialise the widget and its content and exam views.
   *
   * @param {object} root The root element of the learning goals widget.
   */
  const initViews = (root) => {
    root = $(root);

    Sunburst.renderSunburst(root.data("sunburst-id"),
      root.data("user-id"),
      root.data("course-id"),
      root.data("coursemodule-id"),
      root.data("instance-id"),
      root.attr("data-progresslegendLabel"));

    Treemap.renderTreemap(root.data("treemap-id"),
      root.data("user-id"),
      root.data("course-id"),
      root.data("coursemodule-id"),
      root.data("instance-id"),
      root.attr("data-treemapAccessibilityText"));

    document.getElementById(root.data("course-id") + "-"
      + root.data("coursemodule-id") + "-"
      + root.data("instance-id")
      + "-treemap-thumbnail").onclick = function() {
        $("#" + root.data("treemap-id") + "-container").removeClass("d-none");
        $("#" + root.data("treemap-id")).empty();
        Treemap.renderTreemap(root.data("treemap-id"),
          root.data("user-id"),
          root.data("course-id"),
          root.data("coursemodule-id"),
          root.data("instance-id"),
          root.attr("data-treemapAccessibilityText"));
        $("#" + root.data("sunburst-id") + "-container").addClass("d-none");
      };

    document.getElementById(root.data("course-id") + "-" + root.data("coursemodule-id") + "-"
      + root.data("instance-id")
      + "-sunburst-thumbnail").onclick = function() {
        $("#" + root.data("treemap-id") + "-container").addClass("d-none");
        $("#" + root.data("sunburst-id") + "-container").removeClass("d-none");
      };

    $("#" + root.data("treemap-id") + "-container").addClass("d-none");
    $("#" + root.data("sunburst-id") + "-container").removeClass("d-none");

    // Update visualisations whenever a learning goal's progress changes
    root.on("update_learning_goal_progress", function(updateLearningGoalProgressEvent) {
      if (updateLearningGoalProgressEvent.detail.sender === "sunburst") {
        $("#" + root.data("treemap-id")).empty();
        Treemap.renderTreemapView(
          updateLearningGoalProgressEvent.detail.taxonomy,
          root.data("treemap-id"),
          root.attr("data-treemapAccessibilityText"));
      }
      if (updateLearningGoalProgressEvent.detail.sender === "treemap") {
        $("div#" + root.data("sunburst-id") + "-taxonomy-userprogress-chart-fullgoal").remove();
        $("#" + root.data("sunburst-id") + "-taxonomy-userprogress-chart").empty();
        $("#" + root.data("sunburst-id") + "-taxonomy-userprogress-legend").empty();
        Sunburst.renderSunburstWithProgressView(
          updateLearningGoalProgressEvent.detail.taxonomy,
          root.data("sunburst-id"),
          root.attr("data-progresslegendLabel"));
      }
    });
  };

  return {
    initViews: initViews
  };
}
);
