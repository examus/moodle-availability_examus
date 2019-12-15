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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

use core_availability\info_module;
use availability_examus\condition;
use availability_examus\common;

/**
 * Availability examus class
 * @copyright  2017 Max Pomazuev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_examus_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_proctored_modules_parameters() {
        return new external_function_parameters([
            'useremail' => new external_value(PARAM_TEXT, 'User Email', VALUE_DEFAULT, ""),
            'accesscode' => new external_value(PARAM_TEXT, 'Access Code', VALUE_DEFAULT, ""),
        ]);
    }


    static function moduleanswer($entry) {
        global $DB;

        $course = get_course($entry->courseid);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($entry->cmid);

        $url = new moodle_url('/availability/condition/examus/entry.php', [
            'accesscode' => $entry->accesscode
        ]);

        $moduleanswer = [
            'id' => $entry->id,
            'name' => $cm->get_formatted_name(),
            'url' => $url->out(),
            'course_name' => $course->fullname,
            'course_id' => $course->id,
            'cm_id' => $entry->cmid,
            'is_proctored' => true,
            'time_limit_mins' => condition::get_examus_duration($cm),
            'mode' => condition::get_examus_mode($cm),
            'scheduling_required' => condition::get_examus_scheduling($cm),
            'auto_rescheduling' => condition::get_auto_rescheduling($cm),
            'accesscode' => $entry->accesscode,
        ];


        $rules = condition::get_examus_rules($cm);
        if ($rules) {
            $moduleanswer['rules'] = $rules;
        }

        if ($cm->modname == "quiz") {
            $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
            $moduleanswer['start'] = $quiz->timeopen;
            $moduleanswer['end'] = $quiz->timeclose;
        }

        $moduleanswer['status'] = $entry->status;



        return $moduleanswer;
    }
    /**
     * Returns welcome message
     *
     * @param string $useremail Useremail
     * @param string $accesscode Accesscode
     * @return array
     */
    public static function user_proctored_modules($useremail, $accesscode) {
        global $DB;

        $answer = [];

        self::validate_parameters(self::user_proctored_modules_parameters(), [
            'useremail' => $useremail,
            'accesscode' => $accesscode
        ]);

        if ($accesscode) {
            $entries = $DB->get_records('availability_examus', [
                'accesscode' => $accesscode
            ]);

            foreach ($entries as $entry) {
                array_push($answer, self::moduleanswer($entry));
            }

        } elseif ($useremail) {

            $_SESSION['examus_api'] = true;

            $user = $DB->get_record('user', array('email' => $useremail));
            $courses = enrol_get_users_courses($user->id, true);

            foreach ($courses as $course) {
                $course = get_course($course->id);

                // Clearing cache.
                get_fast_modinfo($course->id, $user->id, true);
                $modinfo = get_fast_modinfo($course->id, $user->id);
                $instancesbytypes = $modinfo->get_instances();
                foreach ($instancesbytypes as $instances) {
                    foreach ($instances as $cm) {
                        $availibility_info = new info_module($cm);

                        if (condition::has_examus_condition($cm)) {
                            $reason = '';
                            if(!$cm->uservisible || !$availibility_info->is_available($reason, false, $user->id)){
                                continue;
                            }


                            $entry = condition::create_entry_for_cm($user->id, $cm);
                            if ($entry == null) {
                                continue;
                            }

                            array_push($answer, self::moduleanswer($entry));

                        } else {
                            common::delete_empty_entries($user->id, $course->id, $cm->id);
                        }

                    }
                }
            }
        } else {

            // Shows all modules

            $courses = get_courses();
            foreach ($courses as $course) {
                $modinfo = get_fast_modinfo($course);
                $instancesbytypes = $modinfo->get_instances();
                foreach ($instancesbytypes as $instances) {
                    foreach ($instances as $cm) {
                        if (condition::has_examus_condition($cm)) {
                            $entry = condition::make_entry($course->id, $cm->id);
                            array_push($answer, self::moduleanswer($entry));
                        }
                    }
                }
            }
        }

        return ['modules' => $answer];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function user_proctored_modules_returns() {
        return new external_single_structure([
            'modules' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'entry id'),
                    'name' => new external_value(PARAM_TEXT, 'module name'),
                    'url' => new external_value(PARAM_TEXT, 'module url'),
                    'status' => new external_value(PARAM_TEXT, 'status'),
                    'course_name' => new external_value(PARAM_TEXT, 'module course name'),
                    'time_limit_mins' => new external_value(PARAM_INT, 'module duration', VALUE_OPTIONAL),
                    'mode' => new external_value(PARAM_TEXT, 'module proctoring mode'),
                    'scheduling_required' => new external_value(PARAM_BOOL, 'module calendar mode'),
                    'auto_rescheduling' =>  new external_value(PARAM_BOOL, 'allow rescheduling'),
                    'rules' => new external_single_structure([
                        'allow_to_use_websites' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_to_use_books' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_to_use_paper' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_to_use_messengers' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_to_use_calculator' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_to_use_excel' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_to_use_human_assistant' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_absence_in_frame' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_voices' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                        'allow_wrong_gaze_direction'=> new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
                    ], 'rules set', VALUE_OPTIONAL),
                    'is_proctored' => new external_value(PARAM_BOOL, 'module proctored'),
                    'accesscode' => new external_value(PARAM_TEXT, 'module code'),
                    'start' => new external_value(PARAM_INT, 'module start', VALUE_OPTIONAL),
                    'end' => new external_value(PARAM_INT, 'module end', VALUE_OPTIONAL),
                ], 'module')
            ),
        ]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_proctoring_review_parameters() {
        return new external_function_parameters([
            'accesscode' => new external_value(PARAM_TEXT, 'Access Code'),
            'status' => new external_value(PARAM_TEXT, 'Status of review'),
            'review_link' => new external_value(PARAM_TEXT, 'Link to review page', VALUE_DEFAULT, ""),
            'timescheduled' => new external_value(PARAM_INT, 'Time scheduled', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Returns welcome message
     *
     * @param string $accesscode accesscode
     * @param string $status status
     * @param string $reviewlink reviewlink
     * @param string $timescheduled timescheduled
     * @return array
     */
    public static function submit_proctoring_review($accesscode, $status, $reviewlink, $timescheduled) {
        global $DB;

        self::validate_parameters(self::submit_proctoring_review_parameters(), [
            'accesscode' => $accesscode,
            'review_link' => $reviewlink,
            'status' => $status,
            'timescheduled' => $timescheduled
        ]);

        $timenow = time();
        $entry = $DB->get_record('availability_examus', ['accesscode' => $accesscode]);

        if ($entry) {
            if ($reviewlink) {
                $entry->review_link = $reviewlink;
            }

            if ($timescheduled === -1) {
                $entry->timescheduled = null;
            } else if ($timescheduled) {
                $entry->timescheduled = $timescheduled;
            }

            $entry->status = $status;
            $entry->timemodified = $timenow;

            $DB->update_record('availability_examus', $entry);

            if (!$entry->attemptid && $status != 'Scheduled') {
                common::reset_entry(['accesscode' => $entry->accesscode]);
            }

            return ['success' => true, 'error' => null];
        }
        return ['success' => false, 'error' => 'Entry was not found'];

    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function submit_proctoring_review_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'request success status'),
            'error' => new external_value(PARAM_TEXT, 'error message')
        ]);
    }


    /**
     * Returns success flag and error message for reset operation
     *
     * @param string $accesscode accesscode
     * @return array
     */
    public static function reset_entry($accesscode) {
        global $DB;

        self::validate_parameters(self::reset_entry_parameters(), [
            'accesscode' => $accesscode,
        ]);

        $result = common::reset_entry(['accesscode' => $accesscode]);

        if ($result) {
            return ['success' => true, 'error' => null];
        }else{
            return ['success' => false, 'error' => 'Entry was not found'];
        }

    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function reset_entry_parameters() {
        return new external_function_parameters([
            'accesscode' => new external_value(PARAM_TEXT, 'Access Code'),
        ]);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function reset_entry_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'request success status'),
            'error' => new external_value(PARAM_TEXT, 'error message')
        ]);
    }
}
