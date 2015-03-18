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
 * Defines core nodes for my profile navigation tree.
 *
 * @package   core
 * @copyright 2015 onwards Ankit Agarwal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines core nodes for my profile navigation tree.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser is the user viewing profile, current user ?
 * @param stdClass $course course object
 *
 * @return bool
 */
function core_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG;

    $miscategory = new core_user\output\myprofile\category('miscellaneous', get_string('miscellaneous'));
    $reportcategory = new core_user\output\myprofile\category('reports', get_string('reports'), 'miscellaneous');
    $admincategory = new core_user\output\myprofile\category('administration', get_string('administration'), 'miscellaneous');

    // Add categories.
    $tree->add_category($miscategory);
    $tree->add_category($reportcategory);
    $tree->add_category($admincategory);

    // Add core nodes.
    // Full profile node.
    if (empty($CFG->forceloginforprofiles) || $iscurrentuser ||
        has_capability('moodle/user:viewdetails', context_user::instance($user->id))
        || has_coursecontact_role($user->id)) {
        $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        $node = new core_user\output\myprofile\node('miscellaneous', 'fullprofile', get_string('fullprofile'), null, $url);
        $tree->add_node($node);
    }

    // Preference page.
    if ($iscurrentuser || is_siteadmin()) {
        $url = new moodle_url('/user/preferences.php', array('userid' => $user->id));
        $title = $iscurrentuser ? get_string('usercurrentsettings') : get_string('userviewingsettings', 'moodle', fullname($user));
        $node = new core_user\output\myprofile\node('administration', 'preferences', $title, null, $url);
        $tree->add_node($node);
    }

    // Login as ...
    $context = !empty($course) ? context_course::instance($course->id) : context_system::instance();
    $id = !empty($course) ? $course->id : SITEID;
    if (!$user->deleted && !$iscurrentuser &&
                !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas',
                $context) && !is_siteadmin($user->id)) {
        $url = new moodle_url('/course/logdadainas.php',
                array('id' => $id, 'user' => $user->id, 'sesskey' => sesskey()));
        $node = new  core_user\output\myprofile\node('administration', 'loginas', get_string('loginas'), null, $url);
        $tree->add_node($node);
    }

    // Grades.
    $usercontext = context_user::instance($user->id);
    $anyreport = has_capability('moodle/user:viewuseractivitiesreport', $usercontext);
    if ($anyreport || ($course->showreports && $iscurrentuser)) {
        // Add grade hardcoded grade report if necessary.
        $gradeaccess = false;
        if (has_capability('moodle/grade:viewall', $context)) {
            // Can view all course grades.
            $gradeaccess = true;
        } else if ($course->showgrades) {
            if ($iscurrentuser && has_capability('moodle/grade:view', $context)) {
                // Can view own grades.
                $gradeaccess = true;
            } else if (has_capability('moodle/grade:viewall', $usercontext)) {
                // Can view grades of this user - parent most probably.
                $gradeaccess = true;
            } else if ($anyreport) {
                // Can view grades of this user - parent most probably.
                $gradeaccess = true;
            }
        }
        if ($gradeaccess) {
            $url = new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $id, 'user' => $user->id));
            $node = new core_user\output\myprofile\node('reports', 'grades', get_string('grade'), null, $url);
            $tree->add_node($node);
        }
    }
    return true;
}
