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
 * Finish attempt on attempt finish event.
 *
 * @param stdClass $event Event
 */
function examus_attempt_submitted_handler($event) {
    global $DB;

    $course = $DB->get_record('course', array('id' => $event->courseid));
    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
    $quiz = $event->get_record_snapshot('quiz', $attempt->quiz);
    $cm = get_coursemodule_from_id('quiz', $event->get_context()->instanceid, $event->courseid);

    $userid = $event->userid;
    $entries = $DB->get_records('availability_examus',
            array('userid' => $userid, 'courseid' => $event->courseid, 'cmid' => $cm->id, 'status' => "Started"), '-id');
    foreach ($entries as $entry) {
        $entry->status = "Finished";
        $DB->update_record('availability_examus', $entry);
    }

    unset($_SESSION['examus']);
}

/**
 * When attempt is started, update entry accordingly
 *
 * @param stdClass $event Event
 */
function examus_attempt_started_handler($event) {
    global $DB;

    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);

    $entry = $DB->get_record('availability_examus', ['status' => 'Started', 'accesscode' => $accesscode]);

    $entry->attemptid = $attempt->id;
    $DB->update_record('availability_examus', $entry);
}


/**
 * Remove entries on attempt deletion
 *
 * @param stdClass $event Event
 */
function examus_attempt_deleted_handler($event) {
    global $DB;

    $course = $DB->get_record('course', array('id' => $event->courseid));
    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
    $quiz = $event->get_record_snapshot('quiz', $attempt->quiz);
    $cm = get_coursemodule_from_id('quiz', $event->get_context()->instanceid, $event->courseid);

    $DB->delete_records('availability_examus', ['cmid' => $cm->id, 'attemptid' => $attempt->id]);
}

