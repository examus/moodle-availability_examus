<?php
/**
 * Created by PhpStorm.
 * User: merlix
 * Date: 03/07/2017
 * Time: 10:41
 */

function examus_attempt_submitted_handler($event) {
    global $DB;

    $course  = $DB->get_record('course', array('id' => $event->courseid));
    $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
    $quiz    = $event->get_record_snapshot('quiz', $attempt->quiz);
    $cm      = get_coursemodule_from_id('quiz', $event->get_context()->instanceid, $event->courseid);

//    $cmid = $event->contextinstanceid;
//    $course = get_course($event->courseid);
//    $modinfo = get_fast_modinfo($course);
//    $cm = $modinfo->get_cm($cmid);
    $userid = $event->userid;
    $entries = $DB->get_records(
        'availability_examus',
        array('userid' => $userid, 'courseid' => $event->courseid, 'cmid' => $cm->id), '-id');
    foreach ($entries as $entry) {
        $entry->status = "Finished";
        $DB->update_record('availability_examus', $entry);
    }
}