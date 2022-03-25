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

    /** @var array List of (de-)serializable properties */
    const PROPS = [
        'duration', 'mode', 'schedulingrequired', 'autorescheduling',
        'istrial', 'rules', 'identification', 'noprotection',
        'useragreementurl', 'auxiliarycamera', 'customrules',
        'groups',
    ];

    const WARNINGS = [
        'warning_extra_user_in_frame' => true,
        'warning_substitution_user' => true,
        'warning_no_user_in_frame' => true,
        'warning_avert_eyes' => true,
        'warning_timeout' => true,
        'warning_change_active_window_on_computer' => true,
        'warning_talk' => true,
        'warning_forbidden_software' => true,
        'warning_forbidden_device' => true,
        'warning_voice_detected' => true,
        'warning_extra_display' => true,
        'warning_books' => true,
        'warning_cheater' => true,
        'warning_mic_muted' => true,
        'warning_mic_no_sound' => true,
        'warning_mic_no_device_connected' => true,
        'warning_camera_no_picture' => true,
        'warning_camera_no_device_connected' => true,
        'warning_nonverbal' => true,
        'warning_phone' => true,
        'warning_phone_screen' => true,
        'warning_no_ping' => true,
        'warning_desktop_request_pending' => true,
    ];

    const RULES = [
        'allow_to_use_websites' => false,
        'allow_to_use_books' => false,
        'allow_to_use_paper' => true,
        'allow_to_use_messengers' => false,
        'allow_to_use_calculator' => true,
        'allow_to_use_excel' => false,
        'allow_to_use_human_assistant' => false,
        'allow_absence_in_frame' => false,
        'allow_voices' => false,
        'allow_wrong_gaze_direction' => false,
    ];

    /** @var int Default exam duration */
    public $duration = 60;

    /** @var string Default exam mode */
    public $mode = 'normal';

    /** @var string Default calendar mode */
    public $schedulingrequired = true;

    /** @var bool Reschedule when exam was missed */
    public $autorescheduling = false;

    /** @var bool Is trial exam */
    public $istrial = false;

    /** @var array Default exam rules */
    public $rules = [];

    /** @var array Default exam rules */
    public $warnings = [];

    /** @var string identification method **/
    public $identification;

    /** @var bool No protection (shade) */
    public $noprotection = false;

    /** @var string User agreement URL */
    public $useragreementurl = null;

    /** @var string Auxiliary camera enabled */
    public $auxiliarycamera = false;

    /** @var string List of custom rules */
    public $customrules = null;

    /**
     * @var array Apply condition to specified groups
     */
    public $groups = [];

    private static $cached_trees = [];

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

        if (isset($structure->scheduling_required) && $structure->scheduling_required !== null) {
            $this->schedulingrequired = $structure->scheduling_required;
        } else {
            $manualmodes = ['normal', 'identification'];
            $this->schedulingrequired = in_array($this->mode, $manualmodes);
        }

        if (!empty($structure->auto_rescheduling)) {
            $this->autorescheduling = $structure->auto_rescheduling;
        }

        if (!empty($structure->warnings)) {
            $warnings = array_merge(self::WARNINGS, (array)$structure->warnings);
            $this->warnings = (object)$warnings;
        }else {
            $this->warnings = (object)self::WARNINGS;
        }
        if (!empty($structure->rules)) {
            $rules = array_merge(self::RULES, (array)$structure->rules);
            $this->rules = $structure->rules;
        }else {
            $this->rules = (object)self::RULES;
        }

        if (!empty($structure->customrules)) {
            $this->customrules = $structure->customrules;
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

        if (!empty($structure->useragreementurl)) {
            $this->useragreementurl = $structure->useragreementurl;
        }

        if (isset($structure->noprotection)) {
            $this->noprotection = $structure->noprotection;
        } else {
            $this->noprotection = false;
        }

        if (isset($structure->auxiliarycamera)) {
            $this->auxiliarycamera = $structure->auxiliarycamera;
        } else {
            $this->auxiliarycamera = false;
        }
        $this->validate();
    }

    public function validate(){
        $keys = array_keys(self::RULES);
        foreach($this->rules as $key => $value) {
            if(!in_array($key, $keys)) {
                unset($this->rules->{$key});
            } else {
                $this->rules->{$key} = (bool) $this->rules->{$key};
            }
        }

        $keys = array_keys(self::WARNINGS);
        foreach($this->warnings as $key => $value) {
            if(!in_array($key, $keys)) {
                unset($this->warnings->{$key});
            } else {
                $this->warnings->{$key} = (bool) $this->warnings->{$key};
            }
        }
    }

    /**
     * Import from external communication
     *
     * @return null
     */
    public function from_json($data){
        foreach ($this::PROPS as $prop) {
            if (in_array($prop, ['rules'])) {
                continue;
            }
            if (isset($data[$prop])) {
                $this->{$prop} = $data[$prop];
            }
        }

        if (isset($data['rules']) && is_array($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                $key = $rule['key'];
                $value = $rule['value'];
                $this->rules->{$key} = $value;
            }
        }
        $this->validate();
    }

    /**
     * Export for external communication
     *
     * @return Array of properties of current condition
     */
    public function to_json() {
        $result = [];
        foreach ($this::PROPS as $prop) {
            $result[$prop] = $this->{$prop};
        }

        if (!empty($result['rules'])) {
            $rules = [];
            foreach ($result['rules'] as $key => $value) {
                $rules[] = ['key' => $key, 'value' => $value];
            }
            $result['rules'] = $rules;
        }else{
            $result['rules'] = [];
        }

        return $result;
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
     * get examus conditions
     *
     * @param \cm_info $cm Cm
     * @return array
     */
    public static function get_examus_condition($cm) {
        $conds = self::get_examus_conditions($cm);
        return $conds && isset($conds[0]) ? $conds[0] : null;
    }

    /**
     * get examus conditions
     *
     * @param \cm_info $cm Cm
     * @return array
     */
    private static function get_examus_conditions($cm) {
        if($cm && isset(self::$cached_trees[$cm->id])) {
            return self::$cached_trees[$cm->id];
        }

        $info = new info_module($cm);
        try {
            $tree = $info->get_availability_tree();
            $tree = $tree->get_all_children('\\availability_examus\\condition');

            self::$cached_trees[$cm->id] = $tree;

        } catch (moodle_exception $e) {
            return null;
        }

        return $tree;
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
        $usergroups = $DB->get_records('groups_members', ['userid' => $user->id], null, 'groupid');
        return self::user_groups_intersect($cm, $usergroups);
    }

    /**
     * Check if condition is limiteted to groups, and at least one
     * usergroup intersects with them
     * There is possibility to make this method private and move it
     * to has_examus_condition, or maybe something else.
     *
     * @param \cm_info $cm Cm
     * @params array $usergroups Array of usergroups
     */
    public static function user_groups_intersect($cm, $usergroups){
        $selectedgroups = self::get_examus_groups($cm);

        if(empty($selectedgroups)){
            return true;
        }

        foreach ($usergroups as $usergroup) {
            if (in_array($usergroup->groupid, $selectedgroups)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Export for moodle storage
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
            'warnings' => (array) $this->warnings,
            'groups' => (array) $this->groups,
            'istrial' => (bool) $this->istrial,
            'identification' => $this->identification,
            'noprotection' => (bool) $this->noprotection,
            'useragreementurl' => $this->useragreementurl,
            'auxiliarycamera' => (bool) $this->auxiliarycamera,
            'customrules' => $this->customrules,
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

        if (!$info instanceof \core_availability\info_module) {
            return true;
        }

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
     * @param array $userentries Pre-collected list of user entries, indexed by cmid
     * @return stdClass
     */
    public function create_entry_for_cm($userid, $cm, $userentries = null) {
        global $DB;

        $courseid = $cm->course;

        if($userentries) {
            $entries = isset($userentries[$cm->id]) ? $userentries[$cm->id] : [];
        } else {
            $entries = $DB->get_records('availability_examus', [
                'userid' => $userid,
                'courseid' => $courseid,
                'cmid' => $cm->id,
            ], 'id');
        }

        foreach ($entries as $entry) {
            if ($entry->status == 'Not inited') {
                return $entry;
            }
        }

        foreach ($entries as $entry) {
            if ($this->autorescheduling) {
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

        if ($cm->modname == 'quiz') {
            $quiz = \quiz_access_manager::load_quiz_and_settings($cm->instance);
            $allowedattempts = $quiz->attempts;
            $allowedattempts = $allowedattempts > 0 ? $allowedattempts : null;
        } else {
            $allowedattempts = null;
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
