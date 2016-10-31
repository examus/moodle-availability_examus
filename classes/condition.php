<?php

namespace availability_examus;

defined('MOODLE_INTERNAL') || die();
use stdClass;

class condition extends \core_availability\condition
{

    public function __construct($structure)
    {
    }

    public function save()
    {
    }

    public function is_available($not,
                                 \core_availability\info $info, $grabthelot, $userid)
    {
        if (in_array('examus', $_SESSION)) {
            $allow = True;
        } else {
            $allow = False;
        }

        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info)
    {
        return get_string('use_examus', 'availability_examus');
    }

    protected function get_debug_string()
    {
        return in_array('examus', $_SESSION) ? 'YES' : 'NO';
    }

    public static function course_module_deleted(\core\event\course_module_deleted $event)
    {
        global $DB;
        $cmid = $event->contextinstanceid;
        $DB->delete_records('availability_examus', array('cmid' => $cmid));
    }

    public static function course_module_updated(\core\event\course_module_updated $event)
    {
        global $DB;
        $cmid = $event->contextinstanceid;
        $course = get_course($event->courseid);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($cmid);

        if (strpos($cm->availability, '"c":[{"type":"examus"}]') !== false) {
            $users = get_enrolled_users($event->get_context());
            foreach ($users as $user) {
                $entry = $DB->get_record('availability_examus', array(
                    'userid' => $user->id, 'courseid' => $event->courseid, 'cmid' => $cmid));
                if (!$entry) {
                    $entry = new stdClass();
                    $entry->userid = $user->id;
                    $entry->courseid = $event->courseid;
                    $entry->cmid = $cmid;
                    $entry->accesscode = md5(uniqid(rand(), 1));
                    $entry->status = 'not_inited';
                    $DB->insert_record('availability_examus', $entry);
                }

            }
        }
    }

}