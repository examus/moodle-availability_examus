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
require_once($CFG->libdir . "/formslib.php");
require_once($CFG->libdir . '/adminlib.php');

require_login();

admin_externalpage_setup('availability_examus_settings');

$action = optional_param('action', '', PARAM_ALPHA);

switch ($action) {
    case 'renew':
        $id = required_param('id', PARAM_TEXT);

        if(\availability_examus\common::reset_entry(['id' => $id])){
            redirect('index.php', get_string('new_entry_created', 'availability_examus'),
                     null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            redirect('index.php', get_string('entry_exist', 'availability_examus'),
                    null, \core\output\notification::NOTIFY_ERROR);
        }

        break;
    default:
        break;
}


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'availability_examus'));
global $PAGE;

$filters = [
    'courseid'     => optional_param('courseid', null, PARAM_INT),
    'timemodified' => optional_param('timemodified', null, PARAM_INT),
    'moduleid'     => optional_param('moduleid', null, PARAM_INT),
    'userid'       => optional_param('userid', null, PARAM_INT),
    'status'       => optional_param('status', null, PARAM_TEXT),
];

$log = new \availability_examus\log($filters, optional_param('page', 0, PARAM_INT));
$log->render_filter_form();
$log->render_table();


echo $OUTPUT->footer();
