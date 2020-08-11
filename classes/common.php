<?php
namespace availability_examus;
use \stdClass;
defined('MOODLE_INTERNAL') || die();

class common {
    /**
     * Name of the table used by the plugin.
     */
    public const TABLE = 'availability_examus';

    /**
     * Get entries from database.
     */
    private static function db_get_entries($where) {
        global $DB;
        return $DB->get_records(self::TABLE, $where);
    }

    /**
     * Create new entry with given `userid`, `courseid`, and `cmid`.
     */
    private static function create_new_entry($props) {
        $timenow = time();
        $entry = new stdClass();

        $entry->userid = $props.userid;
        $entry->courseid = $props.courseid;
        $entry->cmid = $props.cmid;
        $entry->accesscode = md5(uniqid(rand(), 1));
        $entry->status = 'Not inited';
        $entry->timecreated = $timenow;
        $entry->timemodified = $timenow;

        $entry->id = $DB->insert_record(self::$table, $entry);

        return $entry;
    }

    /**
     * Reset entry even if there are some not inited entries.
     */
    private static function reset_entry_forcefully($props) {
        $entries = self::db_get_entries(['status' => 'Not inited']);

        foreach ($entries as $x) {
            global $DB;
            $x->status = "Force reset";
            $DB->update_record(self::TABLE, $x);
        };

        return self::create_new_entry($props);
    }

    /**
     * Reset entry only when there are no not inited entries.
     */
    private static function reset_entry_gently($props) {
        $some_not_inited = count(self::db_get_entries(['status' => 'Not inited']));

        if ($some_not_inited) { return false; }
        else { return self::create_new_entry($props); }
    }

    public static function reset_entry($props, $force = false){
        $f = $force ? self::reset_entry_forcefully : self::reset_entry_gently;

        return $f($props);
    }

    public static function delete_empty_entries($userid, $courseid, $cmid) {
        global $DB;

        $condition = [
            'userid' => $userid,
            'courseid' => $courseid,
            'status' => 'Not inited'
        ];

        if (!empty($cmid)) { $condition['cmid'] = $cmid; }

        $DB->delete_records(self::TABLE, $condition);
    }


}
