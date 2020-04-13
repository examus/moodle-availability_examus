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

defined('MOODLE_INTERNAL') || die();

$string['examus:logaccess'] = 'Examus log access';
$string['examus:logaccess_course'] = 'Examus log access for course';
$string['examus:logaccess_all'] = 'Examus log access for all courses';

$string['description'] = 'Allows students to use Examus proctoring service';
$string['pluginname'] = 'Proctoring by Examus';
$string['title'] = 'Examus';

$string['use_examus'] = 'Use examus app to view this module';
$string['settings'] = 'Examus settings';
$string['log_section'] = 'Examus log';
$string['status'] = 'Status';
$string['review'] = 'Review';
$string['module'] = 'Module';
$string['new_entry'] = 'New entry';
$string['new_entry_force'] = 'New entry';
$string['error_setduration'] = 'Duration must be a multiple of 30';
$string['duration'] = 'Duration in minutes (a multiple of 30)';
$string['link'] = 'Logs and video';

$string['new_entry_created'] = 'New entry created';
$string['entry_exist'] = 'New entry already exist';
$string['date_modified'] = 'Date of last change';

$string['mode'] = 'Proctoring mode';
$string['normal_mode'] = 'Normal (Full human proctoring)';
$string['olympics_mode'] = 'Olympics (Automatic)';
$string['identification_mode'] = 'Identification (Human identification, automatic proctoring)';

$string['rules'] = "Rules";

$string['time_scheduled'] = 'Scheduled';
$string['auto_rescheduling'] = 'Automatic reset for missed exams';
$string['enable'] = 'Enable';

$string['allow_to_use_websites'] =  'Allow to use websites';
$string['allow_to_use_books'] =  'Allow to use books';
$string['allow_to_use_paper'] =  'Allow to use paper';
$string['allow_to_use_messengers'] =  'Allow to use messengers';
$string['allow_to_use_calculator'] =  'Allow to use calculator';
$string['allow_to_use_excel'] =  'Allow to use excel';
$string['allow_to_use_human_assistant'] =  'Allow to use human assistant';
$string['allow_absence_in_frame'] = 'Allow absence in frame';
$string['allow_voices'] =  'Allow voices';
$string['allow_wrong_gaze_direction'] =  'Allow wrong gaze direction';

$string['scheduling_required'] = 'A calendar entry is required';
$string['apply_filter'] = 'Apply filter';
$string['allcourses'] = 'All courses';
$string['allstatuses'] = 'All statuses';
$string['userquery'] = 'User Email starts with';
$string['fromdate'] = 'From date:';
$string['todate'] = 'To date:';
