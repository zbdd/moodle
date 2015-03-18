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
 * Defines various library functions.
 *
 * @package   core_badges
 * @copyright 2015 onwards Ankit Agarwal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function core_badges_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (isguestuser($user) || !$iscurrentuser) {
        // Since the link is for current user context only show it only when the site viewing the user is the current user.
        return true;
    }
    $category = new core_user\output\myprofile\category('badges', get_string('managebadges', 'badges'), null);
    $url = new moodle_url("/badges/mybadges.php");
    $mybadges = new core_user\output\myprofile\node('badges', 'mybadges', get_string('mybadges', 'badges'), null, $url);

    // Add nodes.
    $category->add_node($mybadges);
    $tree->add_category($category);
    return true;
}