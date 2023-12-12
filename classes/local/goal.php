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
 * Learning Goal Taxonomy object
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_learninggoalwidget\local;

/**
 * Class goal representation of a goal
 *
 * which consists of title (mandatory), shortname and a url (optional)
 *
 * @package   mod_learninggoalwidget
 * @copyright 2021 Know Center GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class goal {

    /**
     * goal title (mandatory)
     *
     * @var string
     */
    private $title;

    /**
     * goal shortname (optional)
     *
     * @var string
     */
    private $shortname;

    /**
     * goal url (optional)
     *
     * @var [type]
     */
    private $url;

     /**
      * ctor of class goal
      *
      * @param [type] $title
      * @param [type] $shortname
      * @param [type] $url
      */
    public function __construct($title, $shortname, $url) {
        $this->title = $title;
        $this->shortname = $shortname;
        $this->url = $url;
    }

    /**
     * get the goal's title
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * get the goal's shortname
     *
     * @return string
     */
    public function get_shortname() {
        return $this->shortname;
    }

    /**
     * get the goal's url
     *
     * @return string
     */
    public function get_url() {
        return $this->url;
    }


    /**
     * factory method which creates a goal
     * from a database record
     *
     * @param [sqlrecord] $goalrecord
     * @return goal
     */
    public static function from_record($goalrecord): goal {
        return new goal($goalrecord->title, $goalrecord->shortname, $goalrecord->url);
    }
}
