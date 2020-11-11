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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

use core_availability\info_module;
use availability_examus\condition;
use availability_examus\common;


/**
 * External API
 */
class availability_examus_external extends external_api {

    /**
     * Prepares entry data for outside world
     *
     * @param \stdClass $entry
     * @return array Entry data, ready for serialization
     */
    protected static function moduleanswer($entry) {
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

        // Ref
        switch ($cm->modname) {
            case 'quiz':
                try {
                    $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
                    $moduleanswer['start'] = $quiz->timeopen;
                    $moduleanswer['end'] = $quiz->timeclose;
                } catch (\dml_missing_record_exception $ex) {}
                break;
            case 'assign':
                try {
                    $assign = $DB->get_record('assign', ['id' => $cm->instance]);
                    $moduleanswer['start'] = $assign->allowsubmissionsfromdate;
                    $moduleanswer['end'] = $assign->duedate;
                } catch (\dml_missing_record_exception $ex) {}
                break;
        }

        $moduleanswer['status'] = $entry->status;

        return $moduleanswer;
    }

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

    /**
     * Returns list of entries based on provided criteria
     *
     * @param string|null $useremail Useremail
     * @param string|null $accesscode Accesscode
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

        } else if ($useremail) {

            $_SESSION['examus_api'] = true;

            $user = $DB->get_record('user', ['email' => $useremail]);
            $courses = enrol_get_users_courses($user->id, true);

            foreach ($courses as $course) {
                $course = get_course($course->id);

                // Clearing cache.
                get_fast_modinfo($course->id, $user->id, true);
                $modinfo = get_fast_modinfo($course->id, $user->id);
                $instancesbytypes = $modinfo->get_instances();
                foreach ($instancesbytypes as $instances) {
                    foreach ($instances as $cm) {
                        $availibilityinfo = new info_module($cm);

                        if (condition::has_examus_condition($cm)) {
                            $reason = '';

                            if (!$cm->uservisible || !$availibilityinfo->is_available($reason, false, $user->id)) {
                                continue;
                            }

                            if(!condition::user_in_proctored_groups($cm, $user->id)){
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

            // Shows all modules.

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
                    'auto_rescheduling' => new external_value(PARAM_BOOL, 'allow rescheduling'),
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
                        'allow_wrong_gaze_direction' => new external_value(PARAM_BOOL, 'proctoring rule', VALUE_OPTIONAL),
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
            'timescheduled' => new external_value(PARAM_INT, 'Time scheduled', VALUE_DEFAULT, 0),
            'comment' => new external_value(PARAM_TEXT, 'Review comment', VALUE_DEFAULT, null),
            'score' => new external_value(PARAM_INT, 'Scoring value', VALUE_DEFAULT, null),
            'threshold' => new external_single_structure([
                'attention' => new external_value(PARAM_INT, 'Attention threshold', VALUE_OPTIONAL),
                'rejected' => new external_value(PARAM_INT, 'Rejected threshold', VALUE_OPTIONAL),
            ], 'Thresholds', VALUE_DEFAULT, ['attention' => null, 'rejected' => null]),
            'session_start' => new external_value(PARAM_INT, 'Session start time', VALUE_DEFAULT, null),
            'session_end' => new external_value(PARAM_INT, 'Time scheduled', VALUE_DEFAULT, null),
            'warnings' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Warning', VALUE_OPTIONAL),
                'Warnings', VALUE_DEFAULT, []
            ),
            'warning_titles' => new external_value(PARAM_TEXT, 'Warnings Titles JSON', VALUE_DEFAULT, null)
        ]);
    }

    /**
     * Stores entry review results
     *
     * @param string $accesscode accesscode
     * @param string $status status
     * @param string $reviewlink reviewlink
     * @param string $timescheduled timescheduled
     * @param string $comment Proctoring comment
     * @param float $score Proctoring score
     * @param float $threshold Proctoring score threshold
     * @param integer $sessionstart Quiz/Proctoring start time
     * @param integer $sessionend  Quiz/Proctoring end time
     * @param array $warnings Warnings
     * @return array
     */
    public static function submit_proctoring_review(
        $accesscode,
        $status,
        $reviewlink,
        $timescheduled,
        $comment,
        $score,
        $threshold,
        $sessionstart,
        $sessionend,
        $warnings,
        $warningtitles
    ) {
        global $DB;

        self::validate_parameters(self::submit_proctoring_review_parameters(), [
            'accesscode' => $accesscode,
            'review_link' => $reviewlink,
            'status' => $status,
            'timescheduled' => $timescheduled,
            'comment' => $comment,
            'score' => $score,
            'threshold' => $threshold,
            'session_start' => $sessionstart,
            'session_end' => $sessionend,
            'warnings' => $warnings,
            'warning_titles' => $warningtitles
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

            $entry->comment = $comment;
            $entry->score = $score;
            $entry->threshold = json_encode($threshold);
            $entry->session_start = $sessionstart;
            $entry->session_end = $sessionend;
            $entry->warnings = json_encode($warnings);
            $entry->warning_titles = !empty($warningtitles) ? json_encode(@json_decode($warningtitles)) : null;

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
        self::validate_parameters(self::reset_entry_parameters(), [
            'accesscode' => $accesscode,
        ]);

        $result = common::reset_entry(['accesscode' => $accesscode]);

        if ($result) {
            return ['success' => true, 'error' => null];
        } else {
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

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_picture_parameters() {
        return new external_function_parameters([
            'useremail' => new external_value(PARAM_TEXT, 'User Email', VALUE_DEFAULT, ""),
        ]);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function user_picture_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'request success status'),
            'userpicture' => new external_value(PARAM_TEXT, 'user pic url', VALUE_OPTIONAL),
            'error' => new external_value(PARAM_TEXT, 'error message', VALUE_OPTIONAL)
        ]);
    }

    /**
     * Returns user picture for user by email
     *
     * @param string $accesscode accesscode
     * @return array
     */
    public static function user_picture($useremail) {
        global $DB, $PAGE;

        self::validate_parameters(self::user_picture_parameters(), [
            'useremail' => $useremail,
        ]);

        $user = $DB->get_record('user', ['email' => $useremail]);

        if(!$user) {
            return ['success' => false, 'error' => 'User was not found'];
        }

        $userpictureurl = null;
        if($user && $user->picture){
            $userpicture = new user_picture($user);
            $userpicture->size = 200; // Size f3.
            $userpictureurl = $userpicture->get_url($PAGE)->out(false);
            $validuntill = time()+(60*60);

            if($userpictureurl){
                $key = get_user_key('core_files', $user->id, null, null, $validuntill);
                $userpictureurl = str_replace('/pluginfile.php/', '/tokenpluginfile.php/'.$key.'/', $userpictureurl);
                return ['success' => true, 'userpicture' => $userpictureurl];
            } else {
                return ['success' => false, 'error' => 'User has no image'];
            }
        } else {
            return ['success' => false, 'error' => 'User has no image'];
        }
    }
}
