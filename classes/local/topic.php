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
 * Learning Goal Topic object
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

use mod_learninggoalwidget\local\goal;

/**
 * Topics class
 *
 * a topic consists of a title, shortname and url
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class topic {

    /**
     * title of the topic
     *
     * @var string
     */
    private $title;

    /**
     * shortname of the topic
     *
     * @var string
     */
    private $shortname;

    /**
     * url of the topic
     *
     * @var string
     */
    private $url;

    /**
     * array of goals related to the topic
     *
     * @var array
     */
    private $goals;

    /**
     * c'tor of a topic
     *
     * @param string $title
     * @param string $shortname
     * @param string $url
     * @param array $goals
     */
    public function __construct($title, $shortname, $url, $goals) {
        $this->title = $title;
        $this->shortname = $shortname;
        $this->url = $url;
        $this->goals = $goals;
    }

    /**
     * return the topic title
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * return the topic shortname
     *
     * @return string
     */
    public function get_shortname() {
        return $this->shortname;
    }

    /**
     * return the topic url
     *
     * @return string
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * return the topic goals
     *
     * @return array
     */
    public function get_goals() {
        return $this->goals;
    }
}
