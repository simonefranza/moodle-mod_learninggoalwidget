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
 * Structure step of the backup
 *
 * @package   mod_learninggoalwidget
 * @category  backup
 * @copyright 2024 onwards Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the
 * backup_learninggoalwidget_activity_task
 */

/**
 * Define the complete learninggoalwidget structure for backup, with file and id annotations
 */
class backup_learninggoalwidget_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure of the backup
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $learninggoalwidget = new backup_nested_element('learninggoalwidget', ['id'],
            ['name', 'intro', 'introformat', 'timecreated', 'timemodified']);

        $topics = new backup_nested_element('topics');

        $topic = new backup_nested_element('topic', ['id'], [
            'title', 'shortname', 'url']);

        $goals = new backup_nested_element('goals');

        $goal = new backup_nested_element('goal', ['id'], [
            'title', 'shortname', 'url']);

        $itopics = new backup_nested_element('itopics');

        $itopic = new backup_nested_element('itopic', ['id'], [
            'coursemodule', 'instance', 'ranking']);

        $igoals = new backup_nested_element('igoals');

        $igoal = new backup_nested_element('igoal', ['id'], [
            'coursemodule', 'instance', 'ranking']);

        $userprogresses = new backup_nested_element('userprogresses');

        $userprogress = new backup_nested_element('userprogress', ['id'],
            ['coursemodule', 'instance', 'userid', 'progress']);

        // Build the tree.
        $learninggoalwidget->add_child($topics);
        $topics->add_child($topic);

        $topic->add_child($itopics);
        $itopics->add_child($itopic);

        $topic->add_child($goals);
        $goals->add_child($goal);

        $goal->add_child($igoals);
        $igoals->add_child($igoal);

        $goal->add_child($userprogresses);
        $userprogresses->add_child($userprogress);

        // Define sources.
        $learninggoalwidget->set_source_table('learninggoalwidget', ['id' => backup::VAR_ACTIVITYID]);

        $topic->set_source_sql('
            SELECT *
              FROM {learninggoalwidget_topic} t
        INNER JOIN {learninggoalwidget_i_topics} it
                ON t.id = it.topic
             WHERE it.course = ? AND it.coursemodule = ?
             ',
            [backup::VAR_COURSEID, backup::VAR_MODID]);

        $itopic->set_source_sql('
            SELECT *
              FROM {learninggoalwidget_i_topics} it
             WHERE it.topic = ?
             ',
            [backup::VAR_PARENTID]);

        $goal->set_source_sql('
            SELECT *
              FROM {learninggoalwidget_goal} g
        INNER JOIN {learninggoalwidget_i_goals} ig
                ON g.id = ig.goal
             WHERE ig.course = ? AND ig.coursemodule = ? AND g.topic = ?
             ',
            [backup::VAR_COURSEID, backup::VAR_MODID, backup::VAR_PARENTID]);
        $igoal->set_source_sql('
            SELECT *
              FROM {learninggoalwidget_i_goals} ig
             WHERE ig.course = ? AND ig.coursemodule = ? AND ig.topic = ? AND ig.goal = ?
             ',
            [backup::VAR_COURSEID, backup::VAR_MODID, '../../../../id', backup::VAR_PARENTID]);
        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $userprogress->set_source_sql('
                SELECT *
                  FROM {learninggoalwidget_i_userpro} pro
                 WHERE pro.course = ? AND pro.coursemodule = ? AND pro.topic = ? AND pro.goal = ?
                 ',
                [backup::VAR_COURSEID, backup::VAR_MODID, '../../../../id', backup::VAR_PARENTID]);
        }

        // Define id annotations.
        $userprogress->annotate_ids('user', 'userid');

        // Return the root element (learninggoalwidget), wrapped into standard activity structure.
        return $this->prepare_activity_structure($learninggoalwidget);
    }
}
