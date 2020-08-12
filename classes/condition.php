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

namespace availability_examus;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

use core_availability\info_module;
use moodle_exception;
use quiz;
use stdClass;

class condition extends \core_availability\condition {
    const EXPIRATION_SLACK = 15*60;

    /** @var int Default exam duration */
    protected $duration = 60;

    /** @var string Default exam mode */
    protected $mode = 'normal';

    /** @var string Default calendar mode */
    protected $scheduling_required = true;

    /** @var bool Reschedule when exam was missed */
    protected $auto_rescheduling = false;

    /** @var array Default exam rules */
    protected $rules = [];

    /**
     * Construct
     *
     * @param stdClass $structure Structure
     */
    public function __construct($structure) {
        $manual_modes = ['normal', 'identification'];

        if(!empty($structure->duration)) {
            $this->duration = $structure->duration;
        }
        if(!empty($structure->mode)) {
            $this->mode = $structure->mode;
        }

        $this->scheduling_required = in_array($this->mode, $manual_modes);

        if(!empty($structure->auto_rescheduling)){
            $this->auto_rescheduling = $structure->auto_rescheduling;
        }

        if (!empty($structure->rules)) {
            $this->rules = $structure->rules;
        }
    }

    /**
     * Delete empty entry
     *
     * @param int $userid User id
     * @param int $courseid Course id
     * @param int $cmid Cm id
     */
    private static function delete_empty_entry($userid, $courseid, $cmid) {
        common::delete_empty_entries($userid, $courseid, $cmid);
    }

    /**
     * delete empty entry for cm
     *
     * @param int $userid User id
     * @param stdClass $cm Cm
     * @return bool
     */
    public static function delete_empty_entry_for_cm($userid, $cm) {
        $course = $cm->get_course();
        $courseid = $course->id;
        self::delete_empty_entry($userid, $courseid, $cm->id);
    }


    /**
     * has examus condition
     *
     * @param \cm_info $cm Cm
     * @return bool
     */
    public static function has_examus_condition($cm) {
        $econds = self::get_examus_conditions($cm);
        return (bool) $econds;
    }

    /**
     * get examus duration
     *
     * @param \cm_info $cm Cm
     * @return int
     */
    public static function get_examus_duration($cm) {
        $econds = self::get_examus_conditions($cm);
        return (int) $econds[0]->duration;
    }

    /**
     * get examus mode
     *
     * @param \cm_info $cm Cm
     * @return string
     */
    public static function get_examus_mode($cm) {
        $econds = self::get_examus_conditions($cm);
        return (string) $econds[0]->mode;
    }

    /**
     * get examus rules
     *
     * @param \cm_info $cm Cm
     * @return array
     */
    public static function get_examus_rules($cm) {
        $econds = self::get_examus_conditions($cm);
        return (array) $econds[0]->rules;
    }

    /**
     * get examus scheduling mode
     *
     * @param \cm_info $cm Cm
     * @return bool
     */
    public static function get_examus_scheduling($cm) {
        $econds = self::get_examus_conditions($cm);
        return (bool) $econds[0]->scheduling_required;

    }

    /**
     * get examus scheduling mode
     *
     * @param \cm_info $cm Cm
     * @return bool
     */
    public static function get_auto_rescheduling($cm) {
        $econds = self::get_examus_conditions($cm);
        return (bool) $econds[0]->auto_rescheduling;

    }

    /**
     * get examus conditions
     *
     * @param \cm_info $cm Cm
     * @return array
     */
    private static function get_examus_conditions($cm) {
        $info = new info_module($cm);
        try {
            $tree = $info->get_availability_tree();
        } catch (moodle_exception $e) {
            return null;
        }
        return $tree->get_all_children('\\availability_examus\\condition');
    }

    /**
     * save
     *
     * @return object
     */
    public function save() {
        return (object) [
            'type' => 'examus',
            'duration' => (int) $this->duration,
            'mode' => (string) $this->mode,
            'scheduling_required' => (bool) $this->scheduling_required,
            'auto_rescheduling' => (bool) $this->auto_rescheduling,
            'rules' => (array) $this->rules
        ];
    }

