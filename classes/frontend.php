<?php

namespace availability_examus;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend {

    protected function get_javascript_strings() {
        return ['title', 'error_setduration', 'duration', 'link', 'mode', 'normal_mode', 'olympics_mode', 'identification_mode'];
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null,
                                                  \section_info $section = null) {
        return array();
    }

    protected function allow_add($course, \cm_info $cm = null,
                                 \section_info $section = null) {
        return true;
    }
}