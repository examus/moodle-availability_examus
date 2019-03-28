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

require_once('../../../config.php');
require_once($CFG->libdir . "/formslib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();

admin_externalpage_setup('availability_examus_settings');

$action = optional_param('action', '', PARAM_ALPHA);

switch ($action) {
    case 'renew':
        $id = required_param('id', PARAM_TEXT);

        if(\availability_examus\common::reset_entry(['id' => $id])){
            redirect('index.php', get_string('new_entry_created', 'availability_examus'),
                     null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            redirect('index.php', get_string('entry_exist', 'availability_examus'),
                    null, \core\output\notification::NOTIFY_ERROR);
        }

        break;
    default:
        break;
}


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'availability_examus'));

$table = new flexible_table('availability_examus_table');

$table->define_columns(['timemodified', 'timescheduled', 'u_email', 'courseid', 'cmid', 'status', 'review_link', 'create_entry']);
$table->define_headers([
    get_string('date_modified', 'availability_examus'),
    get_string('time_scheduled', 'availability_examus'),
    get_string('user'),
    get_string('course'),
    get_string('module', 'availability_examus'),
    get_string('status', 'availability_examus'),
    get_string('review', 'availability_examus'),
    '']);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'date_modified');
$table->no_sorting('courseid');
$table->no_sorting('cmid');
$table->set_attribute('id', 'entries');
$table->set_attribute('class', 'generaltable generalbox');
$table->setup();

$select = [
    'e.id id',
    'e.timemodified timemodified',
    'timescheduled',
    'u.firstname u_firstname',
    'u.lastname u_lastname',
    'u.email u_email',
    'e.status status',
    'review_link',
    'cmid',
    'courseid'
];
$query = 'SELECT '.implode(', ', $select).' FROM {availability_examus} e LEFT JOIN {user} u ON u.id=e.userid';
$orderBy = $table->get_sql_sort();
if($orderBy){
    $query .= ' ORDER BY '. $orderBy;
}
$entries = $DB->get_records_sql($query);

if (!empty($entries)) {
    foreach ($entries as $entry) {
        $row = array();

        $date = usergetdate($entry->timemodified);
        $row[] = '<b>' . $date['year'] . '.' . $date['mon'] . '.' . $date['mday'] . '</b> ' .
            $date['hours'] . ':' . $date['minutes'];

        if ($entry->timescheduled) {
            $timescheduled = usergetdate($entry->timescheduled);
            $row[] = '<b>' . $timescheduled['year'] . '.' . $timescheduled['mon'] . '.' . $timescheduled['mday'] . '</b> ' .
                $timescheduled['hours'] . ':' . $timescheduled['minutes'];
        } else {
            $row[] = '';
        }

        $row[] = $entry->u_firstname . " " . $entry->u_lastname . "<br>" . $entry->u_email;

        $course = get_course($entry->courseid);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($entry->cmid);

        $row[] = $course->fullname;
        $row[] = $cm->get_formatted_name();
        $row[] = $entry->status;
        if ($entry->review_link !== null) {
            $row[] = "<a href='" . $entry->review_link . "'>" . get_string('link', 'availability_examus') . "</a>";
        } else {
            $row[] = "-";
        }

        if ($entry->status != 'Not inited' and $entry->status != 'Scheduled') {
            $row[] = "<form action='index.php' method='post'>" .
                "<input type='hidden' name='id' value='" . $entry->id . "'>" .
                "<input type='hidden' name='action' value='renew'>" .
                "<input type='submit' value='" . get_string('new_entry', 'availability_examus') . "'></form>";
        } else {
            $row[] = "-";
        }
        $table->add_data($row);
    }
    $table->print_html();
}


echo $OUTPUT->footer();
