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

use core_availability\info_module;
use availability_examus\condition;

function availability_examus_render_navbar_output() {
    global $PAGE;

    $context = context_system::instance();

    if (!has_capability('availability/examus:logaccess', $context)) {
        return '';
    }

    $title = get_string('log_section', 'availability_examus');
    $url = new \moodle_url('/availability/condition/examus/index.php');
    $icon = new \pix_icon('i/log', '');
    $node = navigation_node::create($title, $url, navigation_node::TYPE_CUSTOM, null, null, $icon);
    $PAGE->flatnav->add($node);

    return '';
}

function availability_examus_before_standard_html_head() {
    global $EXAMUS;
    global $DB;

    // Not viewing quiz attempt.
    if (empty($EXAMUS)) {
        return;
    }

    if (isset($EXAMUS['attempt_data']['attempt_id'])) {
        $attemptid = $EXAMUS['attempt_data']['attempt_id'];
        $attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid]);
        if (!$attempt || $attempt->state != 'inprogress') {
            return;
        }
    } else {
        return;
    }

    // Not examused quiz.
    $cmid = $EXAMUS['attempt_data']['cm_id'];
    $courseid = $EXAMUS['attempt_data']['course_id'];

    $modinfo = get_fast_modinfo($courseid);
    $cm = $modinfo->get_cm($cmid);

    if (!condition::has_examus_condition($cm)) {
        return;
    }

    $origin = isset($_SESSION['examus_client_origin']) ? $_SESSION['examus_client_origin'] : '';

    ob_start();
    include(dirname(__FILE__).'/proctoring_fader.php');
    $output = ob_get_clean();

    return $output;
}
