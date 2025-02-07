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

    // Keys to mark topics or goals as deleted/added/edited
    const ADD_KEY = 'new';
    const DELETE_KEY = 'deleted';
    const EDIT_KEY = 'edit';

    var instance = null;
    var taxonomy = null;
    var selectedTopic = null;
    var selectedTopicElement = null;

    /**
     * Initialise all of the modules for the Learning Goals Widget.
     * @param {string} paramInstance The course module instance ID
     * @param {string} paramTaxonomy The learning goal taxonomy as string
     */
    const init = (paramInstance, paramTaxonomy) => {
      instance = paramInstance;
      taxonomy = JSON.parse(paramTaxonomy);

      loadTopics();

      $("#newtopic").click(clickedNewTopic);
      $("#newgoal").click(clickedNewGoal);
      $("#json-upload").click(clickedJSONUpload);
      $("#json-download").click(clickedJSONDownload);
      $("#json-download-template").click(clickedJSONDownloadTemplate);
    };

    /**
     * Render the topics of the learning goal taxonomy
     */
    const loadTopics = async () => {
      $("#topics-list").children().remove();
      taxonomy.children.sort((a, b) => a.ranking - b.ranking);
      console.log("taxonomy", taxonomy);

      if (taxonomy.children.length === 0) {
        $("#notopics").removeClass("d-none");
      }
      for (let topic of taxonomy.children) {
        if (DELETE_KEY in topic && topic[DELETE_KEY]) {
          continue;
        }

        const topicContext = {
          topicname: topic.name,
          topicid: topic.topicid
        };
        const html = await Templates.render(TEMPLATES.TOPIC, topicContext);

        $("#notopics").addClass("d-none");
        $("#topics-list").append(html);

        const topicid = topic.topicid;
        $("#topic-item-" + topicid).click(clickedTopicName);
        const baseID = `#${topicid}-action-`;
        $(baseID + "edit").click(clickedEditTopic);
        $(baseID + "delete").click(clickedDeleteTopic);
        $(baseID + "moveup").click(clickedMoveupTopic);
        $(baseID + "movedown").click(clickedMovedownTopic);
      }
      if (!selectedTopic) {
        const showGoalStr = await CoreStr.get_string('settings:showgoals', 'mod_learninggoalwidget');
        $('#learninggoals-list').children().remove();
        $("#goalsfortopic").removeClass("d-none");
        $("#goalsfortopicstatusmessage").html(showGoalStr);
        return;
      }
      const selectedTopicObj = getTopicById(selectedTopic);
      loadGoals(selectedTopicObj);
    };

    /**
     * Render the goals of the learning goal taxonomy
     * @param {*} topic The topic
     */
    const loadGoals = async (topic) => {
      $('#learninggoals-list').children().remove();
      topic.children.sort((a, b) => a.ranking - b.ranking);
      if (!topic.children.length) {
        const noGoalStr = await CoreStr.get_string('settings:nogoals', 'mod_learninggoalwidget');
        $("#goalsfortopic").removeClass("d-none");
        $("#goalsfortopicstatusmessage").html(noGoalStr);
        return;
      }

      $("#goalsfortopic").addClass("d-none");
      const topicid = topic.topicid;
      for (let goal of topic.children) {
        if (DELETE_KEY in goal && goal[DELETE_KEY]) {
          continue;
        }

        const goalid = goal.goalid;

        var goalContext = {
          topicid: topicid,
          goalid: goalid,
          learninggoaltitle: goal.name
        };

        const html = await Templates.render(TEMPLATES.GOAL, goalContext);
        $('#learninggoals-list').append(html);

        const baseID = `#${topicid}-goal-${goalid}-action-`;
        $(baseID + "edit").click(clickedEditGoal);
        $(baseID + "delete").click(clickedDeleteGoal);
        $(baseID + "moveup").click(clickedMoveupGoal);
        $(baseID + "movedown").click(clickedMovedownGoal);
      }
    };

    /**
     * Handle topic name click event; load learning goals
     * @param {event} e click event
     */
    const clickedTopicName = (e) => {
      e.preventDefault();

      var topicId = $(e.currentTarget).data('topicid');
      let topic = getTopicById(topicId);
      if (!topic) {
        return;
      }

      loadGoals(topic);

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
    const clickedNewTopic = async () => {
      let strings = [
        { key: 'settings:topic', component: 'mod_learninggoalwidget' },
        { key: 'settings:description', component: 'mod_learninggoalwidget' },
        { key: 'settings:addtopic', component: 'mod_learninggoalwidget' },
        { key: 'settings:link', component: 'mod_learninggoalwidget' },
        { key: 'settings:save', component: 'mod_learninggoalwidget' },
      ];
      try {
        const results = await CoreStr.get_strings(strings);
        const context = {
          title: results[0],
          shortname: results[1],
          weburl: results[3],
        };
        let [modal, topicName, topicShortname, topicUrl] = await showModal(
          context,
          results[2],
          TEMPLATES.TOPIC_MODAL_VIEW,
          results[4],
        );
        modal.hide();

        // Add topic to local taxonomy
        addLocalTopic(topicName, topicShortname, topicUrl);
        updateTaxonomyValue();

        loadTopics();
      } catch (e) {
        console.error("Failed to create new topic", e);
      }
    };


    /**
     * Handle edit topic click event
     * @param {event} e Clicked event
     */
    const clickedEditTopic = async (e) => {
      e.preventDefault();

      var topicid = $(e.currentTarget).data('topicid');

      let topic = getTopicById(topicid);
      if (!topic) {
        return;
      }
      const oldTopicTitle = topic.name;
      const oldTopicShortname = topic.keyword;
      const oldTopicUrl = topic.link;

      let strings = [
        { key: 'settings:topic', component: 'mod_learninggoalwidget' },
        { key: 'settings:description', component: 'mod_learninggoalwidget' },
        { key: 'settings:edittopic', component: 'mod_learninggoalwidget' },
        { key: 'settings:link', component: 'mod_learninggoalwidget' },
        { key: 'settings:save', component: 'mod_learninggoalwidget' },
      ];
      try {
        const results = await CoreStr.get_strings(strings);
        var context = {
          title: results[0],
          shortname: results[1],
          weburl: results[3],
          topictitle: oldTopicTitle,
          topicshortname: oldTopicShortname,
          topicurl: oldTopicUrl,
        };
        const [modal, topicName, topicShortname, topicUrl] = await showModal(
          context,
          results[2],
          TEMPLATES.TOPIC_MODAL_VIEW,
          results[4],
        );
        modal.hide();

        // Update topic
        const update = {
          name: topicName,
          keyword: topicShortname,
          link: topicUrl,
        };
        updateLocalTopic(topic, update);
        updateTaxonomyValue();

        loadTopics();
      } catch (e) {
        console.error("Failed to edit topic", e);
      }
    };

    /**
     * Handle delete topic event
     * @param {event} e click event
     */
    const clickedDeleteTopic = async (e) => {
      e.preventDefault();

      const topicid = $(e.currentTarget).data('topicid');
      let topicToDelete = getTopicById(topicid);
      if (!topicToDelete) {
        return;
      }

      let strings = [
        { key: 'settings:deletetopic', component: 'mod_learninggoalwidget' },
        { key: 'settings:deletetopicmsg', component: 'mod_learninggoalwidget' },
        { key: 'settings:delete', component: 'mod_learninggoalwidget' },
      ];
      try {
        const results = await CoreStr.get_strings(strings);
        const modal = await showMessage(
          results[0],
          results[1],
          results[2],
        );
        modal.hide();

        // Mark topic as deleted
        deleteLocalTopic(topicToDelete);
        updateTaxonomyValue();

        loadTopics();
      } catch (e) {
        console.error("Failed to delete topic", e);
      }
    };

    /**
     * Handle topic move up event
     * @param {event} e click event
     */
    const clickedMoveupTopic = (e) => {
      e.preventDefault();

      var topicid = $(e.currentTarget).data('topicid');
      let topicToMove = getTopicById(topicid);
      if (!topicToMove || topicToMove.ranking == 1) {
        // No topic or is already first
        return;
      }
      for (let topic of taxonomy.children) {
        // Only topic directly above is affected
        if (topic.ranking === topicToMove.ranking - 1) {
          updateLocalTopic(topic, {ranking: topic.ranking + 1});
          updateLocalTopic(topicToMove, {ranking: topicToMove.ranking - 1});
          break;
        }
      }
      updateTaxonomyValue();

      loadTopics();
    };

    /**
     * Handle topic move down event
     * @param {event} e click event
     */
    const clickedMovedownTopic = (e) => {
      e.preventDefault();

      var topicid = $(e.currentTarget).data('topicid');
      let topicToMove = getTopicById(topicid);
      if (!topicToMove) {
        return;
      }
      for (let topic of taxonomy.children) {
        // Only topic directly below (aka ranking + 1) is affected
        if (topic.ranking === topicToMove.ranking + 1) {
          updateLocalTopic(topic, {ranking: topic.ranking - 1});
          updateLocalTopic(topicToMove, {ranking: topicToMove.ranking + 1});
          break;
        }
      }
      updateTaxonomyValue();

      loadTopics();
    };

    /**
     * Show 'New Goal' Modal
     */
    const clickedNewGoal = async () => {
      if (selectedTopic === null) {
        return;
      }

      const topic = getTopicById(selectedTopic);
      if (!topic) {
        return;
      }
      const topicTitle = topic.name;

      let strings = [
        { key: 'settings:topic', component: 'mod_learninggoalwidget' },
        { key: 'settings:goal', component: 'mod_learninggoalwidget' },
        { key: 'settings:description', component: 'mod_learninggoalwidget' },
        { key: 'settings:link', component: 'mod_learninggoalwidget' },
        { key: 'settings:addgoal', component: 'mod_learninggoalwidget' },
        { key: 'settings:save', component: 'mod_learninggoalwidget' },
      ];
      try {
        const results = await CoreStr.get_strings(strings);
        const context = {
          topiclabel: results[0],
          title: results[1],
          shortname: results[2],
          weburl: results[3],
          topictitle: topicTitle,
        };

        const [modal, goalName, goalShortname, goalUrl] = await showModal(
          context,
          results[4],
          TEMPLATES.GOAL_MODAL_VIEW,
          results[5],
        );
        modal.hide();

        // Add goal to local taxonomy
        addLocalGoal(topic, goalName, goalShortname, goalUrl);
        updateTaxonomyValue();

        loadGoals(topic);
      } catch (e) {
        console.error("Failed to create new goal", e);
      }
    };

    /**
     * Handle edit goal click event
     * @param {event} e Clicked event
     */
    const clickedEditGoal = async (e) => {
      e.preventDefault();

      var topicid = $(e.currentTarget).data('topicid');
      var goalid = $(e.currentTarget).data('goalid');
      const topic = getTopicById(topicid);
      const goal = getGoalById(goalid, topic);
      if (!topic || !goal) {
        return;
      }

      const topicTitle = topic.name;
      const oldGoalTitle = goal.name;
      const oldGoalShortname = goal.keyword;
      const oldGoalUrl = goal.link;

      let strings = [
        {key: 'settings:topic', component: 'mod_learninggoalwidget'},
        {key: 'settings:goal', component: 'mod_learninggoalwidget'},
        {key: 'settings:description', component: 'mod_learninggoalwidget'},
        {key: 'settings:link', component: 'mod_learninggoalwidget'},
        {key: 'settings:editgoal', component: 'mod_learninggoalwidget'},
        {key: 'settings:save', component: 'mod_learninggoalwidget'},
      ];
      try {
        const results = await CoreStr.get_strings(strings);
        const context = {
          topiclabel: results[0],
          topictitle: topicTitle,
          title: results[1],
          shortname: results[2],
          weburl: results[3],
          goaltitle: oldGoalTitle,
          goalshortname: oldGoalShortname,
          goalurl: oldGoalUrl,
        };

        const [modal, goalName, goalShortname, goalUrl] = await showModal(
          context,
          results[4],
          TEMPLATES.GOAL_MODAL_VIEW,
          results[5],
        );
        modal.hide();

        // Update goal
        const update = {
          name: goalName,
          keyword: goalShortname,
          link: goalUrl,
        };
        updateLocalGoal(goal, update);
        updateTaxonomyValue();

        loadGoals(topic);
      } catch (e) {
        console.error("Failed to edit goal", e);
      }
    };

    /**
     * Handle delete goal event
     * @param {event} e click event
     */
    const clickedDeleteGoal = async (e) => {
      e.preventDefault();

      const topicid = $(e.currentTarget).data('topicid');
      const goalid = $(e.currentTarget).data('goalid');
      const topic = getTopicById(topicid);
      const goalToDelete = getGoalById(goalid, topic);
      if (!topic || !goalToDelete) {
        return;
      }

      let strings = [
        { key: 'settings:deletegoal', component: 'mod_learninggoalwidget' },
        { key: 'settings:deletegoalmsg', component: 'mod_learninggoalwidget' },
        { key: 'settings:delete', component: 'mod_learninggoalwidget' },
      ];
      try {
        const results = await CoreStr.get_strings(strings);
        const modal = await showMessage(
          results[0],
          results[1],
          results[2],
        );
        modal.hide();

        // Mark goal as deleted
        deleteLocalGoal(goalToDelete, topic, true);
        updateTaxonomyValue();

        loadGoals(topic);
      } catch (e) {
        console.error("Failed to delete goal", e);
      }
    };

    /**
     * Handle goal move up event
     * @param {event} e click event
     */
    const clickedMoveupGoal = (e) => {
      e.preventDefault();

      const topicid = $(e.currentTarget).data('topicid');
      const goalid = $(e.currentTarget).data('goalid');
      const topic = getTopicById(topicid);
      const goalToMove = getGoalById(goalid, topic);
      if (!topic || !goalToMove || goalToMove.ranking == 1) {
        // No goal or is already first
        return;
      }

      for (let goal of topic.children) {
        // Only goal directly above is affected
        if (goal.ranking === goalToMove.ranking - 1) {
          updateLocalGoal(goal, {ranking: goal.ranking + 1});
          updateLocalGoal(goalToMove, {ranking: goalToMove.ranking - 1});
          break;
        }
      }
      updateTaxonomyValue();

      loadGoals(topic);
    };

    /**
     * Handle goal move down event
     * @param {event} e click event
     */
    const clickedMovedownGoal = (e) => {
      e.preventDefault();

      const topicid = $(e.currentTarget).data('topicid');
      const goalid = $(e.currentTarget).data('goalid');
      const topic = getTopicById(topicid);
      const goalToMove = getGoalById(goalid, topic);
      if (!topic || !goalToMove) {
        // No goal or is already first
        return;
      }

      for (let goal of topic.children) {
        // Only goal directly below is affected
        if (goal.ranking === goalToMove.ranking + 1) {
          updateLocalGoal(goal, {ranking: goal.ranking - 1});
          updateLocalGoal(goalToMove, {ranking: goalToMove.ranking + 1});
          break;
        }
      }
      updateTaxonomyValue();

      loadGoals(topic);
    };


    /**
     *
     * @param {arry} context Template context for
     * @param {string} title The title bar of the modal
     * @param {string} templateName The template name to render
     * @param {string} btnSaveText The SAVE button text
     */
    const showModal = async (context, title, templateName, btnSaveText) => {
      let strings = [
        { key: 'validation:missingtitle', component: 'mod_learninggoalwidget' },
        { key: 'validation:invalidlink', component: 'mod_learninggoalwidget' },
      ];
      const results = await CoreStr.get_strings(strings);
      const modal = await ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: title,
        body: Templates.render(templateName, context)
      });
      modal.setSaveButtonText(btnSaveText);

      modal.show();

      // Destroy when hidden.
      modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
      });

      return new Promise((resolve, reject) =>
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
            resolve([
              modal,
              titleInputfield[0].value,
              shortnameInputfield[0].value,
              urlInputfield[0].value
            ]);
          } else {
            event.preventDefault();
            event.stopPropagation();
            if (titleValid === false) {
              modal.getRoot().find('[data-action="titlefeedback"]')
                .text(results[0]);
              modal.getRoot().find('[data-action="titlefeedback"]')
                .css("display", "inline");
              reject("Title invalid");
            }
            if (urlValid === false) {
              modal.getRoot().find('[data-action="urlfeedback"]')
                .text(results[1]);
              modal.getRoot().find('[data-action="urlfeedback"]')
                .css("display", "inline");
              reject("Url invalid");
            }
          }
        })
      );
    };

    /**
     *
     * @param {*} title The title bar of the modal
     * @param {*} text The message text
     * @param {*} btnSaveText The SAVE button text
     *
     * @returns {void}
     */
    const showMessage = async (title, text, btnSaveText) => {
      const modal = await ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: title,
        body: text
      });
      modal.setSaveButtonText(btnSaveText);
      // Destroy when hidden.
      modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
      });
      modal.show();
      return new Promise((resolve) => modal.getRoot().on(ModalEvents.save, () => resolve(modal)));
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
        return { error: true, code: "validation:jsontop1" };
      } else if (!('children' in json) || json.children === null || json.children === undefined) {
        return { error: true, code: "validation:jsontop2" };
      } else if (!Array.isArray(json.children)) {
        return { error: true, code: "validation:jsontop3" };
      } else if (json.children.length === 0) {
        return { error: true, code: "validation:jsontop4" };
      }
      return { error: false };
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
        return { error: true, code: "validation:jsontopic1", codeParam: undefined };
      } else if (typeof (topic.name) !== 'string') {
        return { error: true, code: "validation:jsontopic2", codeParam: topic.name };
      } else if ('link' in topic) {
        if (typeof (topic.link) !== 'string') {
          return { error: true, code: "validation:jsontopic3", codeParam: topic.name };
        } else if (!isValidUrl(topic.link)) {
          return { error: true, code: "validation:jsontopic4", codeParam: topic.name };
        }
      } else if ('keyword' in topic && typeof (topic.keyword) !== 'string') {
        return { error: true, code: "validation:jsontopic5", codeParam: topic.name };
      } else if (!('children' in topic) || topic.children === null || topic.children === undefined) {
        return { error: false };
      } else if (!Array.isArray(topic.children)) {
        return { error: true, code: "validation:jsontopic6", codeParam: topic.name };
      }
      return { error: false };
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
        return { error: true, code: "validation:jsongoal1", codeParam: topicName };
      } else if (typeof (goal.name) !== 'string') {
        return { error: true, code: "validation:jsongoal2", codeParam: topicName };
      } else if ('link' in goal) {
        if (typeof (goal.link) !== 'string') {
          return { error: true, code: "validation:jsongoal3", codeParam: goal.name };
        } else if (!isValidUrl(goal.link)) {
          return { error: true, code: "validation:jsongoal4", codeParam: goal.name };
        }
      } else if ('keyword' in goal && typeof (goal.keyword) !== 'string') {
        return { error: true, code: "validation:jsongoal5", codeParam: goal.name };
      }
      return { error: false };
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
    const parseJSON = async (json) => {
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
      return { error: false, preview };
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
      reader.onload = async (evt) => {
        if (evt.target === null) {
          return;
        }
        try {
          const parsed = JSON.parse(reader.result);
          const check = await parseJSON(parsed);
          let strings = [
            { key: 'validation:invalid', component: 'mod_learninggoalwidget' },
            { key: 'validation:invalidfile', component: 'mod_learninggoalwidget' },
            { key: 'validation:close', component: 'mod_learninggoalwidget' },
            { key: 'settings:newtaxonomyheader', component: 'mod_learninggoalwidget' },
            { key: 'settings:newtaxonomymsg', component: 'mod_learninggoalwidget' },
            { key: 'settings:replace', component: 'mod_learninggoalwidget' },
          ];
          const results = await CoreStr.get_strings(strings);
          if (check.error) {
            const modal = await showMessage(
              results[0],
              results[1] + ":<br/>" + check.msg,
              results[2],
            );
            modal.hide();
            return;
          }

          const modal = await showMessage(
            results[3],
            results[4] + "\n" + check.preview,
            results[5],
          );
          modal.hide();

          taxonomy.children.forEach((topic) => {
            topic.children.forEach((goal) => {
              deleteLocalGoal(goal, topic, false);
            });
            deleteLocalTopic(topic, false);
          });

          $("#topics-list").children().remove();
          $("#learninggoals-list").children().remove();
          parsed.children.forEach((topic) => {
            const addedTopic = addLocalTopic(topic.name, topic.keyword, topic.link);
            topic.children.forEach((goal) => {
              addLocalGoal(addedTopic, goal.name, goal.keyword, goal.link);
            });
          });
          updateTaxonomyValue();
          console.log(parsed, taxonomy);

          loadTopics();
        } catch (e) {
          // Do nothing
        }
      };
    };

    /**
     * Download current JSON Taxonomy
     */
    const clickedJSONDownload = async () => {

      const strTaxonomy = await Controller.getTaxonomy({ instance: instance });
      const jsonTaxonomy = JSON.parse(strTaxonomy);

      let newTaxonomy = { name: jsonTaxonomy.name, children: [] };
      jsonTaxonomy.children.forEach((topic) => {
        let goals = [];
        let topicObj = {};
        if ('name' in topic) {
          topicObj.name = topic.name;
        }
        if ('keyword' in topic) {
          topicObj.keyword = topic.keyword;
        }
        if ('link' in topic) {
          topicObj.link = topic.link;
        }

        if ('children' in topic) {
          topic.children.forEach((goal) => {
            goals.push({});
            if ('name' in goal) {
              goals[goals.length - 1].name = goal.name;
            }
            if ('keyword' in goal) {
              goals[goals.length - 1].keyword = goal.keyword;
            }
            if ('link' in goal) {
              goals[goals.length - 1].link = goal.link;
            }
          });
        }
        topicObj.children = goals;
        newTaxonomy.children.push(topicObj);
      });
      const filename = 'taxonomy.json';
      const blob = new Blob([JSON.stringify(newTaxonomy, null, 2)], { type: 'text/csv' });
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

    /**
     * Download JSON Taxonomy template
     */
    const clickedJSONDownloadTemplate = async () => {
      const template = {
        "name": "Learning Goal's taxonomy",
        "children": [
          {
            "name": "Topic 1 Name",
            "keyword": "Topic 1 Shortname",
            "link": "https://example.com",
            "children": [
              {
                "name": "Learning Goal 1 of Topic 1",
                "keyword": "Goal 1 Shortname",
                "link": "https://example.com"
              },
              {
                "name": "Learning Goal 2 of Topic 1",
                "keyword": "Goal 2 Shortname",
                "link": "https://example.com"
              }
            ]
          },
          {
            "name": "Topic 2 Name",
            "keyword": "Topic 2 Shortname",
            "link": "https://example.com",
            "children": [
              {
                "name": "Learning Goal 1 of Topic 2",
                "keyword": "Goal 1 Shortname",
                "link": "https://example.com"
              },
              {
                "name": "Learning Goal 2 of Topic 2",
                "keyword": "Goal 2 Shortname",
                "link": "https://example.com"
              }
            ]
          }
        ]
      };
      const filename = 'taxonomy.json';
      const blob = new Blob([JSON.stringify(template, null, 2)], { type: 'text/csv' });
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

    /**
     * Returns the topic object by its id
     *
     * @param {number} topicid id of the topic
     * @return {object | null} topic or null
     */
    const getTopicById = (topicid) => {
      for (let topic of taxonomy.children) {
        if (topic.topicid == topicid) {
          return topic;
        }
      }
      return null;
    };

    /**
     * Returns the ranking that should be used next for the topics
     * It is = max ranking + 1
     *
     * @return {number} ranking
     */
    const getNextTopicRanking = () => {
      let ranking = 0;
      taxonomy.children.forEach((topic) => {
        ranking = Math.max(topic.ranking, ranking);
      });
      return ranking + 1;
    };

    /**
     * Returns the next available topic id
     *
     * @return {number} id
     */
    const getNextTopicId = () => {
      let id = 0;
      taxonomy.children.forEach((topic) => {
        id = Math.max(topic.topicid, id);
      });
      return id + 1;
    };

    /**
     * Returns the goal object by its id
     *
     * @param {number} goalid id of the goal
     * @param {object | undefined} topic to search
     * @return {object | null} goal or null
     */
    const getGoalById = (goalid, topic = undefined) => {
      if (topic) {
        for (let goal of topic.children) {
          if (goal.goalid == goalid) {
            return goal;
          }
        }
        return null;
      }
      for (let topic of taxonomy.children) {
        for (let goal of topic.children) {
          if (goal.goalid == goalid) {
            return goal;
          }
        }
      }
      return null;
    };

    /**
     * Returns the ranking that should be used next for the goals
     * It is = max ranking + 1
     *
     * @param {object} topic Topic to check
     * @return {number} ranking
     */
    const getNextGoalRanking = (topic) => {
      let ranking = 0;
      topic.children.forEach((goal) => {
        ranking = Math.max(goal.ranking, ranking);
      });
      return ranking + 1;
    };

    /**
     * Returns the next available goal id
     *
     * @return {number} id
     */
    const getNextGoalId = () => {
      let id = 0;
      taxonomy.children.forEach((topic) => {
        topic.children.forEach((goal) => {
          id = Math.max(goal.goalid, id);
        });
      });
      return id + 1;
    };

    /**
     * Add a new local topic
     *
     * @param {string} name Name of the new topic
     * @param {string} shortname Shortname of the new topic
     * @param {string} url Url of the new topic
     * @returns {object} newly created topic
     */
    const addLocalTopic = (name, shortname, url) => {
      let newTopic = {
        name: name,
        keyword: shortname,
        link: url,
        ranking: getNextTopicRanking(),
        topicid: getNextTopicId(),
        children: [],
        type: 'topic',
      };
      newTopic[ADD_KEY] = true;
      taxonomy.children.push(newTopic);

      return newTopic;
    };

    /**
     * Update a local topic
     *
     * @param {object} topic Topic to update
     * @param {object} update Update to apply
     */
    const updateLocalTopic = (topic, update) => {
      Object.keys(update).forEach((key) => {
        topic[key] = update[key];
      });
      topic[EDIT_KEY] = true;
    };

    /**
     * Marks a local topic as deleted
     * @param {object} topicToDelete Topic to delete
     * @param {boolean} reorder Whether to reorder the rankings of the other topics
     */
    const deleteLocalTopic = (topicToDelete, reorder) => {
      if (reorder) {
        taxonomy.children.forEach((topic) => {
          if (topic.ranking > topicToDelete.ranking) {
            topic.ranking--;
            topic[EDIT_KEY] = true;
          }
        });
      }
      topicToDelete[DELETE_KEY] = true;
      topicToDelete.ranking = -1;
    };

    /**
     * Add a new local goal
     *
     * @param {string} topic Parent topic to which the goal should be added
     * @param {string} name Name of the new goal
     * @param {string} shortname Shortname of the new goal
     * @param {string} url Url of the new goal
     * @returns {object} newly created goal
     */
    const addLocalGoal = (topic, name, shortname, url) => {
      let newGoal = {
        name: name,
        keyword: shortname,
        link: url,
        ranking: getNextGoalRanking(topic),
        goalid: getNextGoalId(),
        type: 'goal',
      };
      newGoal[ADD_KEY] = true;
      topic.children.push(newGoal);

      return newGoal;
    };

    /**
     * Update a local goal
     *
     * @param {object} goal Goal to update
     * @param {object} update Update to apply
     */
    const updateLocalGoal = (goal, update) => {
      Object.keys(update).forEach((key) => {
        goal[key] = update[key];
      });
      goal[EDIT_KEY] = true;
    };

    /**
     * Marks a local goal as deleted
     * @param {object} goalToDelete Goal to delete
     * @param {object} topic Topic parent of goal
     * @param {boolean} reorder Whether to reorder the rankings of the other goals
     */
    const deleteLocalGoal = (goalToDelete, topic, reorder) => {
      if (reorder) {
        topic.children.forEach((goal) => {
          if (goal.ranking > goalToDelete.ranking) {
            goal.ranking--;
            goal[EDIT_KEY] = true;
          }
        });
      }
      goalToDelete[DELETE_KEY] = true;
      goalToDelete.ranking = -1;
    };

    /**
     * Updates the value of the taxonomy
     */
    const updateTaxonomyValue = () => {
      document.querySelector("input[name='taxonomy']").value = JSON.stringify(taxonomy);
    };

    return {
      init: init
    };
  });
