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

require_once('../../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

require_login();

admin_externalpage_setup('availability_examus_settings');

$action = optional_param('action', '', PARAM_ALPHA);

switch ($action) {
    case 'renew':
        $id = required_param('id', PARAM_TEXT);
        $oldentry = $DB->get_record('availability_examus', array('id' => $id));

        if ($oldentry and $oldentry->status != 'Not inited') {
            $entries = $DB->get_records('availability_examus', array(
                'userid' => $oldentry->userid,
                'courseid' => $oldentry->courseid,
                'cmid' => $oldentry->cmid,
                'status' => 'Not inited'));
            if (count($entries) == 0) {
                $timenow = time();
                $entry = new stdClass();
                $entry->userid = $oldentry->userid;
                $entry->courseid = $oldentry->courseid;
                $entry->cmid = $oldentry->cmid;
                $entry->accesscode = md5(uniqid(rand(), 1));
                $entry->status = 'Not inited';
                $entry->timecreated = $timenow;
                $entry->timemodified = $timenow;
                $DB->insert_record('availability_examus', $entry);
                redirect('index.php', get_string('new_entry_created', 'availability_examus'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                redirect('index.php', get_string('entry_exist', 'availability_examus'),
                    null, \core\output\notification::NOTIFY_ERROR);
            }
        }
        break;
    default:
        break;
}


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'availability_examus'));

$entries = $DB->get_records('availability_examus', array(), '-id');

if (!empty($entries)) {
    $table = new flexible_table('availability_examus_table');

    $table->define_columns(array('date', 'time_scheduled', 'user', 'course', 'module', 'status', 'review_link', 'create_entry'));
    $table->define_headers(array(
        get_string('date_modified', 'availability_examus'),
        get_string('time_scheduled', 'availability_examus'),
        get_string('user'),
        get_string('course'),
        get_string('module', 'availability_examus'),
        get_string('status', 'availability_examus'),
        get_string('review', 'availability_examus'),
        ''));

    $table->define_baseurl($PAGE->url);
    $table->set_attribute('id', 'entries');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->setup();

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

        $user = $DB->get_record('user', array('id' => $entry->userid));
        $row[] = $user->firstname . " " . $user->lastname . "<br>" . $user->email;

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
