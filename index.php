<?php
require_once('../../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->libdir . '/adminlib.php');

require_login();

admin_externalpage_setup('availability_examus_settings');

$action = optional_param('action', '', PARAM_ALPHA);

switch ($action) {
    case 'renew':
        $id = required_param('id', PARAM_TEXT);
        $old_entry = $DB->get_record('availability_examus', array('id' => $id));
        if ($old_entry and $old_entry->status != 'Not inited') {
            $timenow = time();
            $entry = new stdClass();
            $entry->userid = $old_entry->userid;
            $entry->courseid = $old_entry->courseid;
            $entry->cmid = $old_entry->cmid;
            $entry->accesscode = md5(uniqid(rand(), 1));
            $entry->status = 'Not inited';
            $entry->timecreated = $timenow;
            $entry->timemodified = $timenow;
            $entry->duration = $old_entry->duration;
            $DB->insert_record('availability_examus', $entry);
        }
        redirect('index.php');
        break;
    default:
        break;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'availability_examus'));


$entries = $DB->get_records('availability_examus', array(), '-id');

if (!empty($entries)) {
    $table = new flexible_table('availability_examus_table');

    $table->define_columns(array('user', 'course', 'module', 'status', 'review_link', 'create_entry'));
    $table->define_headers(array(
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
        $user = $DB->get_record('user', array('id'=> $entry->userid));
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

        if ($entry->status != 'Not inited') {
            $row[] = "<a href='index.php?action=renew&id=" . $entry->id . "'>" . get_string('new_entry', 'availability_examus') . "</a>";
        } else {
            $row[] = "-";
        }
        $table->add_data($row);
    }
    $table->print_html();
}


echo $OUTPUT->footer();
