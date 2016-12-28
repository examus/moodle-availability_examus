<?php
global $CFG;
require_once($CFG->libdir . "/externallib.php");

class availability_examus_external extends external_api
{

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function user_proctored_modules_parameters()
    {
        return new external_function_parameters(
            array('useremail' => new external_value(PARAM_TEXT, 'User Email'))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function user_proctored_modules($useremail)
    {
        global $DB;

        self::validate_parameters(self::user_proctored_modules_parameters(),
            array('useremail' => $useremail));
        $_SESSION['examus_api'] = True;

        $user = $DB->get_record('user', array('email' => $useremail));
        $courses = enrol_get_users_courses($user->id, true);
        foreach ($courses as $course) {
            $course = get_course($course->id);

            // Clearing cache
            get_fast_modinfo($course->id, $user->id, true);
            $modinfo = get_fast_modinfo($course->id, $user->id);
            $instances_by_types = $modinfo->get_instances();
            foreach ($instances_by_types as $instances) {
                foreach ($instances as $cm) {
                    if (\availability_examus\condition::has_examus_condition($cm) and $cm->uservisible) {
                        \availability_examus\condition::create_entry_for_cm($user->id, $cm);
                        // TODO build answer array here
                    } else {
                        \availability_examus\condition::delete_empty_entry_for_cm($user->id, $cm);
                    }

                }
            }

        }

        $entries = $DB->get_records('availability_examus', array('userid' => $user->id, 'status' => 'Not inited'));

        $answer = array();
        foreach ($entries as $entry) {
            $course = get_course($entry->courseid);
            $modinfo = get_fast_modinfo($course);
            $cm = $modinfo->get_cm($entry->cmid);
            $url = new moodle_url(
                '/availability/condition/examus/entry.php',
                array('accesscode' => $entry->accesscode));
            $module_answer = array(
                'id' => $entry->id,
                'name' => $cm->get_formatted_name(),
                'url' => $url->out(),
                'course_name' => $course->fullname,
                'course_id' => $course->id,
                'cm_id' => $entry->cmid,
                'is_proctored' => True,
                'time_limit_mins' => $entry->duration,
                'accesscode' => $entry->accesscode,
            );

            if ($cm->get_module_type_name() == get_string('modulename', 'mod_quiz')) {
                $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
                $module_answer['start'] = $quiz->timeopen;
                $module_answer['end'] = $quiz->timeclose;
            }

            array_push($answer, $module_answer);
        }

        return array('modules' => $answer);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function user_proctored_modules_returns()
    {
        return new external_single_structure(
            array('modules' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'entry id'),
                        'name' => new external_value(PARAM_TEXT, 'module name'),
                        'url' => new external_value(PARAM_TEXT, 'module url'),
//                        'course_id' => new external_value(PARAM_TEXT, 'course id'),
//                        'cm_id' => new external_value(PARAM_TEXT, 'module id'),
                        'course_name' => new external_value(PARAM_TEXT, 'module course name', VALUE_OPTIONAL),
                        'time_limit_mins' => new external_value(PARAM_INT, 'module duration', VALUE_OPTIONAL),
                        'is_proctored' => new external_value(PARAM_BOOL, 'module proctored'),
                        'accesscode' => new external_value(PARAM_TEXT, 'module code'),
                        'start' => new external_value(PARAM_INT, 'module start', VALUE_OPTIONAL),
                        'end' => new external_value(PARAM_INT, 'module end', VALUE_OPTIONAL),
                    ), 'module')
            ),)
        );
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function submit_proctoring_review_parameters()
    {
        return new external_function_parameters(
            array('accesscode' => new external_value(PARAM_TEXT, 'Access Code'),
                'review_link' => new external_value(PARAM_TEXT, 'Link to review page'),
                'status' => new external_value(PARAM_TEXT, 'Status of review'))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function submit_proctoring_review($accesscode, $review_link, $status)
    {
        global $DB;

        self::validate_parameters(self::submit_proctoring_review_parameters(),
            array('accesscode' => $accesscode, 'review_link' => $review_link, 'status' => $status));

        $timenow = time();
        $entry = $DB->get_record('availability_examus', array('accesscode' => $accesscode));

        if ($entry) {
            $entry->review_link = $review_link;
            $entry->status = $status;
            $entry->timemodified = $timenow;

            $DB->update_record('availability_examus', $entry);
            return array('success' => True, 'error'=>null);
        }
        return array('success' => False, 'error'=>'Entry was not found');

    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function submit_proctoring_review_returns()
    {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'request success status'),
                'error' => new external_value(PARAM_TEXT, 'error message')
            )
        );
    }
}
