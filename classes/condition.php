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
 * @copyright  2019-2020 Maksim Burnin <maksim.burnin@gmail.com>
 * @copyright  based on work by 2017 Max Pomazuev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_examus;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

use core_availability\info_module;
use moodle_exception;
use quiz;
use stdClass;
use availability_examus\state;

/**
 * Examus condition
 */
class condition extends \core_availability\condition {
    /**
     * @var integer Time for entry to expire
     */
    const EXPIRATION_SLACK = 15 * 60;

    /** @var int Default exam duration */
    protected $duration = 60;

    /** @var string Default exam mode */
    protected $mode = 'normal';

    /** @var string Default calendar mode */
    protected $schedulingrequired = true;

    /** @var bool Reschedule when exam was missed */
    protected $autorescheduling = false;

    /** @var bool Is trial exam */
    protected $istrial = false;

    /** @var array Default exam rules */
    protected $rules = [];

    /** @var string identification method **/
    protected $identification;

    /**
     * @var array Apply condition to specified groups
     */
    protected $groups = [];

    /**
     * Construct
     *
     * @param stdClass $structure Structure
     */
    public function __construct($structure) {
        if (!empty($structure->duration)) {
            $this->duration = $structure->duration;
        }
        if (!empty($structure->mode)) {
            $this->mode = $structure->mode;
        }

        if (!empty($structure->scheduling_required)) {
            $this->schedulingrequired = $structure->scheduling_required;
        } else {
            $manualmodes = ['normal', 'identification'];
            $this->schedulingrequired = in_array($this->mode, $manualmodes);
        }

        if (!empty($structure->auto_rescheduling)) {
            $this->autorescheduling = $structure->auto_rescheduling;
        }

        if (!empty($structure->rules)) {
            $this->rules = $structure->rules;
        }else {
            $this->rules = (object)[];
        }

        if (!empty($structure->customrules)) {
            $this->rules->custom_rules = $structure->customrules;
        }

        if (!empty($structure->groups)) {
            $this->groups = $structure->groups;
        }

        if (!empty($structure->identification)) {
            $this->identification = $structure->identification;
        }

        if (isset($structure->istrial)) {
            $this->istrial = $structure->istrial;
        } else {
            $this->istrial = false;
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
        return (bool) $econds[0]->schedulingrequired;
    }

    /**
     * get examus groups
     *
     * @param \cm_info $cm Cm
     * @return bool
     */
    public static function get_examus_groups($cm) {
        $econds = self::get_examus_conditions($cm);
        return (array) (isset($econds[0]->groups) ? $econds[0]->groups : []);
    }

    /**
     * get examus scheduling mode
     *
     * @param \cm_info $cm Cm
     * @return bool
     */
    public static function get_auto_rescheduling($cm) {
        $econds = self::get_examus_conditions($cm);
        return (bool) $econds[0]->autorescheduling;
    }

    /**
     * get identification mode
     *
     * @param \cm_info $cm Cm
     * @return string
     */
    public static function get_identification($cm) {
        $econds = self::get_examus_conditions($cm);
        return $econds[0]->identification;
    }

    /**
     * get is trial
     *
     * @param \cm_info $cm Cm
     * @return bool
     */
    public static function get_is_trial($cm) {
        $econds = self::get_examus_conditions($cm);
        return (bool) $econds[0]->istrial;
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
     * Check if condition is limiteted to groups, and is user is part
     * of these groups.
     * There is possibility to make this method private and move it
     * to has_examus_condition, or maybe something else.
     *
     * @param \cm_info $cm Cm
     * @params int $userid userid
     */
    public static function user_in_proctored_groups($cm, $userid) {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid]);

        $selectedgroups = self::get_examus_groups($cm);
        if (!empty($selectedgroups)) {
            $usergroups = $DB->get_records('groups_members', ['userid' => $user->id], null, 'groupid');
            foreach ($usergroups as $usergroup) {
                if (in_array($usergroup->groupid, $selectedgroups)) {
                    return true;
                }
            }
            return false;
        } else {
            return true;
        }
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
            'scheduling_required' => (bool) $this->schedulingrequired,
            'auto_rescheduling' => (bool) $this->autorescheduling,
            'rules' => (array) $this->rules,
            'groups' => (array) $this->groups,
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

        $course = $info->get_course();
        $cm = $info->get_course_module();

        $allow = self::is_available_internal($course->id, $cm->id, $userid);

        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * is available internal
     *
     * @param bool $not Not
     * @param int $userid User id
     * @return bool
     */
    public static function is_available_internal($courseid, $cmid, $userid) {
        global $DB, $SESSION;

        $allow = false;

        if (isset($SESSION->availibilityexamustoken)) {
            $accesscode = $SESSION->availibilityexamustoken;

            $entry = $DB->get_record('availability_examus', [
                'userid' => $userid,
                'courseid' => $courseid,
                'cmid' => $cmid,
                'accesscode' => $accesscode
            ]);

            if ($entry) {
                $allow = true;
            }
        }

        if (state::$apirequest) {
            $allow = true;
        }

        return $allow;

    }


    /**
     * get description
     *
     * @param string $full Full
     * @param \core_availability\info $info Info
     * @return string
     */
    public function get_description($full, $not, \core_availability\info $info) {
        return get_string('use_examus', 'availability_examus');
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


    /**
     * Initialize new entry, ready to write to DB
     * @param integer $courseid
     * @param integer $cmid
     * @param integer $userid
     * @return \stdClass entry
     */
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
     * @param integer $userid User id
     * @param integer $cm Cm id
     * @return stdClass
     */
    private static function create_entry_if_not_exist($userid, $cm) {
        global $DB;

        if ($cm->modname == 'quiz') {
            $quizobj = quiz::create($cm->instance, $userid);
            $allowedattempts = $quizobj->get_num_attempts_allowed();
            $allowedattempts = $allowedattempts > 0 ? $allowedattempts : null;
        } else {
            $allowedattempts = null;
        }

        $course = $cm->get_course();
        $courseid = $course->id;

        $autorescheduling = self::get_auto_rescheduling($cm);

        $entries = $DB->get_records('availability_examus', [
            'userid' => $userid,
            'courseid' => $courseid,
            'cmid' => $cm->id,
        ], 'id');

        foreach ($entries as $entry) {
            if ($entry->status == 'Not inited') {
                return $entry;
            }
        }

        foreach ($entries as $entry) {
            if ($autorescheduling) {
                // Was schduled and not completed.
                $scheduled = !$entry->attemptid && $entry->status == 'Scheduled';
                // Consider expired, giving 15 minutes slack.
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

        if ($allowedattempts == null || count($entries) < $allowedattempts) {
            $entry = self::make_entry($courseid, $cm->id, $userid);
            $entry->id = $DB->insert_record('availability_examus', $entry);
            return $entry;
        }

        return null;
    }

    /**
     * Get debug string
     * Implements abstract method `core_availability\condition::get_debug_string`
     *
     * @return string
     */
    protected function get_debug_string() {
        global $SESSION;
        return isset($SESSION->availibilityexamustoken) ? 'YES' : 'NO';
    }

}
