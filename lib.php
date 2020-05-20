<?php
use core_availability\info_module;
use availability_examus\condition;

function availability_examus_render_navbar_output(){
    global $PAGE;

    $context = context_system::instance();

    if(!has_capability('availability/examus:logaccess', $context)){
        return '';
    }

    $title = get_string('log_section', 'availability_examus');
    $url = new \moodle_url('/availability/condition/examus/index.php');
    $icon = new \pix_icon('i/log','');
    $node = navigation_node::create($title, $url, navigation_node::TYPE_CUSTOM, null, null, $icon);
    $PAGE->flatnav->add($node);

    return '';
}

function availability_examus_before_standard_html_head(){
    global $EXAMUS;
    global $DB;

    // No viewing quiz attempt
    if(empty($EXAMUS)){
        return;
    }

    if(isset($EXAMUS['attempt_data']['attempt_id'])){
        $attempt_id = $EXAMUS['attempt_data']['attempt_id'];
        $attempt = $DB->get_record('quiz_attempts', ['id' => $attempt_id]);
        if(!$attempt || $attempt->state != 'inprogress'){
            return;
        }
    }else{
        return;
    }

    // Not examused quiz
    $cm_id = $EXAMUS['attempt_data']['cm_id'];
    $course_id = $EXAMUS['attempt_data']['course_id'];

    $modinfo = get_fast_modinfo($course_id);
    $cm = $modinfo->get_cm($cm_id);

    $availibility_info = new info_module($cm);
    if(!condition::has_examus_condition($cm)) {
        return;
    }


    $origin = isset($_SESSION['examus_client_origin']) ? $_SESSION['examus_client_origin'] : '';

    ob_start();
    include(dirname(__FILE__).'/proctoring_fader.php');
    $output = ob_get_clean();

    return $output;
}
