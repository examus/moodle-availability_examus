<?php
require_once('../../../config.php');

require_login();

global $DB;

$accesscode = required_param('accesscode', PARAM_RAW);
$entry = $DB->get_record('availability_examus', array('accesscode' => $accesscode));

if ($entry) {
    $entry->status = 'Started';
    $entry->timemodified = time();
    $DB->update_record('availability_examus', $entry);
    $cmid = $entry->cmid;

    $_SESSION['examus'] = True;

    list($course, $cm) = get_course_and_cm_from_cmid($cmid);

    redirect($cm->url->out(false));
}

die;