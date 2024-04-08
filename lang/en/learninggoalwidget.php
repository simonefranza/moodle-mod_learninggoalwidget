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
 * Strings for component 'learninggoalwidget', language 'en'
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['title'] = 'Learning Goal\'s taxonomy';
$string['learninggoalwidget:addinstance'] = 'Add a new Learning Goal Widget';
$string['learninggoalwidget:view'] = 'View Learning Goal Widget';
$string['learninggoalwidgettext'] = 'Learning Goal Widget text';
$string['modulename'] = 'Learning Goal Widget';
$string['modulename_help'] = '';
$string['modulename_link'] = 'mod/learninggoalwidget/view';
$string['modulenameplural'] = 'Learning Goal Widgets';
$string['privacy:metadata'] = '';
$string['pluginadministration'] = 'Learning Goal Widget administration';
$string['pluginname'] = 'Learning Goal Widget';
$string['search:activity'] = 'learninggoalwidget';

$string['settings:header'] = 'Topics and learning goals';
$string['settings:topic'] = 'Topic';
$string['settings:goal'] = 'Learning goal';
$string['settings:description'] = 'Short description';
$string['settings:link'] = 'Web link';
$string['settings:addtopic'] = 'Add a new topic';
$string['settings:edittopic'] = 'Edit topic';
$string['settings:deletetopic'] = 'Delete topic';
$string['settings:deletetopicmsg'] = 'Do you really want to delete the topic?';
$string['settings:save'] = 'Save';
$string['settings:delete'] = 'Delete';
$string['settings:addgoal'] = 'Add a new learning goal';
$string['settings:editgoal'] = 'Edit learning goal';
$string['settings:deletegoal'] = 'Delete learning goal';
$string['settings:deletegoalmsg'] = 'Do you really want to delete the learning goal?';
$string['settings:showgoals'] = 'Click on a topic to show its learning goals';
$string['settings:nogoals'] = 'There are no learning goals for the chosen topic';
$string['settings:topicheader'] = 'Topics';
$string['settings:goalheader'] = 'Learning goals';
$string['settings:btnnewtopic'] = 'New topic';
$string['settings:btnnewgoal'] = 'New learning goal';
$string['settings:jsonheader'] = 'JSON format';
$string['settings:btnjsonupload'] = 'Upload';
$string['settings:btnjsondownload'] = 'Download';
$string['settings:notopicsmessage'] = 'There are no topics yet';
$string['settings:newtaxonomyheader'] = 'New topics and learning goals';
$string['settings:newtaxonomymsg'] = 'Do you want to replace the current topics and learning goals with the following?';
$string['settings:replace'] = 'Replace';

$string['validation:missingtitle'] = 'The title is mandatory';
$string['validation:invalidlink'] = 'The URL is invalid';
$string['validation:invalid'] = 'Invalid taxonomy';
$string['validation:close'] = 'Close';
$string['validation:invalidfile'] = 'The uploaded file is invalid';
$string['validation:jsontop1'] = 'The "name" property is missing.';
$string['validation:jsontop2'] = 'The "children" property is missing.';
$string['validation:jsontop3'] = 'The "children" property is not an array.';
$string['validation:jsontop4'] = 'The "children" array is empty.';
$string['validation:jsontopic1'] = 'A topic is missing the "name" property.';
$string['validation:jsontopic2'] = 'The "name" property of the topic "{$a}" is not a string.';
$string['validation:jsontopic3'] = 'The "link" property of the topic "{$a}" is not a string.';
$string['validation:jsontopic4'] = 'The "link" property of the topic "{$a}" is invalid.';
$string['validation:jsontopic5'] = 'The "keyword" property of the topic "{$a}" is not a string.';
$string['validation:jsontopic6'] = 'The "children" property of the topic "{$a}" is not an array.';
$string['validation:jsongoal1'] = 'A learning goal of the topic "{$a}" has no "name" property.';
$string['validation:jsongoal2'] = 'The "name" property of a learning goal of the topic "{$a}" is not a string.';
$string['validation:jsongoal3'] = 'The "link" property of the learning goal "{$a}" is not a string.';
$string['validation:jsongoal4'] = 'The "link" property of the learning goal "{$a}" is invalid.';
$string['validation:jsongoal5'] = 'The "keyword" property of the learning goal "{$a}" is not a string.';
$string['validation:missinggoal'] = 'The topics [{$a}] have no learning goals. Please add at least one to each of them.';

$string['guestaccess'] = 'You need to login first';
$string['noaccess'] = 'You need to login first';
$string['contentview'] = 'Learning goals view';
$string['examview'] = 'Progress view';
$string['progresslabel0'] = 'Low (0%)';
$string['progresslabel50'] = 'Medium (50%)';
$string['progresslabel100'] = 'High (100%)';
$string['progressdialogtitle'] = 'Update learning goal orogress';
$string['progresslegendlabel'] = 'Learning progress';
$string['textualbulletpointlisttitle'] = 'Overview of topics and learning goals';
$string['treemapaccessibilitytext'] = 'Welcome to the Learning Goals Widget <br/> <br/>
This tool helps you reflect on the topics you learned
and keeps track of your progress.
On the left side there are the topics and on right side
the learning goals. Each of these goals has a percent near
it representing your progress so far. <br/>
You can change this value, by clicking on the topic or on
the learning goal and then moving the slider
to the desired position. On the right side of each topic
there is an average of the progress that you made on the
learning goals. <br/> <br/>
If you are using a screen reader, you can navigate through the document
with the Tab key. By pressing enter you can expand the topics.
When an action for the keyboard is available, it will be announced.
This tool is optimized for Google Chrome and its extension called
Screen Reader. For an optimal experience we suggest you to use those. <br/>
Under the accessibility icon you can find two buttons to change
the size of the font. If instead you would like to change the zoom
you can press Ctrl and + or Ctrl and -. <br/>
The third icon is used to change the colors of the widget. <br/>
If you are colorblind, there you can find some colorschemes,
that have a dark blue outline. These have a high contrast and
should be suitable to you. <br/> <br/>
We wish you a happy learning experience!';