    /**
     * is available
     *
     * @param bool $not Not
     * @param \core_availability\info $info Info
     * @param string $grabthelot grabthelot
     * @param int $userid User id
     * @return bool
     */
    public function is_available($not,
            \core_availability\info $info, $grabthelot, $userid) {
        global $DB;

        $allow = false;

        if (isset($_SESSION['examus'])) {
            $course = $info->get_course();
            $cm = $info->get_course_module();
            $accesscode = $_SESSION['examus'];

            $entry = $DB->get_record('availability_examus', [
                'userid' => $userid,
                'courseid' => $course->id,
                'cmid' => $cm->id,
                'accesscode' => $accesscode
            ]);

            if ($entry) {
                $allow = true;
            }
        }

        if (isset($_SESSION['examus_api'])) {
            // Call from api function.
            $allow = true;
        }

        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * get description
     *
     * @param string $full Full
     * @param bool $not Not
     * @param \core_availability\info $info Info
     * @return string
     */
    public function get_description($full, $not, \core_availability\info $info) {
        return get_string('use_examus', 'availability_examus');
    }

    /**
     * get debug string
     *
     * @return string
     */
    protected function get_debug_string() {
        return in_array('examus', $_SESSION) ? 'YES' : 'NO';
    }

    /**
     * create entry for cm
     *
     * @param int $userid User id
     * @param stdClass $cm Cm
     * @return stdClass
     */
    public static function create_entry_for_cm($userid, $cm) {
        return self::create_entry_if_not_exist($userid, $cm);
    }


    public static function make_entry($courseid, $cmid, $userid=null) {
        $timenow = time();
        $entry = new stdClass();
        $entry->courseid = $courseid;
        $entry->cmid = $cmid;
        $entry->accesscode = is_null($userid) ? '' : md5(uniqid(rand(), 1));
        $entry->status = is_null($userid) ? null : 'Not inited';
        $entry->timecreated = $timenow;
        $entry->timemodified = $timenow;
        $entry->userid = $userid;

        return $entry;
    }

    /**
     * create entry if not exist
     *
     * @param int $userid User id
     * @param int $courseid Course id
     * @param int $cmid Cm id
     * @return stdClass
     */
    private static function create_entry_if_not_exist($userid, $cm) {
        global $DB;

        $quizobj = quiz::create($cm->instance, $userid);
        $allowed_attempts = $quizobj->get_num_attempts_allowed();
        $allowed_attempts = $allowed_attempts > 0 ? $allowed_attempts : NULL;

        $course = $cm->get_course();
        $courseid = $course->id;

        $auto_rescheduling = condition::get_auto_rescheduling($cm);

        $entries = $DB->get_records('availability_examus', [
            'userid' => $userid,
            'courseid' => $courseid,
            'cmid' => $cm->id,
        ], $sort = 'id');

        foreach ($entries as $entry) {
            if ($entry->status == 'Not inited') {
                return $entry;
            }
        }

        foreach ($entries as $entry) {
            if($auto_rescheduling){
                // Was schduled and not completed
                $scheduled = !$entry->attemptid && $entry->status == 'Scheduled';
                // Consider expired, giving 15 minutes slack
                $expired = time() > $entry->timescheduled + self::EXPIRATION_SLACK;

                if ($scheduled && $expired) {
                    $entry->timemodified = time();
                    $entry->status = 'Rescheduled';

                    $DB->update_record('availability_examus', $entry);
                    $entry = common::reset_entry(['id' => $entry->id]);
                    return $entry;
                }

            }
        }

        if ($allowed_attempts == NULL || count($entries) < $allowed_attempts) {
            $entry = self::make_entry($courseid, $cm->id, $userid);
            $entry->id = $DB->insert_record('availability_examus', $entry);
            return $entry;
        }

        return null;
    }

}
