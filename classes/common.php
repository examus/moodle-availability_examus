<?php
namespace availability_examus;
use \stdClass;
defined('MOODLE_INTERNAL') || die();

class common {
    public static function reset_entry($conditions){
        global $DB;

        $oldentry = $DB->get_record('availability_examus', $conditions);
        if ($oldentry and $oldentry->status != 'Not inited') {
            $entries = $DB->get_records('availability_examus', [
                'userid' => $oldentry->userid,
                'courseid' => $oldentry->courseid,
                'cmid' => $oldentry->cmid,
                'status' => 'Not inited'
            ]);

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

                $entry->id = $DB->insert_record('availability_examus', $entry);

                return $entry;
            } else {
                return false;
            }
        }
    }

    public static function delete_empty_entries($userid, $courseid, $cmid = null){
        global $DB;

        $condition = [
          'userid' => $userid,
          'courseid' => $courseid,
          'status' => 'Not inited'
        ];

        if(!empty($cmid)) {
            $condition['cmid'] = $cmid;
        }

        $DB->delete_records('availability_examus', $condition);
    }


}
