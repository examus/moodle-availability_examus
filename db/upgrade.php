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
 * Availability plugin for integration with Examus proctoring system.
 *
 * @package    availability_examus
 * @copyright  2017 Max Pomazuev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * availability examus upgrade
 * @param string $oldversion Oldversion
 * @return bool
 */
function xmldb_availability_examus_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Add a new column newcol to the mdl_myqtype_options.
    if ($oldversion < 2017061602) {
        $table = new xmldb_table('availability_examus');

        $field = new xmldb_field('duration');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('timescheduled', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Conditionally launch add field timescheduled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Examus savepoint reached.
        upgrade_plugin_savepoint(true, 2017061602, 'availability', 'examus');

    }

    if ($oldversion < 2019031502) {
        $table = new xmldb_table('availability_examus');

        $field = new xmldb_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Conditionally launch add field timescheduled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    }

    return true;
}
