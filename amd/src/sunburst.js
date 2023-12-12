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
        "mod_learninggoalwidget/controller",
        "mod_learninggoalwidget/selectors",
        "core/config",
        "core/notification",
        "core/templates",
        "core/modal_factory",
        "core/modal_events"
    ], function(
        $,
        Controller,
        Selectors,
        Configuration,
        Notification,
        Templates,
        ModalFactory,
        ModalEvents
    ) {

    var progressModalsDict = {};

    var TEMPLATES = {
        EDIT_PROGRESS_VALUES: "mod_learninggoalwidget/widget/sunburst-edit-progress-view"
    };

    /**
     * Intialise the sunburst widget.
     * @param {*} sunburstId The sunburst widget instance ID
     * @param {*} userId The user ID
     * @param {*} courseId The course ID
     * @param {*} courseModuleId The course module ID
     * @param {*} instanceId The course module instance ID
     * @param {*} progressLegendLabel The legend string
     */
    var renderSunburst = function(sunburstId, userId, courseId, courseModuleId, instanceId, progressLegendLabel) {
        require.config({
            paths: {
                d3v7: Configuration.wwwroot + "/mod/learninggoalwidget/js/d3.v7.min"
            }
        });

        // Request learning goals taxonomy
        Controller.getLearningGoals({courseid: courseId, userid: userId, coursemoduleid: courseModuleId, instanceid: instanceId})
            .then((jsonLearningGoals) => {

                    var loadedTaxonomy = JSON.parse(jsonLearningGoals);
                    if (loadedTaxonomy.children.length > 0) {
                        renderSunburstView(loadedTaxonomy, sunburstId);
                        renderSunburstWithProgressView(loadedTaxonomy, sunburstId, progressLegendLabel);
                        renderTextualBulletPointList(loadedTaxonomy, sunburstId);
                    }
                    return 0;
                }
            )
            .catch(function() {
                // Do nothing
            });

        // Adding functionality to the elements
        document.getElementById(sunburstId + "-ClickedOverview").onclick = function() {

            var sunburstId = getSunburstId(this);
            var courseId = getCourseId(this);
            var courseModuleId = getCourseModuleId(this);
            var instanceId = getInstanceId(this);
            var userId = getUserId(this);

            changeView("Overview", sunburstId);

            // Log overview click event
            var learningGoalEvent = createLearningGoalEvent("clickedOverview", courseId, courseModuleId, instanceId, userId);
            logLearningGoalEvent(courseId, courseModuleId, instanceId, userId, learningGoalEvent);
        };

        document.getElementById(sunburstId + "-ClickedPreparation").onclick = function() {

            var sunburstId = getSunburstId(this);
            var courseId = getCourseId(this);
            var courseModuleId = getCourseModuleId(this);
            var instanceId = getInstanceId(this);
            var userId = getUserId(this);

            changeView("Preparation", sunburstId);

            // Log preparation click event
            var learningGoalEvent = createLearningGoalEvent("clickedPreparation", courseId, courseModuleId, instanceId, userId);
            logLearningGoalEvent(courseId, courseModuleId, instanceId, userId, learningGoalEvent);
        };

        // Setting the visualisation container to fit nicely ;)
        document.querySelector("div[data-region='" + sunburstId + "-content-view']").parentElement.style.position = "relative";
        document.querySelector("div[data-region='" + sunburstId + "-content-view']").style.width = "33%";
        document.querySelector("div[data-region='" + sunburstId + "-content-view']").style.height = "100%";
        document.querySelector("div[data-region='" + sunburstId + "-exam-view']").style.width = "33%";
        document.querySelector("div[data-region='" + sunburstId + "-exam-view']").style.height = "100%";

        // Hiding the second chart by default
        $(document.querySelector("div[data-region='" + sunburstId + "-content-view']")).removeClass("d-none");
        $(document.querySelector("div[data-region='" + sunburstId + "-exam-view']")).addClass("d-none");

    };

    /**
     * Toggle between views with and without coloring the user progress in the sunburst
     * @param {*} element The button HTML element
     * @param {*} sunburstId The sunburst instance ID
     */
    var changeView = function(element, sunburstId) {
        if (element == "Overview") {
            $(document.querySelector("div[data-region='" + sunburstId + "-content-view']")).removeClass("d-none");
            $(document.querySelector("div[data-region='" + sunburstId + "-exam-view']")).addClass("d-none");
        } else {
            $(document.querySelector("div[data-region='" + sunburstId + "-content-view']")).addClass("d-none");
            $(document.querySelector("div[data-region='" + sunburstId + "-exam-view']")).removeClass("d-none");
        }
    };

    /**
     * Render the learning goal taxonomy as a bullet point list of topics and intended goals
     * @param {*} courseTaxonomy The learning goal taxonomy
     * @param {*} sunburstId The sunburst instance ID
     */
    var renderTextualBulletPointList = function(courseTaxonomy, sunburstId) {
        var rootElement = document.getElementById(sunburstId + "-listing-view");
        rootElement.style.position = "relative";
        addChildren(rootElement, courseTaxonomy.children, sunburstId);
    };

    /**
     * Adding a new level of topics or goals to the root element
     * @param {*} element The root element
     * @param {*} children The child elements
     * @param {*} sunburstId The sunburst instance ID
     */
    var addChildren = function(element, children, sunburstId) {
        var unorderedListNode = document.createElement("ul");
        unorderedListNode.style = "list-style: none";
        children.forEach(
            function(child) {
                if (child.type == "topic") {
                    appendTopic(sunburstId, $("#" + sunburstId + "-listing-view"), child);
                }
                if (child.type == "goal") {
                    appendGoal(sunburstId, unorderedListNode, child);
                }
                if (child.children) {
                    addChildren($("#" + sunburstId + "-listing-view"), child.children, sunburstId);
                }
            }
        );
        $("#" + sunburstId + "-listing-view").append(unorderedListNode);
    };

    /**
     * Adding a new topic to the root element
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} element The root element
     * @param {*} topic The topic element
     */
    var appendTopic = function(sunburstId, element, topic) {
        var paragraphNode = document.createElement("p");

        paragraphNode.setAttribute("id", sunburstId + "-topic-" + topic.topicid);

        var spanNode = document.createElement("span");
        var topicText = "";
        if (topic.keyword != "") {
            topicText = topic.keyword + " - " + topic.name;
        } else {
            topicText = topic.name;
        }
        if (topic.link) {
            var linkNode = document.createElement("a");
            linkNode.setAttribute("href", topic.link);
            linkNode.setAttribute("target", "_blank");
            linkNode.textContent = topicText;
            spanNode.appendChild(linkNode);
        } else {
            spanNode.textContent = topicText;
        }
        paragraphNode.appendChild(spanNode);
        element.append(paragraphNode);
    };

    /**
     * Adding a new goal to the root element
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} element The root element
     * @param {*} goal The goal element
     */
    var appendGoal = function(sunburstId, element, goal) {
        var listItemNode = document.createElement("li");

        listItemNode.setAttribute("id", sunburstId + "-goal-" + goal.goalid);

        var bulletPoint = document.createElement('span');
        bulletPoint.className = "bulletPoint";
        listItemNode.appendChild(bulletPoint);

        var goalText = "";

        if (goal.keyword != "") {
            goalText = goal.keyword + " - " + goal.name;
        } else {
            goalText = goal.name;
        }

        if (goal.link) {
            var linkNode = document.createElement("a");
            linkNode.setAttribute("href", goal.link);
            linkNode.setAttribute("target", "_blank");
            linkNode.textContent = goalText;
            listItemNode.appendChild(linkNode);
        } else {
            var spanNode = document.createElement("span");
            spanNode.textContent = goalText;
            listItemNode.appendChild(spanNode);
        }

        element.append(listItemNode);
    };

    /**
     * Highlight the topic / goal in the textual represenation of the chart
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} item The HTML element to update
     * @param {*} color The background color
     * @param {*} highlight True if element should get highlighted otherwise false
     */
    var updateItemInTextualHierachy = function(sunburstId, item, color, highlight) {
        let element;
        if (item.data.type === "goal") {
            element = $("#" + sunburstId + "-goal-" + item.data.goalid);
        }
        if (item.data.type === "topic") {
            element = $("#" + sunburstId + "-topic-" + item.data.topicid);
        }

        if (element) {
            let bulletPointElement;
            if (highlight) {
                element.addClass("taxItemHighlighted");
                bulletPointElement = element[0].querySelector('.bulletPoint');
                if (bulletPointElement && color) {
                    bulletPointElement.style.background = color;
                }
                if (item.children) {
                    childrenHighlight(sunburstId, item.children, true);
                }
                scrollToTopic(sunburstId, item);
            } else {
                element.removeClass("taxItemHighlighted");
                bulletPointElement = element[0].querySelector('.bulletPoint');
                if (bulletPointElement) {
                    bulletPointElement.style.background = 'black';
                }
                if (item.children) {
                    childrenHighlight(sunburstId, item.children, false);
                }
            }
        }
    };

    /**
     * Automatically scroll to an topic or goal in the bullet point list
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} item The HTML element
     */
    var scrollToTopic = function(sunburstId, item) {
        let element;
        if ('data' in item && 'type' in item.data) {
            switch (item.data.type) {
              case 'goal':
                element = document.getElementById(sunburstId + "-goal-" + item.data.goalid);
                break;
              case 'topic':
                element = document.getElementById(sunburstId + "-topic-" + item.data.topicid);
                break;
              default:
                throw new Error("Unknown type: " + item.data.type);
            }
            element.scrollIntoView({behavior: "smooth", block: "nearest"});
        }
    };

    /**
     * Highlight or unhighlight all the children at once
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} children The list of children
     * @param {*} highlight True if childs should get highlighted otherwise false
     */
    var childrenHighlight = function(sunburstId, children, highlight) {
        children.forEach((child) => {
                let element;
                if (child.data.type === "goal") {
                    element = $("#" + sunburstId + "-goal-" + child.data.goalid);
                }
                if (child.data.type === "topic") {
                    element = $("#" + sunburstId + "-topic-" + child.data.topicid);
                }
                let bulletPointElement;
                if (element) {
                    if (highlight) {
                        element.addClass("taxItemHighlighted");
                        bulletPointElement = element[0].querySelector('.bulletPoint');
                        var color = getColor(percentage(child));
                        if (bulletPointElement && color) {
                            bulletPointElement.style.background = color;
                        }
                    } else {
                        element.removeClass("taxItemHighlighted");
                        bulletPointElement = element[0].querySelector('.bulletPoint');
                        if (bulletPointElement) {
                            bulletPointElement.style.background = 'black';
                        }
                    }
                }
                if (child.children) {
                    childrenHighlight(sunburstId, child.children, highlight);
                }
            }
        );
    };

    /**
     * Render the sunburst view
     * @param {*} courseTaxonomy The learning goal taxonomy
     * @param {*} sunburstId The sunburst instance ID
     */
    var renderSunburstView = function(courseTaxonomy, sunburstId) {
        require(["d3v7"], function(d3) {
            // Reading the header name of the json data and pasting it before chart container
            d3.select("#" + sunburstId + "-taxonomy")
                .append("p")
                .attr("class", "skipLine");

            //  Window for the tooltip
            var div = d3
                .select("#" + sunburstId + "-container")
                .append("div")
                .attr("id", sunburstId + "-taxonomy-fullgoal")
                .style("opacity", 0)
                .style("position", "absolute")
                .style("vertical-align", "center")
                .style("text-align", "center")
                .style("width", "12vw")
                .style("min-height", "4vh")
                .style("font", "sans-serif")
                .style("background", "rgb(106, 115, 123)")
                .style("border", "1px solid rgb(91, 99, 105)")
                .style("color", "white")
                .style("border-radius", "0.2rem")
                .style("padding-inline", "5px")

                .style("pointer-events", "none");

            // Dimensions
            var width = 360,
                height = 360,
                radius = Math.min(width, height) / 2;
            // Setting up the svg container
            var g = d3
                .select("#" + sunburstId + "-taxonomy")
                .append("svg")
                .attr("class", "svgChart")
                .attr("viewBox", "0 0 " + width + " " + height)
                .append("g")
                .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
                .attr("class", "mainG");

            // Svg container for text path
            d3.select("#" + sunburstId + "-text-path-storage")
                .append("svg")
                .attr("id", sunburstId + "-PathBlock")
                .attr("height", "0%")
                .attr("width", "0%")
                .style("display", "none");

            // Data structure
            var partition = d3.partition().size([2 * Math.PI, radius]);
            // Var maxdepth = 0;

            var root = d3.hierarchy(courseTaxonomy).eachBefore((d) => {
              let sum = 1;
              if (d.depth > 0) {
                for (let i = 0; i < d.depth; i++) {
                  let el = d;
                  for (let j = 0; j < i + 1; j++) {
                    el = el.parent;
                  }
                  sum *= el.children.length;
                }
              }
              d.value = 1 / sum;
              d.data.size = d.value;
            }
            );

            // Retrieving sizes of the arcs
            partition(root);

            var arc = d3
                .arc()
                .startAngle(
                    function(d) {
                        return d.x0;
                    }
                )
                .endAngle(
                    function(d) {
                        return d.x1;
                    }
                )
                .innerRadius(
                    function(d) {
                        return d.y0;
                    }
                )
                .outerRadius(
                    function(d) {
                        return d.y1;
                    }
                );

            /**
             * Recieving the path for text placed on the arcs
             * @param {*} d Data model
             * @param {*} arc Svg arc
             * @returns {string} Text path
             */
            function getTextPath(d, arc) {
                let newArc;
                if ((d.depth == 1 && d.parent.children.length == 1)
                    || (d.depth == 2
                        && d.parent.children.length == 1
                        && d.parent.parent.children.length == 1)
                ) {
                    newArc = arc.replace(/,/g, " ");
                    var loc = /^([^A]*A[^A]*)/;
                    newArc = loc.exec(newArc)[1];
                    return newArc;
                } else {
                    var firstArcSection = /(^.+?)L/;
                    newArc = firstArcSection.exec(arc)[1];
                    newArc = newArc.replace(/,/g, " ");
                    var angle = ((d.x0 + d.x1) / Math.PI) * 90;
                    if (angle > 90 && angle < 270) {
                        var startLoc = /M(.*?)A/;
                        var middleLoc = /A(.*?)0 0 1/;
                        var endLoc = /0 0 1 (.*?)$/;
                        var newStart = endLoc.exec(newArc)[1];
                        var newEnd = startLoc.exec(newArc)[1];
                        var middleSec = middleLoc.exec(newArc)[1];
                        newArc = "M" + newStart + "A" + middleSec + "0 0 0 " + newEnd;
                        return newArc;
                    }
                    return newArc;
                }
            }

            // Building the chart
            const container = document.querySelector('#' + sunburstId + '-container');
            g.selectAll("g")
                .data(root.descendants())
                .enter()
                .append("g")
                .attr("class", "node")
                .append("path")
                .attr(
                    "class", function(d) {
                        return d.data.name;
                    }
                )
                .attr(
                    "display", function(d) {
                        return d.depth ? null : "none";
                    }
                )
                .style("stroke", "#fff")
                .attr("stroke-width", "3")
                .style("fill", "#e5e5e5")
                .style(
                    "cursor", function(d) {
                        if (d.data.link) {
                            return "pointer";
                        } else {
                            return "default";
                        }
                    }
                )
                .attr("d", arc)
                .on(
                    "mouseover", function(e, d) {
                        const rect = container.getBoundingClientRect();
                        div
                            .transition()
                            .duration(200)
                            .style("opacity", 0.95);
                        div
                            .html(d.data.name)
                            .style("left", "" + (e.clientX - rect.x) + "px")
                            .style("top", "" + (e.clientY - height / 10 - rect.y) + "px");

                        // Highlight the topic / goal in the textual represenation of the chart
                        updateItemInTextualHierachy(sunburstId, d, getColor(percentage(d)), true);
                    }
                )
                .on(
                    "mouseout", function(_, d) {
                        div
                            .transition()
                            .duration(500)
                            .style("opacity", 0);

                        // Highlight the topic / goal in the textual represenation of the chart
                        updateItemInTextualHierachy(sunburstId, d, getColor(percentage(d)), false);
                    }
                )
                .on(
                    "click", function(_, d) {
                        if (d.depth >= 1) {
                            if (d.data.link) {
                                window.open(d.data.link);
                                // Log url click event
                                var courseId = getCourseId(this);
                                var courseModuleId = getCourseModuleId(this);
                                var instanceId = getInstanceId(this);
                                var userId = getUserId(this);

                                var learningGoalEvent = createLearningGoalEvent("overviewOpenLink",
                                    courseId, courseModuleId, instanceId, userId);
                                var eventLinkParam = new Object();
                                eventLinkParam.name = "url";
                                eventLinkParam.value = d.data.link;
                                learningGoalEvent.push(eventLinkParam);
                                logLearningGoalEvent(courseId, courseModuleId, instanceId, userId, learningGoalEvent);
                            }

                            const sunburstClickEvent = new CustomEvent('sunburstclick', {
                                bubbles: true,
                                detail: {
                                    data: () => d.data
                                }
                            });
                            $('#' + sunburstId + '-taxonomy')[0].dispatchEvent(sunburstClickEvent);
                        }
                    }
                )
                .each(
                    function(d, i) {
                        if (d.depth > 0) {
                            var arc = d3.select(this).attr("d");
                            var newArc = getTextPath(d, arc);
                            d3.select("#" + sunburstId + "-PathBlock")
                                .append("path")
                                .attr("id", sunburstId + "skillArc_" + i)
                                .attr("d", newArc)
                                .style("fill", "none");
                        }
                    }
                );

            // Making the text rotation based on the angle
            var rotate = function(d) {
                var angle = ((d.x0 + d.x1) / Math.PI) * 90;
                if (angle <= 90 && angle >= 0) {
                    return angle - 90;
                } else if (angle > 90 && angle < 180) {
                    return (90 - angle) * -1;
                } else if (angle >= 180) {
                    return angle - 270;
                } else {
                    // Console.error("error while finding fitting angle");
                    return 0;
                }
            };

            // Calc the depth of the json
            var getDepth = (nodes) => {
                var depth = 0;
                if (nodes.children) {
                    nodes.children.forEach((d) => {
                            var cur = getDepth(d);
                            if (cur > depth) {
                                depth = cur;
                            }
                            return undefined;
                        }
                    );
                }
                return 1 + depth;
            };
            var VerNum = 18 / getDepth(root) - 1;
            g.selectAll(".node")
                .data(root.descendants())
                .append("text")
                .text((d) => {
                        if (d.depth > 0 && d.data.size <= 0.1) {
                            var title = d.data.name.toString();
                            if (d.data.keyword.length > 0) {
                                title = d.data.keyword;
                            }
                            if (title.length > VerNum) {
                                return title.substring(0, VerNum) + "...";
                            } else {
                                return title;
                            }
                        }
                        return undefined;
                    }
                )
                .attr("transform", (d) => {
                        if (d.depth > 0 && d.data.size <= 0.1) {
                            return "translate(" + arc.centroid(d) + ")rotate(" + rotate(d) + ")";
                        }
                        return undefined;
                    }
                )
                .style("font-size", 12)
                .style('pointer-events', 'none')
                .attr("dx", "-20")
                .attr("dy", ".5em");

            // Labeling goals and topic inside the arcs
            // Circle is 360', so 90 chars will give 1 char per 4'
            var maxLetters = 90;
            if (courseTaxonomy.children.length > 1) {
                maxLetters = maxLetters - 2 * courseTaxonomy.children.length;
            }
            g.selectAll(".node")
                .data(root.descendants())
                .append("text")
                .attr(
                    "dy", function(d) {
                        if ((d.depth == 1 && d.parent.children.length == 1)
                            || (d.depth == 2
                                && d.parent.children.length == 1
                                && d.parent.parent.children.length == 1)
                        ) {
                            return 30;
                        } else {
                            var angle = ((d.x0 + d.x1) / Math.PI) * 90;
                            if (angle > 90 && angle < 270) {
                                return -25;
                            } else {
                                return 30;
                            }
                        }
                    }
                )
                .append("textPath")
                .attr(
                    "xlink:href", function(d, i) {
                        return "#" + sunburstId + "skillArc_" + i;
                    }
                )
                .style("text-anchor", "middle") // Place the text halfway on the arc
                .attr("startOffset", "50%")
                .style("font-size", 12)
                .style('pointer-events', 'none')
                .attr("letter-spacing", 2.75)
                .text((d) => {
                        if (d.depth > 0 && d.data.size > 0.1) {
                            var CharNum = Math.round(d.data.size * maxLetters);
                            var title = d.data.name;
                            if (d.data.keyword.length > 0) {
                                title = d.data.keyword;
                            }
                            if (title.length > CharNum) {
                                return title.substring(0, CharNum - 3) + "...";
                            }
                            return title;
                        }
                        return undefined;
                    }
                );
        });
    };
    /**
     * Render the sunburst view coloring the arcs with the users progress
     * @param {*} courseTaxonomy The learning goal taxonomy
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} progressLegendLabel The legend string
     */
    var renderSunburstWithProgressView = function(courseTaxonomy, sunburstId, progressLegendLabel) {
        require(["d3v7"], function(d3) {
            // Reading the header name of the json data and pasting it before chart container
            d3.select("#" + sunburstId + "-taxonomy-userprogress-chart")
                .append("p")
                .attr("class", "skipLine");

            var div = d3
                .select("#" + sunburstId + "-container")
                .append("div")
                .attr("id", sunburstId + "-taxonomy-userprogress-chart-fullgoal")
                .style("opacity", 0)
                .style("position", "absolute")
                .style("vertical-align", "center")
                .style("text-align", "center")
                .style("width", "12vw")
                .style("min-height", "4vh")
                .style("font", "sans-serif")
                .style("background", "rgb(106, 115, 123)")
                .style("border", "1px solid rgb(91, 99, 105)")
                .style("color", "white")
                .style("border-radius", "0.2rem")
                .style("padding-inline", "5px")
                .style("pointer-events", "none");

            // Dimensions
            var width = 360,
                height = 360,
                radius = Math.min(width, height) / 2;
            // Setting up the svg container
            var g = d3
                .select("#" + sunburstId + "-taxonomy-userprogress-chart")
                .append("svg")
                .attr("class", "svgChart")
                .attr("viewBox", "0 0 " + width + " " + height)
                .append("g")
                .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
                .attr("class", "mainG");

            // Data structure
            var partition = d3.partition().size([2 * Math.PI, radius]);

            let root = d3.hierarchy(courseTaxonomy).eachBefore((d) => {
              let sum = 1;
              if (d.depth > 0) {
                for (let i = 0; i < d.depth; i++) {
                  let el = d;
                  for (let j = 0; j < i + 1; j++) {
                    el = el.parent;
                  }
                  sum *= el.children.length;
                }
              }
              d.value = 1 / sum;
              d.data.size = d.value;
            }
            );

            // Retrieving sizes of the arcs
            partition(root);
            var arc = d3
                .arc()
                .startAngle(
                    function(d) {
                        return d.x0;
                    }
                )
                .endAngle(
                    function(d) {
                        return d.x1;
                    }
                )
                .innerRadius(
                    function(d) {
                        return d.y0;
                    }
                )
                .outerRadius(
                    function(d) {
                        return d.y1;
                    }
                );

            // Building the chart
            const container = document.querySelector('#' + sunburstId + '-container');
            g.selectAll("g")
                .data(root.descendants())
                .enter()
                .append("g")
                .attr("class", "node")
                .append("path")
                .attr(
                    "class", function(d) {
                        return d.data.name;
                    }
                )
                .attr(
                    "display", function(d) {
                        return d.depth ? null : "none";
                    }
                )
                .style("stroke", "#fff")
                .attr("stroke-width", "3")
                .style("fill", (d) => {
                        var per = percentage(d);
                        if (per !== null && per !== undefined) {
                            if (per.toString().length > 0) {
                                return getColor(per);
                            }
                        } else {
                            return getColor(0);
                        }
                        return undefined;
                    }
                )
                .style("cursor", (d) => {
                        if (!d.children) {
                            return "pointer";
                        }
                        return undefined;
                    }
                )
                .attr("id", (d, i) => {
                        if (d.depth == 1) {
                            return "ArcNum" + i;
                        }
                        return undefined;
                    }
                )
                .attr("d", arc)
                .on("mouseover", (e, d) => {
                        const rect = container.getBoundingClientRect();
                        div
                            .transition()
                            .duration(200)
                            .style("opacity", 0.95);
                        div
                            .html(d.data.name)
                            .style("left", (e.clientX - rect.x) + "px")
                            .style("top", (e.clientY - height / 10 - rect.y) + "px");

                        // Highlight the topic / goal in the textual represenation of the chart
                        updateItemInTextualHierachy(sunburstId, d, getColor(percentage(d)), true);
                    }
                )
                .on("mouseout", (_, d) => {
                        div
                            .transition()
                            .duration(500)
                            .style("opacity", 0);

                        // Highlight the topic / goal in the textual represenation of the chart
                        updateItemInTextualHierachy(sunburstId, d, getColor(percentage(d)), false);
                    }
                )
                .on(
                    "click", function(e, d) {
                        if (!d.children) {
                            var courseId = getCourseId(this);
                            var courseModuleId = getCourseModuleId(this);
                            var instanceId = getInstanceId(this);
                            var sunburstId = getSunburstId(this);
                            var userId = getUserId(this);
                            var goalName = d.data.name;
                            var goalId = d.data.goalid;
                            var goalProgressValue = 0;
                            var topicId = d.parent.data.topicid;
                            var modalId = new Date().getTime();
                            var context = {
                                progressValue: d.data.pro,
                                modalId: modalId
                            };

                            var modalProgress = progressModalsDict[d.data.name];
                            if (modalProgress) {
                                modalProgress.show();
                            } else {
                                ModalFactory.create(
                                    {
                                        type: ModalFactory.types.SAVE_CANCEL,
                                        title:
                                            "Mein Lernfortschritt für das Lernziel '" + d.data.name + "':",
                                        body: Templates.render(TEMPLATES.EDIT_PROGRESS_VALUES, context)
                                    }
                                ).done(
                                    function(modal) {
                                        progressModalsDict[d.data.name] = modal;
                                        modal.getRoot().on(
                                            ModalEvents.save, function(e) {
                                                e.preventDefault();
                                                goalProgressValue = document.getElementById("progressvalue-" + modalId).value;
                                                saveProgress(sunburstId, courseId, courseModuleId, instanceId,
                                                    userId, topicId, goalId, goalName, goalProgressValue);
                                                modal.hide();
                                            }
                                        );

                                        modal.getRoot().find("progressvalue-" + modalId);

                                        $(modal.getRoot()).on('input', '#progressvalue-' + modalId, function() {
                                            updateLearningProgress(modalId);
                                        });


                                        modal.show();
                                    }
                                );
                            }
                        }
                    }
                );

            // Making the text rotation based on the angle
            var rotate = function(d) {
                var angle = ((d.x0 + d.x1) / Math.PI) * 90;
                if (angle <= 90 && angle >= 0) {
                    return angle - 90;
                } else if (angle > 90 && angle < 180) {
                    return (90 - angle) * -1;
                } else if (angle >= 180) {
                    return angle - 270;
                } else {
                    // Console.error("error while finding fitting angle");
                    return 0;
                }
            };

            // Calc the depth of the json
            var getDepth = function(nodes) {
                var depth = 0;
                if (nodes.children) {
                    nodes.children.forEach(
                        function(d) {
                            var cur = getDepth(d);
                            if (cur > depth) {
                                depth = cur;
                            }
                        }
                    );
                }
                return 1 + depth;
            };
            var VerNum = 18 / getDepth(root) - 1;

            g.selectAll(".node")
                .data(root.descendants())
                .append("text")
                .style('pointer-events', 'none')
                .text((d) => {
                        if (d.depth > 0 && d.data.size <= 0.1) {
                            var title = d.data.name.toString();
                            var per = percentage(d);
                            if (per !== null) {
                                if (per.toString().length > 0) {
                                    if (d.data.keyword.length > 0) {
                                        title = per + "% " + d.data.keyword;
                                    } else {
                                        title = per + "% " + title;
                                    }
                                }
                            }
                            if (d.depth > 1) {
                                VerNum = 28 / getDepth(root) - 1;
                            }
                            if (title.length > VerNum) {
                                return title.substring(0, VerNum) + "...";
                            } else {
                                return title;
                            }
                        }
                        return undefined;
                    }
                )
                .attr("transform", (d) => {
                        if (d.depth > 0 && d.data.size <= 0.1) {
                            return "translate(" + arc.centroid(d) + ")rotate(" + rotate(d) + ")";
                        }
                        return undefined;
                    }
                )
                .style(
                    "font-size", function(d) {
                        return d.depth > 1 ? 10 : 12;
                    }
                )
                .attr("dx", "-20")
                .attr("dy", ".5em");

            // Labeling goals and topic inside the arcs
            // Circle is 360', so 90 chars will give 1 char per 4'
            var maxLetters = 90;
            if (courseTaxonomy.children.length > 1) {
                maxLetters = maxLetters - 2 * courseTaxonomy.children.length;
            }

            g.selectAll(".node")
                .data(root.descendants())
                .append("text")
                .style('pointer-events', 'none')
                .attr(
                    "dy", function(d) {
                        if ((d.depth == 1 && d.parent.children.length == 1)
                            || (d.depth == 2
                                && d.parent.children.length == 1
                                && d.parent.parent.children.length == 1)
                        ) {
                            return 30;
                        } else {
                            var angle = ((d.x0 + d.x1) / Math.PI) * 90;
                            if (angle > 90 && angle < 270) {
                                return -25;
                            } else {
                                return 30;
                            }
                        }
                    }
                )
                .append("textPath")
                .attr(
                    "xlink:href", function(d, i) {
                        return "#" + sunburstId + "skillArc_" + i;
                    }
                )
                .style("text-anchor", "middle") // Place the text halfway on the arc
                .attr("startOffset", "50%")
                .style(
                    "font-size", function(d) {
                        return d.depth > 1 ? 10 : 12;
                    }
                )
                .attr("letter-spacing", 2.75)
                .text((d) => {
                        if (d.depth > 0 && d.data.size > 0.1) {
                            var CharNum = Math.round(d.data.size * maxLetters);
                            var title = d.data.name;
                            if (d.data.keyword.length > 0) {
                                title = d.data.keyword;
                            }
                            if (title.length > CharNum) {
                                return title.substring(0, CharNum - 3) + "...";
                            }
                            return title;
                        }
                        return undefined;
                    }
                );

            g.selectAll(".node")
                .data(root.descendants())
                .append("text")
                .style('pointer-events', 'none')
                .attr(
                    "dy", function(d) {
                        if ((d.depth == 1 && d.parent.children.length == 1)
                            || (d.depth == 2
                                && d.parent.children.length == 1
                                && d.parent.parent.children.length == 1)
                        ) {
                            return 40;
                        } else {
                            var angle = ((d.x0 + d.x1) / Math.PI) * 90;
                            if (angle > 90 && angle < 270) {
                                return -10;
                            } else {
                                return 50;
                            }
                        }
                    }
                )
                .append("textPath")
                .attr(
                    "xlink:href", function(d, i) {
                        return "#" + sunburstId + "skillArc_" + i;
                    }
                )
                .style("text-anchor", "middle") // Place the text halfway on the arc
                .attr("startOffset", "50%")
                .style(
                    "font-size", function(d) {
                        return d.depth > 1 ? 10 : 12;
                    }
                )
                .attr("letter-spacing", 2.75)
                .text((d) => {
                        if (d.depth > 0 && d.data.size > 0.1) {
                            var per = percentage(d);
                            if (per !== null) {
                                if (per.toString().length > 0) {
                                    return per + "%";
                                }
                            }
                        }
                        return undefined;
                    }
                );

            // Generating legend gradient
            var legendWidth = width / 2;
            let legendSvg = d3.select("#" + sunburstId + "-taxonomy-userprogress-legend")
                .append("svg")
                .attr("class", "svgLegend")
                .attr("viewBox", "0 0 " + width + " " + height / 8);
            legendSvg
                .append("defs")
                .append("linearGradient")
                .attr("id", "legendGradientMulti")
                .attr("x1", "0%")
                .attr("y1", "0%")
                .attr("x2", "100%")
                .attr("y2", "0%")
                .selectAll("stop")
                .data(
                    [
                        {offset: "0%", color: getColorForPercentage(0)},
                        {offset: "12.5%", color: getColorForPercentage(0.125)},
                        {offset: "25%", color: getColorForPercentage(0.25)},
                        {offset: "37.5%", color: getColorForPercentage(0.375)},
                        {offset: "50%", color: getColorForPercentage(0.5)},
                        {offset: "62.5%", color: getColorForPercentage(0.625)},
                        {offset: "75%", color: getColorForPercentage(0.75)},
                        {offset: "87.5%", color: getColorForPercentage(0.875)},
                        {offset: "100%", color: getColorForPercentage(1.0)}
                    ]
                )
                .enter()
                .append("stop")
                .attr(
                    "offset", function(d) {
                        return d.offset;
                    }
                )
                .attr(
                    "stop-color", function(d) {
                        return d.color;
                    }
                );

            var legendSvgWrapper = legendSvg
                .append("g")
                .attr("class", "legendWrapper")
                .attr("transform", "translate(" + width / 2 + "," + height / 18 + ")");

            // Draw the Rectangle
            legendSvgWrapper
                .append("rect")
                .attr("class", "legendRect")
                .attr("x", -legendWidth / 2)
                .attr("y", 0)
                .attr("rx", 8 / 2)
                .attr("width", legendWidth)
                .attr("height", 8)
                .style("fill", "url(#legendGradientMulti)");

            // Append title
            legendSvgWrapper
                .append("text")
                .attr("class", "legendTitle")
                .attr("x", 0)
                .attr("y", -8)
                .style("text-anchor", "middle")
                .text(progressLegendLabel);

            // Set scale for x-axis
            var scale = d3
                .scaleLinear()
                .range([-legendWidth / 2, legendWidth / 2])
                .domain([0, 100]);

            // Define x-axis
            var xAxis = d3
                .axisBottom()
                .ticks(5)
                .tickFormat(
                    function(d) {
                        return d + "%";
                    }
                )
                .scale(scale);

            // Set up X axis
            legendSvgWrapper
                .append("g")
                .attr("class", "axis")
                .attr("transform", "translate(0," + 8 + ")")
                .call(xAxis);
        });
    };

    //
    var percentColors = [
        {pct: 0.0, color: {r: 0xcc, g: 0x00, b: 0x00}},
        {pct: 0.5, color: {r: 0xff, g: 0xff, b: 0x00}},
        {pct: 1.0, color: {r: 0x00, g: 0x66, b: 0x00}}
    ];

    /**
     * Color function: converting the progress into color
     * @param {*} pct The progress as percentage betweet 0 and 1
     * @returns {string} The color hex code
     */
    var getColorForPercentage = function(pct) {
        let i;
        for (i = 1; i < percentColors.length - 1; i++) {
            if (pct < percentColors[i].pct) {
                break;
            }
        }
        var lower = percentColors[i - 1],
            upper = percentColors[i],
            range = upper.pct - lower.pct,
            rangePct = (pct - lower.pct) / range,
            pctLower = 1 - rangePct,
            pctUpper = rangePct;
        var color = {
            r: Math.floor(lower.color.r * pctLower + upper.color.r * pctUpper),
            g: Math.floor(lower.color.g * pctLower + upper.color.g * pctUpper),
            b: Math.floor(lower.color.b * pctLower + upper.color.b * pctUpper)
        };
        return (
            "#" +
            ((1 << 24) + (color.r << 16) + (color.g << 8) + color.b)
                .toString(16)
                .slice(1)
        );
    };

    /**
     * Converting user progress into a color code
     * @param {*} value The user progress as a value between 0 and 100
     * @returns {string} The color hex code
     */
    var getColor = function(value) {
        switch (true) {
            case value <= 0:
                return "#e5e5e5";
            case value <= 12.5:
                return getColorForPercentage(0.125);
            case value <= 25:
                return getColorForPercentage(0.25);
            case value <= 37.5:
                return getColorForPercentage(0.375);
            case value <= 50:
                return getColorForPercentage(0.5);
            case value <= 62.5:
                return getColorForPercentage(0.5);
            case value <= 75:
                return getColorForPercentage(0.75);
            case value <= 87.5:
                return getColorForPercentage(0.875);
            case value <= 100:
                return getColorForPercentage(0.875);
            case value > 100:
            default:
                throw new Error(
                    "Cant get the coloring, the progress value is over 100% !"
                );
        }
    };

    /**
     * Calculates the user progress of a topic or goal
     * @param {*} d The topic or goal node inside the D3 hierarchy
     * @returns {number} The progress value
     */
    var percentage = function(d) {
        if (d.children) {
            var sum = 0;
            for (var i = 0; i < d.children.length; i++) {
                sum += percentage(d.children[i]);
            }
            sum = Math.round(sum / d.children.length);
            return typeof sum === "undefined" || isNaN(sum) ? "" : sum;
        }
        return d.data.pro;
    };

    /**
     * Update the users progress.
     * @param {*} sunburstId The sunburst instance ID
     * @param {*} courseId The course ID
     * @param {*} courseModuleId The course module ID
     * @param {*} instanceId The course module instance ID
     * @param {*} userId The user ID
     * @param {*} topicId The topic ID
     * @param {*} goalId The goal ID
     * @param {*} goalName The name of the goal
     * @param {*} goalProgressValue The user progress
     */
    var saveProgress = function(sunburstId,
        courseId, courseModuleId, instanceId, userId, topicId,
        goalId,
        goalName, goalProgressValue) {
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
                    var loadedTaxonomy = JSON.parse(taxonomy);
                    if (loadedTaxonomy.children.length > 0) {
                        $("div#" + sunburstId + "-taxonomy-userprogress-chart-fullgoal").remove();
                        $("#" + sunburstId + "-taxonomy-userprogress-chart").empty();
                        $("#" + sunburstId + "-taxonomy-userprogress-legend").empty();
                        renderSunburstWithProgressView(loadedTaxonomy, sunburstId);
                        const updateLearningGoalProgressEvent = new CustomEvent('update_learning_goal_progress', {
                            bubbles: true,
                            detail: {
                                sender: "sunburst",
                                taxonomy: loadedTaxonomy,
                                sunburstId: sunburstId
                            }
                        });

                        $('#' + sunburstId + '-taxonomy')[0].dispatchEvent(updateLearningGoalProgressEvent);
                    }
                    return;
                }
            )
            .catch(Notification.exception);

        // Log save progress event
        var learningGoalEvent = createLearningGoalEvent("preparationSaveProgress", courseId, courseModuleId, instanceId, userId);
        var eventGoalParam = new Object();
        eventGoalParam.name = "goalname";
        eventGoalParam.value = goalName;
        var eventGoalProgressParam = new Object();
        eventGoalProgressParam.name = "goalprogress";
        eventGoalProgressParam.value = goalProgressValue;
        learningGoalEvent.push(eventGoalParam);
        learningGoalEvent.push(eventGoalProgressParam);
        logLearningGoalEvent(courseId, courseModuleId, instanceId, userId, learningGoalEvent);
    };

    /**
     * Logs learning goal events into moodles standard log store
     * @param {*} courseId The course ID
     * @param {*} courseModuleId The course module ID
     * @param {*} instanceId The course module instance ID
     * @param {*} userId The user ID
     * @param {*} eventParams The learning goal event parameters
     */
    var logLearningGoalEvent = function(courseId, courseModuleId, instanceId, userId, eventParams) {
        Controller.logEvent(
            {
                courseid: courseId,
                coursemoduleid: courseModuleId,
                instanceid: instanceId,
                userid: userId,
                eventparams: eventParams
            }
        )
        .then(() => {
          return;
        })
        .catch(Notification.exception);
    };

    /**
     * Create learning goal event parameters
     * @param {*} courseId The course ID
     * @param {*} courseModuleId The course module ID
     * @param {*} instanceId The course module instance ID
     * @param {*} userId The user ID
     * @returns {array} The array of learning goal event parameters
     */
    var createLearningGoalEvent = function(courseId, courseModuleId, instanceId, userId) {
        var eventCourseParam = new Object();
        eventCourseParam.name = "courseid";
        eventCourseParam.value = courseId;

        var eventCourseModuleParam = new Object();
        eventCourseModuleParam.name = "coursemoduleid";
        eventCourseModuleParam.value = courseModuleId;

        var eventInstanceParam = new Object();
        eventInstanceParam.name = "instanceid";
        eventInstanceParam.value = instanceId;

        var eventUserParam = new Object();
        eventUserParam.name = "userid";
        eventUserParam.value = userId;

        var timestampParam = new Object();
        timestampParam.name = "timestamp";
        timestampParam.value = Math.trunc(new Date().getTime() / 1000);

        return [eventCourseParam, eventCourseModuleParam, eventInstanceParam, eventUserParam, timestampParam];
    };

    /**
     * Update the visualised learing goal progress
     * @param {*} modalId The modal ID
     */
    var updateLearningProgress = function(modalId) {
        var progressvalue = document.getElementById("progressvalue-" + modalId);
        var progressvaluelabel = document.getElementById("progressvaluelabel-" + modalId);
        if (progressvalue && progressvaluelabel) {
            progressvaluelabel.innerHTML = progressvalue.value + "%";
        }
    };

    /**
     *
     * @param {*} element The learning goal widget element
     * @returns {number} The sunburst instance ID
     */
    var getSunburstId = function(element) {
        var learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
        return $(learningGoalWidgetElement).data("sunburst-id");
    };

    /**
     *
     * @param {*} element The learning goal widget element
     * @returns {number} The course ID
     */
    var getCourseId = function(element) {
        var learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
        return $(learningGoalWidgetElement).data("course-id");
    };

    /**
     *
     * @param {*} element The learning goal widget element
     * @returns {number} The course module ID
     */
    var getCourseModuleId = function(element) {
        var learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
        return $(learningGoalWidgetElement).data("coursemodule-id");
    };

    /**
     *
     * @param {*} element The learning goal widget element
     * @returns {number} The course module instance ID
     */
    var getInstanceId = function(element) {
        var learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
        return $(learningGoalWidgetElement).data("instance-id");
    };

    /**
     *
     * @param {*} element The learning goal widget element
     * @returns {number} The user id
     */
    var getUserId = function(element) {
        var learningGoalWidgetElement = $(element).closest('div.telm-learninggoals-widget');
        return $(learningGoalWidgetElement).data("user-id");
    };

    return {
        renderSunburst: renderSunburst,
        renderSunburstWithProgressView: renderSunburstWithProgressView,
        UpdateLearningProgress: updateLearningProgress
    };
}
);

