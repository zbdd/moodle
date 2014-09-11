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
 * Activity Form.
 *
 * @package     activity_form
 * @category    blocks
 * @copyright   Moodle 2014
 * @author      Zachary Durber
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Defines the activity form.
 */
class activity_form extends moodleform {

    /**
     * Defines the form.
     */
    public function definition() {
        $mform = $this->_form;
    }

    /**
     * Append an activity to the form.
     */
    public function append_activity($id, $module, $activity) {
        global $OUTPUT;
        $mform = $this->_form;
        $modulename = get_string('modulename', $module);

        $moduleimage = html_writer::tag('img', $OUTPUT->pix_icon('icon', $modulename, 'mod_'.$module, array('class'=>'iconlarge')));
        $mform->addElement('header', 'course_' . $id . '_alert', $moduleimage . get_string('activityoverview', $module));
        $mform->addElement('html', $activity);
        $mform->setExpanded('course_' . $id . '_alert', false);
    }

    /**
     * Retrieve the raw HTML of the form.
     */
    public function html() {
        return $this->_form->toHtml();
    }
} 
?>
