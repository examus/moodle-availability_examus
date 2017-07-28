<?php
namespace availability_examus;

defined('MOODLE_INTERNAL') || die();

use core_availability\info_module;
use moodle_exception;
use stdClass;

class condition extends \core_availability\condition
{

    protected $duration = 60;
    protected $mode = 'normal';

    public function __construct($structure) {
        if (!empty($structure->duration)) {
            $this->duration = $structure->duration;
        }
        if (!empty($structure->mode)) {
            $this->mode = $structure->mode;
        }
    }

    private static function delete_empty_entry($userid, $courseid, $cmid) {
        global $DB;
        $DB->delete_records('availability_examus', array(
            'userid' => $userid, 'courseid' => $courseid, 'cmid' => $cmid, 'status' => 'Not inited'));
    }

    public static function has_examus_condition($cm)
    {
        $econds = self::get_examus_conditions($cm);
        return (bool)$econds;
    }

    public static function get_examus_duration($cm)
    {
        $econds = self::get_examus_conditions($cm);
        // TODO: restrict examus condition to be only one.
        return (int)$econds[0]->duration;
    }

    public static function get_examus_mode($cm)
    {
        $econds = self::get_examus_conditions($cm);
        // TODO: restrict examus condition to be only one.
        return (string)$econds[0]->mode;
    }

    private static function get_examus_conditions($cm)
    {
        $info = new info_module($cm);
        try {
            $tree = $info->get_availability_tree();
        } catch (moodle_exception $e) {
            return null;
        }
        return $tree->get_all_children('\\availability_examus\\condition');
    }

    public function save()
    {
        return (object)['duration' => (int)$this->duration, 'mode' => (string)$this->mode];
    }

    public function is_available($not,
                                 \core_availability\info $info, $grabthelot, $userid)
    {
        global $DB;


        $allow = False;

        if (isset($_SESSION['examus'])) {
            $course = $info->get_course();
            $cm = $info->get_course_module();
            $accesscode = $_SESSION['examus'];

            $entry = $DB->get_record(
                'availability_examus',
                array('userid' => $userid, 'courseid' => $course->id, 'cmid' => $cm->id, 'accesscode' => $accesscode));

            if ($entry) {
                $allow = True;
            }
        }

        if (isset($_SESSION['examus_api'])) {
            // Call from api function
            $allow = True;
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

    public static function create_entry_for_cm($userid, $cm)
    {
        $course = $cm->get_course();
        $courseid = $course->id;
        return self::create_entry_if_not_exist($userid, $courseid, $cm->id);
    }

    public static function delete_empty_entry_for_cm($userid, $cm)
    {
        $course = $cm->get_course();
        $courseid = $course->id;
        self::delete_empty_entry($userid, $courseid, $cm->id);
    }

    private static function create_entry_if_not_exist($userid, $courseid, $cmid)
    {
        // TODO: refactor this to get courseid and duration from cm
        global $DB;
        $entries = $DB->get_records(
            'availability_examus',
            array('userid' => $userid, 'courseid' => $courseid, 'cmid' => $cmid),
            $sort = 'id');


        if (count($entries) == 0) {
            $timenow = time();
            $entry = new stdClass();
            $entry->userid = $userid;
            $entry->courseid = $courseid;
            $entry->cmid = $cmid;
            $entry->accesscode = md5(uniqid(rand(), 1));
            $entry->status = 'Not inited';
            $entry->timecreated = $timenow;
            $entry->timemodified = $timenow;
            $DB->insert_record('availability_examus', $entry);
            return $entry;
        } else {
            foreach ($entries as $entry) {
                if ($entry->status == 'Not inited') {
                    return $entry;
                }
            }
        }
        return null;
    }


    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event)
    {
        $cmid = $event->contextinstanceid;
        $course = get_course($event->courseid);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($cmid);
        $userid = $event->relateduserid;

        if (self::has_examus_condition($cm)) {
            self::delete_empty_entry($userid, $event->courseid, $cmid);
        }
    }

}