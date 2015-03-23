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
    global $CFG, $USER, $DB;

    $usercontext = context_user::instance($user->id, MUST_EXIST);
    $systemcontext = context_system::instance();
    $context = !empty($course) ? context_course::instance($course->id) : $systemcontext;
    $courseid = !empty($course) ? $course->id : SITEID;

    $contactcategory = new core_user\output\myprofile\category('contact', get_string('contactdetails'));
    $miscategory = new core_user\output\myprofile\category('miscellaneous', get_string('miscellaneous'));
    $reportcategory = new core_user\output\myprofile\category('reports', get_string('reports'), 'miscellaneous');
    $admincategory = new core_user\output\myprofile\category('administration', get_string('administration'), 'miscellaneous');

    // Add categories.
    $tree->add_category($contactcategory);
    $tree->add_category($miscategory);
    $tree->add_category($reportcategory);
    $tree->add_category($admincategory);

    // Add core nodes.
    // Full profile node.
    if (!empty($course)) {
        if (empty($CFG->forceloginforprofiles) || $iscurrentuser ||
            has_capability('moodle/user:viewdetails', context_user::instance($user->id))
            || has_coursecontact_role($user->id)) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
            $node = new core_user\output\myprofile\node('miscellaneous', 'fullprofile', get_string('fullprofile'), null, $url);
            $tree->add_node($node);
        }
    }

    // Edit profile.
    if (isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) {
        if (($iscurrentuser || is_siteadmin($USER) || !is_siteadmin($user)) && has_capability('moodle/user:update',
                    $systemcontext)) {
            $url = new moodle_url('/user/editadvanced.php', array('id' => $user->id, 'course' => $courseid));
            $node = new core_user\output\myprofile\node('administration', 'editprofile', get_string('editmyprofile'), null, $url);
            $tree->add_node($node);
        } else if ((has_capability('moodle/user:editprofile', $usercontext) && !is_siteadmin($user))
                   || ($iscurrentuser && has_capability('moodle/user:editownprofile', $systemcontext))) {
            $userauthplugin = false;
            if (!empty($user->auth)) {
                $userauthplugin = get_auth_plugin($user->auth);
            }
            if ($userauthplugin && $userauthplugin->can_edit_profile()) {
                $url = $userauthplugin->edit_profile_url();
                if (empty($url)) {
                    $url = new moodle_url('/user/edit.php', array('userid' => $user->id, 'course' => $id));
                }
                $node = new core_user\output\myprofile\node('administration', 'editprofile',
                        get_string('editmyprofile'), null, $url);
                $tree->add_node($node);
            }
        }
    }
    // Preference page.
    if ($iscurrentuser || is_siteadmin()) {
        $url = new moodle_url('/user/preferences.php', array('userid' => $user->id));
        $title = $iscurrentuser ? get_string('usercurrentsettings') : get_string('userviewingsettings', 'moodle', fullname($user));
        $node = new core_user\output\myprofile\node('administration', 'preferences', $title, null, $url);
        $tree->add_node($node);
    }

    // Login as ...
    if (!$user->deleted && !$iscurrentuser &&
                !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas',
                $context) && !is_siteadmin($user->id)) {
        $url = new moodle_url('/course/loginas.php',
                array('id' => $courseid, 'user' => $user->id, 'sesskey' => sesskey()));
        $node = new  core_user\output\myprofile\node('administration', 'loginas', get_string('loginas'), null, $url);
        $tree->add_node($node);
    }

    // Contact details.
    if (has_capability('moodle/user:viewhiddendetails', $usercontext)) {
        $hiddenfields = array();
    } else {
        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
    }
    if (has_capability('moodle/site:viewuseridentity', $context)) {
        $identityfields = array_flip(explode(',', $CFG->showuseridentity));
    } else {
        $identityfields = array();
    }

    if (!isset($hiddenfields['country']) && $user->country) {
        $node = new core_user\output\myprofile\node('contact', 'country', get_string('country'), null, null,
                get_string($user->country, 'countries'));
        $tree->add_node($node);
    }

    if (!isset($hiddenfields['city']) && $user->city) {
        $node = new core_user\output\myprofile\node('contact', 'city', get_string('city'), null, null, $user->city);
        $tree->add_node($node);
    }

    if (isset($identityfields['address']) && $user->address) {
        $node = new core_user\output\myprofile\node('contact', 'address', get_string('address'), null, null, $user->address);
        $tree->add_node($node);
    }

    if (isset($identityfields['phone1']) && $user->phone1) {
        $node = new core_user\output\myprofile\node('contact', 'phone1', get_string('phone'), null, null, $user->phone1);
        $tree->add_node($node);
    }

    if (isset($identityfields['phone2']) && $user->phone2) {
        $node = new core_user\output\myprofile\node('contact', 'phone2', get_string('phone2'), null, null, $user->phone2);
        $tree->add_node($node);
    }

    if (isset($identityfields['institution']) && $user->institution) {
        $node = new core_user\output\myprofile\node('contact', 'institution', get_string('institution'), null, null,
                $user->institution);
        $tree->add_node($node);
    }

    if (isset($identityfields['department']) && $user->department) {
        $node = new core_user\output\myprofile\node('contact', 'department', get_string('department'), null, null,
            $user->institution);
        $tree->add_node($node);
    }

    if (isset($identityfields['idnumber']) && $user->idnumber) {
        $node = new core_user\output\myprofile\node('contact', 'idnumber', get_string('idnumber'), null, null,
            $user->institution);
        $tree->add_node($node);
    }

    if (isset($identityfields['email']) and ($iscurrentuser
                                             or $user->maildisplay == 1
                                             or has_capability('moodle/course:useremail', $usercontext)
                                             or ($user->maildisplay == 2 and enrol_sharing_course($user, $USER)))) {
        $node = new core_user\output\myprofile\node('contact', 'email', get_string('email'), null, null,
            obfuscate_mailto($user->email, ''));
        $tree->add_node($node);
    }

    if ($user->url && !isset($hiddenfields['webpage'])) {
        $url = $user->url;
        if (strpos($user->url, '://') === false) {
            $url = 'http://'. $url;
        }
        $webpageurl = new moodle_url($url);
        $node = new core_user\output\myprofile\node('contact', 'webpage', get_string('webpage'), null, $webpageurl);
        $tree->add_node($node);
    }

    if ($user->icq && !isset($hiddenfields['icqnumber'])) {
        $imurl = new moodle_url('http://web.icq.com/wwp', array('uin' => $user->icq) );
        $iconurl = new moodle_url('http://web.icq.com/whitepages/online', array('icq' => $user->icq, 'img' => '5'));
        $statusicon = html_writer::tag('img', '',
                array('src' => $iconurl, 'class' => 'icon icon-post', 'alt' => get_string('status')));
        $node = new core_user\output\myprofile\node('contact', 'icqnumber', get_string('icqnumber'), null, null,
            html_writer::link($imurl, s($user->icq) . $statusicon));
        $tree->add_node($node);
    }

    if ($user->skype && !isset($hiddenfields['skypeid'])) {
        $imurl = 'skype:'.urlencode($user->skype).'?call';
        $iconurl = new moodle_url('http://mystatus.skype.com/smallicon/'.urlencode($user->skype));
        if (is_https()) {
            // Bad luck, skype devs are lazy to set up SSL on their servers - see MDL-37233.
            $statusicon = '';
        } else {
            $statusicon = html_writer::empty_tag('img',
                array('src' => $iconurl, 'class' => 'icon icon-post', 'alt' => get_string('status')));
        }

        $node = new core_user\output\myprofile\node('contact', 'skypeid', get_string('skypeid'), null, null,
            html_writer::link($imurl, s($user->skype) . $statusicon));
        $tree->add_node($node);
    }
    if ($user->yahoo && !isset($hiddenfields['yahooid'])) {
        $imurl = new moodle_url('http://edit.yahoo.com/config/send_webmesg', array('.target' => $user->yahoo, '.src' => 'pg'));
        $iconurl = new moodle_url('http://opi.yahoo.com/online', array('u' => $user->yahoo, 'm' => 'g', 't' => '0'));
        $statusicon = html_writer::tag('img', '',
            array('src' => $iconurl, 'class' => 'iconsmall icon-post', 'alt' => get_string('status')));

        $node = new core_user\output\myprofile\node('contact', 'yahooid', get_string('yahooid'), null, null,
            html_writer::link($imurl, s($user->yahoo) . $statusicon));
        $tree->add_node($node);
    }
    if ($user->aim && !isset($hiddenfields['aimid'])) {
        $imurl = 'aim:goim?screenname='.urlencode($user->aim);
        $node = new core_user\output\myprofile\node('contact', 'aimid', get_string('aimid'), null, null,
            html_writer::link($imurl, s($user->aim)));
        $tree->add_node($node);
    }
    if ($user->msn && !isset($hiddenfields['msnid'])) {
        $node = new core_user\output\myprofile\node('contact', 'msnid', get_string('msnid'), null, null,
            s($user->msn));
        $tree->add_node($node);
    }

    // First access. (Why only for sites ?)
    if (!isset($hiddenfields['firstaccess']) && empty($course)) {
        if ($user->firstaccess) {
            $datestring = userdate($user->firstaccess)."&nbsp; (".format_time(time() - $user->firstaccess).")";
        } else {
            $datestring = get_string("never");
        }
        $node = new core_user\output\myprofile\node('miscellaneous', 'firstaccess', get_string('firstsiteaccess'), null, null,
            $datestring);
        $tree->add_node($node);
    }

    // Last access.
    if (!isset($hiddenfields['lastaccess'])) {
        if (empty($course)) {
            $string = get_string('lastsiteaccess');
            if ($user->lastaccess) {
                $datestring = userdate($user->lastaccess) . "&nbsp; (" . format_time(time() - $user->lastaccess) . ")";
            } else {
                $datestring = get_string("never");
            }
        } else {
            $string = get_string('lastcourseaccess');
            if ($lastaccess = $DB->get_record('user_lastaccess', array('userid' => $user->id, 'courseid' => $course->id))) {
                $datestring = userdate($lastaccess->timeaccess)."&nbsp; (".format_time(time() - $lastaccess->timeaccess).")";
            } else {
                $datestring = get_string("never");
            }
        }

        $node = new core_user\output\myprofile\node('miscellaneous', 'lastaccess', $string, null, null,
            $datestring);
        $tree->add_node($node);
    }

    // Last ip.
    if (has_capability('moodle/user:viewlastip', $usercontext) && !isset($hiddenfields['lastip'])) {
        if ($user->lastip) {
            $iplookupurl = new moodle_url('/iplookup/index.php', array('ip' => $user->lastip, 'user' => $USER->id));
            $ipstring = html_writer::link($iplookupurl, $user->lastip);
        } else {
            $ipstring = get_string("none");
        }
        $node = new core_user\output\myprofile\node('miscellaneous', 'lastip', get_string('lastip'), null, null,
            $ipstring);
        $tree->add_node($node);
    }
}
