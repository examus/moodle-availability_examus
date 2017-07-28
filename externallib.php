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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/externallib.php");

class availability_examus_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_proctored_modules_parameters() {
        return new external_function_parameters(
                array(
                        'useremail' => new external_value(PARAM_TEXT, 'User Email'),
                        'accesscode' => new external_value(PARAM_TEXT, 'Access Code', VALUE_DEFAULT, ""),
                )
        );
    }

    /**
     * Returns welcome message
     *
     * @return array
     */
    public static function user_proctored_modules($useremail, $accesscode) {
        global $DB;

        self::validate_parameters(self::user_proctored_modules_parameters(),
                array('useremail' => $useremail, 'accesscode' => $accesscode));

        if ($accesscode) {
            $entries = $DB->get_records(
                    'availability_examus',
                    array('accesscode' => $accesscode));

            if (count($entries) == 0) {
                return array('modules' => array());
            }
            foreach ($entries as $entry) {

                $course = get_course($entry->courseid);
                $modinfo = get_fast_modinfo($course);
                $cm = $modinfo->get_cm($entry->cmid);

                return array('modules' => array(
                        array(
                                'id' => $entry->id,
                                'name' => $cm->get_formatted_name(),
                                'url' => $entry->url,
                                'course_name' => $course->fullname,
                                'course_id' => $course->id,
                                'cm_id' => $entry->cmid,
                                'status' => $entry->status,
                                'is_proctored' => true,
                                'time_limit_mins' => \availability_examus\condition::get_examus_duration($cm),
                                'mode' => \availability_examus\condition::get_examus_mode($cm),
                                'accesscode' => $entry->accesscode,
                        )
                ));
            }

        }

        $_SESSION['examus_api'] = true;

        $user = $DB->get_record('user', array('email' => $useremail));
        $courses = enrol_get_users_courses($user->id, true);

        $answer = array();
        foreach ($courses as $course) {
            $course = get_course($course->id);

            // Clearing cache.
            get_fast_modinfo($course->id, $user->id, true);
            $modinfo = get_fast_modinfo($course->id, $user->id);
            $instancesbytypes = $modinfo->get_instances();
            foreach ($instancesbytypes as $instances) {
                foreach ($instances as $cm) {
                    if (\availability_examus\condition::has_examus_condition($cm) and $cm->uservisible) {
                        $entry = \availability_examus\condition::create_entry_for_cm($user->id, $cm);
                        // TODO build answer array here.
                        if ($entry == null) {
                            continue;
                        }
                        $url = new moodle_url(
                                '/availability/condition/examus/entry.php',
                                array('accesscode' => $entry->accesscode));
                        $moduleanswer = array(
                                'id' => $entry->id,
                                'name' => $cm->get_formatted_name(),
                                'url' => $url->out(),
                                'course_name' => $course->fullname,
                                'course_id' => $course->id,
                                'cm_id' => $entry->cmid,
                                'status' => $entry->status,
                                'is_proctored' => true,
                                'time_limit_mins' => \availability_examus\condition::get_examus_duration($cm),
                                'mode' => \availability_examus\condition::get_examus_mode($cm),
                                'accesscode' => $entry->accesscode,
                        );

                        if ($cm->modname == "quiz") {
                            $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
                            $moduleanswer['start'] = $quiz->timeopen;
                            $moduleanswer['end'] = $quiz->timeclose;
                        }

                        array_push($answer, $moduleanswer);

                    } else {
                        \availability_examus\condition::delete_empty_entry_for_cm($user->id, $cm);
                    }

                }
            }

        }

        return array('modules' => $answer);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function user_proctored_modules_returns() {
        return new external_single_structure(
                array('modules' => new external_multiple_structure(
                        new external_single_structure(
                                array(
                                        'id' => new external_value(PARAM_INT, 'entry id'),
                                        'name' => new external_value(PARAM_TEXT, 'module name'),
                                        'url' => new external_value(PARAM_TEXT, 'module url'),
                                        // 'course_id' => new external_value(PARAM_TEXT, 'course id'),
                                        // 'cm_id' => new external_value(PARAM_TEXT, 'module id'),
                                        'status' => new external_value(PARAM_TEXT, 'status'),
                                        'course_name' => new external_value(PARAM_TEXT, 'module course name', VALUE_OPTIONAL),
                                        'time_limit_mins' => new external_value(PARAM_INT, 'module duration', VALUE_OPTIONAL),
                                        'mode' => new external_value(PARAM_TEXT, 'module proctoring mode', VALUE_OPTIONAL),
                                        'is_proctored' => new external_value(PARAM_BOOL, 'module proctored'),
                                        'accesscode' => new external_value(PARAM_TEXT, 'module code'),
                                        'start' => new external_value(PARAM_INT, 'module start', VALUE_OPTIONAL),
                                        'end' => new external_value(PARAM_INT, 'module end', VALUE_OPTIONAL),
                                ), 'module')
                ), )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_proctoring_review_parameters() {
        return new external_function_parameters(
                array('accesscode' => new external_value(PARAM_TEXT, 'Access Code'),
                        'status' => new external_value(PARAM_TEXT, 'Status of review'),
                        'review_link' => new external_value(PARAM_TEXT, 'Link to review page', VALUE_DEFAULT, ""),
                        'timescheduled' => new external_value(PARAM_INT, 'Time scheduled', VALUE_DEFAULT, 0)
                )
        );
    }

    /**
     * Returns welcome message
     *
     * @param $accesscode
     * @param $status
     * @param $reviewlink
     * @param $timescheduled
     * @return array
     */
    public static function submit_proctoring_review($accesscode, $status, $reviewlink, $timescheduled) {
        global $DB;

        self::validate_parameters(self::submit_proctoring_review_parameters(), array(
                'accesscode' => $accesscode,
                'review_link' => $reviewlink,
                'status' => $status,
                'timescheduled' => $timescheduled));

        $timenow = time();
        $entry = $DB->get_record('availability_examus', array('accesscode' => $accesscode));

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
            return array('success' => true, 'error' => null);
        }
        return array('success' => false, 'error' => 'Entry was not found');

    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function submit_proctoring_review_returns() {
        return new external_single_structure(
                array(
                        'success' => new external_value(PARAM_BOOL, 'request success status'),
                        'error' => new external_value(PARAM_TEXT, 'error message')
                )
        );
    }
}
