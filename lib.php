<?php
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
