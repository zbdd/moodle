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
 * Grader Report Functionality.
 *
 * @module    moodle-gradereport_grader-gradereporttable
 * @package   gradereport_grader
 * @copyright 2014 UC Regents
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Alfonso Roman <aroman@oid.ucla.edu>
 */

/**
 * @module moodle-gradereport_grader-gradereporttable
 */

var SELECTORS = {
        FOOTERROW: '#user-grades .avg',
        GRADECELL: 'td.grade',
        GRADERTABLE: '.gradeparent table',
        GRADEPARENT: '.gradeparent',
        HEADERCELL: '.gradebook-header-cell',
        STUDENTHEADER: '#studentheader',
        USERCELL: '#user-grades .user.cell'
    },
    CSS = {
        OVERRIDDEN: 'overridden',
        STICKYFOOTER: 'gradebook-footer-row-sticky',
        TOOLTIPACTIVE: 'tooltipactive'
    };

/**
 * The Grader Report Table.
 *
 * @namespace M.gradereport_grader
 * @class ReportTable
 * @constructor
 */
function ReportTable() {
    ReportTable.superclass.constructor.apply(this, arguments);
}

Y.extend(ReportTable, Y.Base, {
    /**
     * Array of EventHandles.
     *
     * @type EventHandle[]
     * @property _eventHandles
     * @protected
     */
    _eventHandles: [],

    /**
     * A Node reference to the grader table.
     *
     * @property graderTable
     * @type Node
     */
    graderTable: null,

    /**
     * Setup the grader report table.
     *
     * @method initializer
     */
    initializer: function() {
        // Some useful references within our target area.
        this.graderRegion = Y.one(SELECTORS.GRADEPARENT);
        this.graderTable = Y.one(SELECTORS.GRADERTABLE);

        // Setup the floating headers.
        this.setupFloatingHeaders();

        // Setup the mouse tooltips.
        this.setupTooltips();
    },

    /**
     * Show the loading spinner.
     *
     * @method showSpinner
     * @protected
     */
    showSpinner: function() {
        // Show the grading spinner.
        Y.one(SELECTORS.SPINNER).show();
    },

    /**
     * Hide the loading spinner.
     *
     * @method hideSpinner
     * @protected
     */
    hideSpinner: function() {
        // Hide the grading spinner.
        Y.one(SELECTORS.SPINNER).hide();
    },

    /**
     * Get the text content of the username for the specified grade item.
     *
     * @method getGradeUserName
     * @param {Node} cell The grade item cell to obtain the username for
     * @return {String} The string content of the username cell.
     */
    getGradeUserName: function(cell) {
        var userrow = cell.ancestor('tr'),
            usercell = userrow.one("th.user .username");

        if (usercell) {
            return usercell.get('text');
        } else {
            return '';
        }
    },

    /**
     * Get the text content of the item name for the specified grade item.
     *
     * @method getGradeItemName
     * @param {Node} cell The grade item cell to obtain the item name for
     * @return {String} The string content of the item name cell.
     */
    getGradeItemName: function(cell) {
        var itemcell = Y.one("th.item[data-itemid='" + cell.getData('itemid') + "']");
        if (itemcell) {
            return itemcell.get('text');
        } else {
            return '';
        }
    },

    /**
     * Get the text content of any feedback associated with the grade item.
     *
     * @method getGradeFeedback
     * @param {Node} cell The grade item cell to obtain the item name for
     * @return {String} The string content of the feedback.
     */
    getGradeFeedback: function(cell) {
        return cell.getData('feedback');
    }
});

Y.namespace('M.gradereport_grader').ReportTable = ReportTable;
Y.namespace('M.gradereport_grader').init = function(config) {
    return new Y.M.gradereport_grader.ReportTable(config);
};
