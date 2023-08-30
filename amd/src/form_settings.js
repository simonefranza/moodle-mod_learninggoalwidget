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
 * @copyright  University of Technology Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

/**
 * Javascript to initialise the Learning Goals Widget.
 *
 * @copyright  University of Technology Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
  [
    'jquery',
    "mod_learninggoalwidget/controller",
    "core/templates",
    'core/modal_factory',
    'core/modal_events',
    'core/str'
  ],
  ($, Controller, Templates, ModalFactory, ModalEvents, CoreStr) => {

    var TEMPLATES = {
      GOAL_MODAL_VIEW: "mod_learninggoalwidget/editor/goalmodalview",
      TOPIC_MODAL_VIEW: "mod_learninggoalwidget/editor/topicmodalview",
      TOPIC: "mod_learninggoalwidget/editor/topic",
      GOAL: "mod_learninggoalwidget/editor/goal",
      NOTOPICS: "mod_learninggoalwidget/editor/notopics",
    };

    var MODAL_ITEM_SELECTORS = {
      ITEM_TITLE_FIELD: '[data-action="itemtitle"]',
      ITEM_SHORTNAME_FIELD: '[data-action="itemshortname"]',
      ITEM_URL_FIELD: '[data-action="itemurl"]',
    };

    var course = null;
    var coursemodule = null;
    var instance = null;
    var taxonomy = null;
    var selectedTopic = null;
    var selectedTopicElement = null;

    /**
     * Initialise all of the modules for the Learning Goals Widget.
     * @param {string} paramCourse The course ID
     * @param {string} paramCoursemodule The course module ID
     * @param {string} paramInstance The course module instance ID
     * @param {string} paramTaxonomy The learning goal taxonomy as string
     */
    const init = (paramCourse, paramCoursemodule, paramInstance, paramTaxonomy) => {
      course = paramCourse;
      coursemodule = paramCoursemodule;
      instance = paramInstance;
      taxonomy = JSON.parse(paramTaxonomy);

      loadTopics(taxonomy);

      $("#newtopic").click(clickedNewTopic);
      $("#newgoal").click(clickedNewGoal);
      $("#json-upload").click(clickedJSONUpload);
      $("#json-download").click(clickedJSONDownload);
    };

    /**
     * Render the topics of the learning goal taxonomy
     * @param {*} taxonomy The learning goal taxonomy
     */
    const loadTopics = (taxonomy) => {
      if (taxonomy.children.length === 0) {
        $("#notopics").removeClass("d-none");
        return;
      }
      taxonomy.children.forEach((topic) => {

        var topicContext = {
          topicname: topic[2],
          topicid: topic[1]
        };
        CoreStr.get_string('settings:showgoals', 'mod_learninggoalwidget')
          .then((showGoalStr) => {
            Templates.render(TEMPLATES.TOPIC, topicContext)
              .then((html) => {
                $("#notopics").addClass("d-none");
                $("#goalsfortopic").removeClass("d-none");
                $("#goalsfortopicstatusmessage").html(showGoalStr);
                $("#topics-list").append(html);
                var topicId = topicContext.topicid;
                $("#topic-item-" + topicId).click(clickedTopicName);
                $("#" + topicId + "-action-edit").click(clickedEditTopic);
                $("#" + topicId + "-action-delete").click(clickedDeleteTopic);
                $("#" + topicId + "-action-moveup").click(clickedMoveupTopic);
                $("#" + topicId + "-action-movedown").click(clickedMovedownTopic);
                return html;
              })
              .catch(() => {
                // Do nothing
              });
            return 0;
          })
          .catch(() => {
            // Do nothing
          });
      });
    };

    /**
     * Render the goals of the learning goal taxonomy
     * @param {*} topicId The topic ID
     * @param {*} taxonomy The learning goal taxonomy
     */
    const loadGoals = (topicId, taxonomy) => {
      if (taxonomy.length > 0) {
        $("#goalsfortopic").addClass("d-none");
        taxonomy.forEach((goal) => {

          var goalContext = {
            topicid: topicId,
            goalid: goal[1],
            learninggoaltitle: goal[2]
          };

          Templates.render(TEMPLATES.GOAL, goalContext)
            .then((html) => {
              $('#learninggoals-list').append(html);
              var goalId = goalContext.goalid;
              $("#" + topicId + "-goal-" + goalId + "-action-edit").click(clickedEditGoal);
              $("#" + topicId + "-goal-" + goalId + "-action-delete").click(clickedDeleteGoal);
              $("#" + topicId + "-goal-" + goalId + "-action-moveup").click(clickedMoveupGoal);
              $("#" + topicId + "-goal-" + goalId + "-action-movedown").click(clickedMovedownGoal);
              return;
            })
            .catch(() => {
              // Do nothing
            });
        });
      } else {
        CoreStr.get_string('settings:nogoals', 'mod_learninggoalwidget')
        .then((noGoalStr) => {
          $("#goalsfortopic").removeClass("d-none");
          $("#goalsfortopicstatusmessage").html(noGoalStr);
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
      }
    };

    /**
     * Handle topic name click event; load learning goals
     * @param {event} e click event
     */
    const clickedTopicName = (e) => {
      e.preventDefault();

      var topicId = $(e.currentTarget).data('topicid');

      taxonomy.children.forEach((topic) => {
        if (topic[1] === topicId) {
          $('#learninggoals-list').children().remove();
          loadGoals(topicId, topic[5]);
        }
      });

      selectedTopic = topicId;
      if (selectedTopicElement !== null) {
        selectedTopicElement.css('background-color', 'white');
      }
      selectedTopicElement = $(e.currentTarget);
      selectedTopicElement.css('background-color', 'gainsboro');
    };

    /**
     * Show 'New Topic' Modal
     */
    const clickedNewTopic = () => {
      let strings = [
        {key: 'settings:topic', component: 'mod_learninggoalwidget'},
        {key: 'settings:description', component: 'mod_learninggoalwidget'},
        {key: 'settings:addtopic', component: 'mod_learninggoalwidget'},
        {key: 'settings:link', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          const context = {
            title: results[0],
            shortname: results[1],
            weburl: results[3],
          };

          showModal(
            context,
            results[2],
            TEMPLATES.TOPIC_MODAL_VIEW,
            "Speichern",
            (modal, topicName, topicShortname, topicUrl) => {
              // Make insert topic call
              Controller.insertTopic({
                course: course,
                coursemodule: coursemodule,
                instance: instance,
                topicname: topicName,
                topicshortname: topicShortname,
                topicurl: topicUrl,
              })
                .then((jsonTaxonomy) => {
                  modal.hide();
                  $("#topics-list").children().remove();
                  taxonomy = JSON.parse(jsonTaxonomy);
                  loadTopics(taxonomy);
                  return;
                })
                .catch(() => {
                  // Do nothing
                });
            });
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };


    /**
     * Handle edit topic click event
     * @param {event} e Clicked event
     */
    const clickedEditTopic = (e) => {
      e.preventDefault();

      var topicId = $(e.currentTarget).data('topicid');

      var topicTitle;
      var topicShortname;
      var topicUrl;
      taxonomy.children.forEach(function(topic) {
        if (topic[1] === topicId) {
          topicTitle = topic[2];
          topicShortname = topic[3];
          topicUrl = topic[4];
        }
      });
      let strings = [
        {key: 'settings:topic', component: 'mod_learninggoalwidget'},
        {key: 'settings:description', component: 'mod_learninggoalwidget'},
        {key: 'settings:edittopic', component: 'mod_learninggoalwidget'},
        {key: 'settings:link', component: 'mod_learninggoalwidget'},
        {key: 'settings:save', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          var context = {
            title: results[0],
            shortname: results[1],
            weburl: results[3],
            topictitle: topicTitle,
            topicshortname: topicShortname,
            topicurl: topicUrl,
          };

          showModal(
            context,
            results[2],
            TEMPLATES.TOPIC_MODAL_VIEW,
            results[4],
            (modal, topicName, topicShortname, topicUrl) => {
              // Make update topic call
              Controller.updateTopic({
                course: course,
                coursemodule: coursemodule,
                instance: instance,
                topicid: topicId,
                topicname: topicName,
                topicshortname: topicShortname,
                topicurl: topicUrl,
              })
                .then((jsonTaxonomy) => {
                  modal.hide();
                  $("#topics-list").children().remove();
                  taxonomy = JSON.parse(jsonTaxonomy);
                  loadTopics(taxonomy);
                  return;
                })
                .catch(() => {
                  // Do nothing
                });
            }
          );
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Handle delete topic event
     * @param {event} e click event
     */
    const clickedDeleteTopic = (e) => {
      e.preventDefault();

      const topicId = $(e.currentTarget).data('topicid');
      let strings = [
        {key: 'settings:deletetopic', component: 'mod_learninggoalwidget'},
        {key: 'settings:deletetopicmsg', component: 'mod_learninggoalwidget'},
        {key: 'settings:delete', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          showMessage(
            results[0],
            results[1],
            results[2],
            (modal) => {
              // Make delete topic call
              Controller.deleteTopic({
                course: course,
                coursemodule: coursemodule,
                instance: instance,
                topicid: topicId
              })
                .then((jsonTaxonomy) => {
                  modal.hide();
                  $("#topics-list").children().remove();
                  $("#learninggoals-list").children().remove();
                  taxonomy = JSON.parse(jsonTaxonomy);
                  loadTopics(taxonomy);
                  return;
                })
                .catch(() => {
                  // Do nothing
                });
            });
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Handle topic move up event
     * @param {event} e click event
     */
    const clickedMoveupTopic = (e) => {
      e.preventDefault();

      var topicId = $(e.currentTarget).data('topicid');

      // Make move up topic call
      Controller.moveUpTopic({
        course: course,
        coursemodule: coursemodule,
        instance: instance,
        topicid: topicId
      })
        .then((jsonTaxonomy) => {
          $("#topics-list").children().remove();
          taxonomy = JSON.parse(jsonTaxonomy);
          loadTopics(taxonomy);
          return;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Handle topic move down event
     * @param {event} e click event
     */
    const clickedMovedownTopic = (e) => {
      e.preventDefault();

      var topicId = $(e.currentTarget).data('topicid');

      // Make move up topic call
      Controller.moveDownTopic({
        course: course,
        coursemodule: coursemodule,
        instance: instance,
        topicid: topicId
      })
        .then((jsonTaxonomy) => {
          $("#topics-list").children().remove();
          taxonomy = JSON.parse(jsonTaxonomy);
          loadTopics(taxonomy);
          return;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Show 'New Goal' Modal
     */
    const clickedNewGoal = () => {
      if (selectedTopic === null) {
        return;
      }

      var topicTitle = "";

      taxonomy.children.forEach((topic) => {
        if (topic[1] === selectedTopic) {
          topicTitle = topic[2];
        }
      });
      let strings = [
        {key: 'settings:topic', component: 'mod_learninggoalwidget'},
        {key: 'settings:goal', component: 'mod_learninggoalwidget'},
        {key: 'settings:description', component: 'mod_learninggoalwidget'},
        {key: 'settings:link', component: 'mod_learninggoalwidget'},
        {key: 'settings:addgoal', component: 'mod_learninggoalwidget'},
        {key: 'settings:save', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          const context = {
            topiclabel: results[0],
            title: results[1],
            shortname: results[2],
            weburl: results[3],
            topictitle: topicTitle,
          };

          showModal(
            context,
            results[4],
            TEMPLATES.GOAL_MODAL_VIEW,
            results[5],
            (modal, goalName, goalShortname, goalUrl) => {
              // Make insert goal call
              Controller.insertGoal({
                course: course,
                coursemodule: coursemodule,
                instance: instance,
                topicid: selectedTopic,
                goalname: goalName,
                goalshortname: goalShortname,
                goalurl: goalUrl,
              })
                .then((jsonTaxonomy) => {
                  modal.hide();

                  taxonomy = JSON.parse(jsonTaxonomy);
                  $('#learninggoals-list').children().remove();
                  taxonomy.children.forEach((topic) => {
                    if (topic[1] === selectedTopic) {
                      loadGoals(selectedTopic, topic[5]);
                    }
                  });
                  return;
                })
                .catch(() => {
                  // Do nothing
                });
            });
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };


    /**
     * Handle edit goal click event
     * @param {event} e Clicked event
     */
    const clickedEditGoal = (e) => {
      e.preventDefault();

      var topicId = $(e.currentTarget).data('topicid');
      var goalId = $(e.currentTarget).data('goalid');

      var topicTitle;
      var goalTitle;
      var goalShortname;
      var goalUrl;
      taxonomy.children.forEach((topic) => {
        if (topic[1] !== topicId) {
          return;
        }
        topicTitle = topic[2];
        topic[5].forEach((goal) => {
          if (goal[1] === goalId) {
            goalTitle = goal[2];
            goalShortname = goal[3];
            goalUrl = goal[4];
          }
        });
      });

      let strings = [
        {key: 'settings:topic', component: 'mod_learninggoalwidget'},
        {key: 'settings:goal', component: 'mod_learninggoalwidget'},
        {key: 'settings:description', component: 'mod_learninggoalwidget'},
        {key: 'settings:link', component: 'mod_learninggoalwidget'},
        {key: 'settings:editgoal', component: 'mod_learninggoalwidget'},
        {key: 'settings:save', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          const context = {
            topiclabel: results[0],
            topictitle: topicTitle,
            title: results[1],
            shortname: results[2],
            weburl: results[3],
            goaltitle: goalTitle,
            goalshortname: goalShortname,
            goalurl: goalUrl,
          };

          showModal(
            context,
            results[4],
            TEMPLATES.GOAL_MODAL_VIEW,
            results[5],
            (modal, goalName, goalShortname, goalUrl) => {
              // Make insert goal call
              Controller.updateGoal({
                course: course,
                coursemodule: coursemodule,
                instance: instance,
                topicid: topicId,
                goalid: goalId,
                goalname: goalName,
                goalshortname: goalShortname,
                goalurl: goalUrl,
              })
                .then((jsonTaxonomy) => {
                  modal.hide();
                  taxonomy = JSON.parse(jsonTaxonomy);
                  $('#learninggoals-list').children().remove();
                  taxonomy.children.forEach((topic) => {
                    if (topic[1] === topicId) {
                      loadGoals(topicId, topic[5]);
                    }
                  });
                  return;
                })
                .catch(() => {
                  // Do nothing
                });
            });
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Handle delete goal event
     * @param {event} e click event
     */
    const clickedDeleteGoal = (e) => {
      e.preventDefault();

      const topicId = $(e.currentTarget).data('topicid');
      const goalId = $(e.currentTarget).data('goalid');

      let strings = [
        {key: 'settings:deletegoal', component: 'mod_learninggoalwidget'},
        {key: 'settings:deletegoalmsg', component: 'mod_learninggoalwidget'},
        {key: 'settings:delete', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          showMessage(
            results[0],
            results[1],
            results[2],
            (modal) => {
              // Make delete topic call
              Controller.deleteGoal({
                course: course,
                coursemodule: coursemodule,
                instance: instance,
                topicid: topicId,
                goalid: goalId
              })
                .then((jsonTaxonomy) => {
                  modal.hide();
                  taxonomy = JSON.parse(jsonTaxonomy);
                  $('#learninggoals-list').children().remove();
                  taxonomy.children.forEach((topic) => {
                    if (topic[1] === topicId) {
                      loadGoals(topicId, topic[5]);
                    }
                  });
                  return;
                })
                .catch(() => {
                  // Do nothing
                });
            });
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Handle goal move up event
     * @param {event} e click event
     */
    const clickedMoveupGoal = (e) => {
      e.preventDefault();

      const topicId = $(e.currentTarget).data('topicid');
      const goalId = $(e.currentTarget).data('goalid');

      // Make move up goal call
      Controller.moveUpGoal({
        course: course,
        coursemodule: coursemodule,
        instance: instance,
        topicid: topicId,
        goalid: goalId
      })
        .then((jsonTaxonomy) => {
          taxonomy = JSON.parse(jsonTaxonomy);

          $('#learninggoals-list').children().remove();
          taxonomy.children.forEach((topic) => {
            if (topic[1] === topicId) {
              loadGoals(topicId, topic[5]);
            }
          });
          return;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     * Handle goal move down event
     * @param {event} e click event
     */
    const clickedMovedownGoal = (e) => {
      e.preventDefault();

      const topicId = $(e.currentTarget).data('topicid');
      const goalId = $(e.currentTarget).data('goalid');

      // Make move down goal call
      Controller.moveDownGoal({
        course: course,
        coursemodule: coursemodule,
        instance: instance,
        topicid: topicId,
        goalid: goalId
      })
        .then((jsonTaxonomy) => {
          taxonomy = JSON.parse(jsonTaxonomy);

          $('#learninggoals-list').children().remove();
          taxonomy.children.forEach((topic) => {
            if (topic[1] === topicId) {
              loadGoals(topicId, topic[5]);
            }
          });
          return;
        })
        .catch(() => {
          // Do nothing
        });
    };


    /**
     *
     * @param {arry} context Template context for
     * @param {string} title The title bar of the modal
     * @param {string} templateName The template name to render
     * @param {string} btnSaveText The SAVE button text
     * @param {function} onSaveCallback Function callback when user clicks save
     */
    const showModal = (context, title, templateName, btnSaveText, onSaveCallback) => {
      let strings = [
        {key: 'validation:missingtitle', component: 'mod_learninggoalwidget'},
        {key: 'validation:invalidlink', component: 'mod_learninggoalwidget'},
      ];
      CoreStr.get_strings(strings)
        .then((results) => {
          ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: title,
            body: Templates.render(templateName, context)
          })
            .done((modal) => {
              modal.setSaveButtonText(btnSaveText);
              modal.getRoot().on(ModalEvents.save, (event) => {
                var titleInputfield = modal.getRoot().find(MODAL_ITEM_SELECTORS.ITEM_TITLE_FIELD);
                var shortnameInputfield = modal.getRoot().find(MODAL_ITEM_SELECTORS.ITEM_SHORTNAME_FIELD);
                var urlInputfield = modal.getRoot().find(MODAL_ITEM_SELECTORS.ITEM_URL_FIELD);

                let titleValid = false;
                let urlValid = false;
                if (titleInputfield[0].value !== undefined && titleInputfield[0].value !== "") {
                  titleValid = true;
                }
                if (isValidUrl(urlInputfield[0].value)) {
                  urlValid = true;
                }
                if (titleValid && urlValid) {
                  onSaveCallback(
                    modal,
                    titleInputfield[0].value,
                    shortnameInputfield[0].value,
                    urlInputfield[0].value
                  );
                } else {
                  event.preventDefault();
                  event.stopPropagation();
                  if (titleValid === false) {
                    modal.getRoot().find('[data-action="titlefeedback"]')
                      .text(results[0]);
                    modal.getRoot().find('[data-action="titlefeedback"]')
                      .css("display", "inline");
                  }
                  if (urlValid === false) {
                    modal.getRoot().find('[data-action="urlfeedback"]')
                      .text(results[1]);
                    modal.getRoot().find('[data-action="urlfeedback"]')
                      .css("display", "inline");
                  }
                }
              });
              modal.show();

              // Destroy when hidden.
              modal.getRoot().on(ModalEvents.hidden, () => {
                modal.destroy();
              });

              return modal;
            });
          return 0;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     *
     * @param {*} title The title bar of the modal
     * @param {*} text The message text
     * @param {*} btnSaveText The SAVE button text
     * @param {*} onSaveCallback Function callback when user clicks save
     *
     * @returns {void}
     */
    const showMessage = (title, text, btnSaveText, onSaveCallback) => {
      ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: title,
        body: text
      })
        .then((modal) => {
          modal.setSaveButtonText(btnSaveText);
          modal.getRoot().on(ModalEvents.save, async() => {
            await onSaveCallback(modal);
          });
          // Destroy when hidden.
          modal.getRoot().on(ModalEvents.hidden, () => {
            modal.destroy();
          });
          modal.show();
          return modal;
        })
        .catch(() => {
          // Do nothing
        });
    };

    /**
     *
     * @param {string} urlString The string containing a URL; needs checking
     * @returns {boolean} True if the string is a valid URL otherwise false
     */
    const isValidUrl = (urlString) => {
      if (urlString === null || urlString == "") {
        return true;
      }
      let isValid = false;
      let exp = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_+.~#?&//=]*)?/gi;
      let regex = new RegExp(exp);

      if (urlString.match(regex)) {
        isValid = true;
      }
      return isValid;
    };

    /**
     * Check if the top level taxonomy is valid
     *
     * @param {Object} json The json to be parsed
     *
     * @returns {Object} //{error: boolean, code?: string}
     */
    const isValidTopLevelJSON = (json) => {
      if (!('name' in json) || json.name === null || json.name === undefined) {
        return {error: true, code: "validation:jsontop1"};
      } else if (!('children' in json) || json.children === null || json.children === undefined) {
        return {error: true, code: "validation:jsontop2"};
      } else if (!Array.isArray(json.children)) {
        return {error: true, code: "validation:jsontop3"};
      } else if (json.children.length === 0) {
        return {error: true, code: "validation:jsontop4"};
      }
      return {error: false};
    };

    /**
     * Check if topic is valid
     *
     * @param {Object} topic The topic to be parsed
     *
     * @returns {Object} //{error: boolean, msg?: string}
     */
    const isValidTopicJSON = (topic) => {
      if (!('name' in topic) || topic.name === null || topic.name === undefined) {
        return {error: true, code: "validation:jsontopic1", codeParam: undefined};
      } else if (typeof (topic.name) !== 'string') {
        return {error: true, code: "validation:jsontopic2", codeParam: topic.name};
      } else if ('link' in topic) {
        if (typeof (topic.link) !== 'string') {
          return {error: true, code: "validation:jsontopic3", codeParam: topic.name};
        } else if (!isValidUrl(topic.link)) {
          return {error: true, code: "validation:jsontopic4", codeParam: topic.name};
        }
      } else if ('keyword' in topic && typeof (topic.keyword) !== 'string') {
        return {error: true, code: "validation:jsontopic5", codeParam: topic.name};
      } else if (!('children' in topic) || topic.children === null || topic.children === undefined) {
        return {error: false};
      } else if (!Array.isArray(topic.children)) {
        return {error: true, code: "validation:jsontopic6", codeParam: topic.name};
      }
      return {error: false};
    };

    /**
     * Check if goal is valid
     *
     * @param {string} topicName The name of the parent topic
     * @param {Object} goal The goal to be parsed
     *
     * @returns {Object} //{error: boolean, msg?: string}
     */
    const isValidGoalJSON = (topicName, goal) => {
      if (!('name' in goal) || goal.name === null || goal.name === undefined) {
        return {error: true, code: "validation:jsongoal1", codeParam: topicName};
      } else if (typeof (goal.name) !== 'string') {
        return {error: true, code: "validation:jsongoal2", codeParam: topicName};
      } else if ('link' in goal) {
        if (typeof (goal.link) !== 'string') {
          return {error: true, code: "validation:jsongoal3", codeParam: goal.name};
        } else if (!isValidUrl(goal.link)) {
          return {error: true, code: "validation:jsongoal4", codeParam: goal.name};
        }
      } else if ('keyword' in goal && typeof (goal.keyword) !== 'string') {
        return {error: true, code: "validation:jsongoal5", codeParam: goal.name};
      }
      return {error: false};
    };

    /**
     * Check if the uploaded taxonomy is valid and if so returns the preview text
     * There has to be a top level name and children property
     * children has to be an array with at least one topic
     * each topic needs a name property and if it contains learning goals in the children
     * array, then each learning goal needs to have the name property
     *
     * @param {string} json The json to be parsed
     * @returns {Object} //{error: boolean, preview ?:string, msg?: string}
     */
    const parseJSON = async(json) => {
      const res = isValidTopLevelJSON(json);
      if (res.error) {
        res.msg = await CoreStr.get_string(res.code, 'mod_learninggoalwidget');
        return res;
      }
      let preview = '<pre>';

      // Parse Topics
      for (let topicIdx = 0; topicIdx < json.children.length; topicIdx++) {
        const topic = json.children[topicIdx];
        const check = isValidTopicJSON(topic);
        if (check.error) {
          check.msg = await CoreStr.get_string(check.code, 'mod_learninggoalwidget', check.codeParam);
          return check;
        }
        preview += `${topic.name.replaceAll('&', '&amp').replaceAll('<', '&lt').replaceAll('>', '&gt')}\n`;

        // Parse Goals
        for (let goalIdx = 0; goalIdx < topic.children.length; goalIdx++) {
          const goal = topic.children[goalIdx];
          const goalCheck = isValidGoalJSON(topic.name, goal);
          if (goalCheck.error) {
            goalCheck.msg = await CoreStr.get_string(goalCheck.code, 'mod_learninggoalwidget', goalCheck.codeParam);
            return goalCheck;
          }
          let isLast = goalIdx == topic.children.length - 1;
          preview += ` |- ${goal.name.replaceAll('&', '&amp').replaceAll('<', '&lt').replaceAll('>', '&gt')}
${isLast ? '\n\n' : '\n'}`;
        }
      }
      preview += '</pre>';
      return {error: false, preview};
    };

    /**
     * Save JSON Taxonomy
     */
    const clickedJSONUpload = () => {
      const fileInput = document.querySelector('#json-file');
      if (fileInput.files.length === 0) {
        return;
      }
      const reader = new FileReader();
      reader.readAsText(fileInput.files[0], "UTF-8");
      reader.onload = async(evt) => {
        if (evt.target === null) {
          return;
        }
        try {
          const parsed = JSON.parse(reader.result);
          const check = await parseJSON(parsed);
          let strings = [
            {key: 'validation:invalid', component: 'mod_learninggoalwidget'},
            {key: 'validation:invalidfile', component: 'mod_learninggoalwidget'},
            {key: 'validation:close', component: 'mod_learninggoalwidget'},
            {key: 'settings:newtaxonomyheader', component: 'mod_learninggoalwidget'},
            {key: 'settings:newtaxonomymsg', component: 'mod_learninggoalwidget'},
            {key: 'settings:replace', component: 'mod_learninggoalwidget'},
          ];
          const results = await CoreStr.get_strings(strings);
          if (check.error) {
            showMessage(
              results[0],
              results[1] + ":<br/>" + check.msg,
              results[2],
              (modal) => {
                modal.hide();
              }
            );
            return;
          }

          showMessage(
            results[3],
            results[4] + "\n" + check.preview,
            results[5],
            async(modal) => {
              let jsonTaxonomy = {};
              jsonTaxonomy = await new Promise((resolve, reject) => {
                Controller.deleteTaxonomy({
                  course: course,
                  coursemodule: coursemodule,
                  instance: instance,
                })
                  .then((jsonTaxonomy) => resolve(jsonTaxonomy))
                  .catch((e) => reject(e));
              });
              $("#topics-list").children().remove();
              $("#learninggoals-list").children().remove();
              jsonTaxonomy = await new Promise((resolve, reject) => {
                Controller.addTaxonomy({
                  course: course,
                  coursemodule: coursemodule,
                  instance: instance,
                  taxonomy: JSON.stringify(parsed),
                })
                  .then((jsonTaxonomy) => resolve(jsonTaxonomy))
                  .catch((e) => reject(e));
              });
              taxonomy = JSON.parse(jsonTaxonomy);
              loadTopics(taxonomy);
              modal.hide();
            });

        } catch (e) {
          // Do nothing
        }
      };
    };

    /**
     * Download JSON Taxonomy
     */
    const clickedJSONDownload = async() => {
      const jsonTaxonomy = await new Promise((resolve, reject) => {
        Controller.getTaxonomy({
          course: course,
          coursemodule: coursemodule,
          instance: instance,
        })
          .then((jsonTaxonomy) => resolve(JSON.parse(jsonTaxonomy)))
          .catch((e) => reject(e));
      });
      let newTaxonomy = {name: jsonTaxonomy.name, children: []};
      jsonTaxonomy.children.forEach((topic) => {
        let goals = [];
        let topicObj = {};
        if (topic.length >= 3) {
          topicObj.name = topic[2];
          if (topic.length >= 4) {
            topicObj.keyword = topic[3];
            if (topic.length >= 5) {
              topicObj.link = topic[4];
            }
          }
        }

        if (topic.length >= 6) {
          topic[5].forEach((goal) => {
            goals.push({});
            if (goal.length >= 3) {
              goals[goals.length - 1].name = goal[2];
              if (goal.length >= 4) {
                goals[goals.length - 1].keyword = goal[3];
                if (goal.length >= 5) {
                  goals[goals.length - 1].link = goal[4];
                }
              }
            }
          });
        }
        topicObj.children = goals;
        newTaxonomy.children.push(topicObj);
      });
      const filename = 'taxonomy.json';
      const blob = new Blob([JSON.stringify(newTaxonomy, null, 2)], {type: 'text/csv'});
      if (window.navigator.msSaveOrOpenBlob) {
        window.navigator.msSaveBlob(blob, filename);
      }
      const element = document.createElement('a');
      element.setAttribute('href', window.URL.createObjectURL(blob));
      element.setAttribute('download', filename);
      element.style.display = 'none';
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    };

    return {
      init: init
    };
  });
