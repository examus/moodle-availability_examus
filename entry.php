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

require_once('../../../config.php');

require_login();

global $DB;

$accesscode = required_param('accesscode', PARAM_RAW);
$entry = $DB->get_record('availability_examus', array('accesscode' => $accesscode));

if ($entry) {
    $entry->status = 'Started';
    $entry->timemodified = time();
    $DB->update_record('availability_examus', $entry);
    $cmid = $entry->cmid;

    $_SESSION['examus'] = $accesscode;

    list($course, $cm) = get_course_and_cm_from_cmid($cmid);

    redirect($cm->url->out(false));
}else{
    echo "Unknown access code";
}

// TODO: Show message for user that smth went wrong
die;
