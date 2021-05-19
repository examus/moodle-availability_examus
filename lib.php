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

use availability_examus\condition;
use availability_examus\state;

/**
 * Hooks into navbar rendering, add link to log, if user has such capability
 * @return string
 */
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

/**
 * Hooks into head rendering. Adds proctoring fader/shade and accompanying javascript
 * This is used to prevent users from seeing questions before it is known that
 * attempt is viewed thorough Examus WebApp
 *
 * @return string
 */
function availability_examus_before_standard_html_head() {
    global $DB, $USER;

    if (isset(state::$attempt['attempt_id'])) {
        $attemptid = state::$attempt['attempt_id'];
        $attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid]);
        if (!$attempt || $attempt->state != 'inprogress') {
            return '';
        }
    } else {
        return '';
    }

    $cmid = state::$attempt['cm_id'];
    $courseid = state::$attempt['course_id'];

    $modinfo = get_fast_modinfo($courseid);
    $cm = $modinfo->get_cm($cmid);

    if (!condition::user_in_proctored_groups($cm, $USER->id)) {
        return '';
    }

    if (!condition::has_examus_condition($cm)) {
        return '';
    }

    if (condition::get_no_protection($cm)) {
        return '';
    }

    // Check that theres more rules, which pass.
    // If we have no examus accesstoken (condition fails),
    // but the module is still avalible, this means we should not
    // enfoce proctoring.
    $availibilityinfo = new \core_availability\info_module($cm);
    $reason = '';
    $isavailiblegeneral = $availibilityinfo->is_available($reason, false, $USER->id);
    $isavailibleexamus  = condition::is_available_internal($courseid, $cm->id, $USER->id);
    if (!$isavailibleexamus && $isavailiblegeneral) {
        return '';
    }

    ob_start();
    include(dirname(__FILE__).'/proctoring_fader.php');
    $output = ob_get_clean();

    return $output;
}
