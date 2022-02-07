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

defined('MOODLE_INTERNAL') || die();

$string['examus:logaccess'] = 'Examus log access';
$string['examus:logaccess_course'] = 'Examus log access for course';
$string['examus:logaccess_all'] = 'Examus log access for all courses';
$string['examus:proctor_auth'] = 'Authorize to Examus App as a proctor';
$string['examus:reviewer_auth'] = 'Authorize to Examus App as a reviewer';

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
$string['normal_mode'] = 'Real-time proctoring';
$string['olympics_mode'] = 'Post-review';
$string['auto_mode'] = 'Fully automated';
$string['identification_mode'] = 'Live identification';

$string['identification'] = 'Identification mode';
$string['face_passport_identification'] = 'Face & Passport';
$string['passport_identification'] = 'Passport';
$string['face_identification'] = 'Face';
$string['skip_identification'] = 'Skip';

$string['is_trial'] = 'Trial exam';
$string['noprotection'] = 'No protection against starting outside Examus APP';
$string['auxiliary_camera'] = 'Auxiliary camera (mobile) ';

$string['rules'] = "Rules";
$string['custom_rules'] = "Custom rules";

$string['user_agreement_url'] = "User agreement URL";

$string['time_scheduled'] = 'Scheduled';
$string['time_finish'] = 'Attempt finished at';

$string['auto_rescheduling'] = 'Automatic reset for missed exams';
$string['enable'] = 'Enable';

$string['allow_to_use_websites'] = 'Allow to use websites';
$string['allow_to_use_books'] = 'Allow to use books';
$string['allow_to_use_paper'] = 'Allow to use paper';
$string['allow_to_use_messengers'] = 'Allow to use messengers';
$string['allow_to_use_calculator'] = 'Allow to use calculator';
$string['allow_to_use_excel'] = 'Allow to use excel';
$string['allow_to_use_human_assistant'] = 'Allow to use human assistant';
$string['allow_absence_in_frame'] = 'Allow absence in frame';
$string['allow_voices'] = 'Allow voices';
$string['allow_wrong_gaze_direction'] = 'Allow wrong gaze direction';

$string['select_groups'] = 'Use Examus only for selected groups';
$string['scheduling_required'] = 'A calendar entry is required';
$string['apply_filter'] = 'Apply filter';
$string['allcourses'] = 'All courses';
$string['allstatuses'] = 'All statuses';
$string['userquery'] = 'User Email starts with';
$string['fromdate'] = 'From date:';
$string['todate'] = 'To date:';

$string['score'] = 'Score';
$string['threshold_attention'] = 'Threshold: Attention';
$string['threshold_rejected'] = 'Threshold: Rejection';
$string['session_start'] = 'Session start';
$string['session_end'] = 'Session end';
$string['warnings'] = 'Warnings';
$string['comment'] = 'Comment';

$string['details'] = 'Details';

// Fader screen.
$string['fader_awaiting_proctoring'] = 'Waiting for proctoring';
$string['fader_instructions'] = '<p>Use Examus app to take the test</p>';

$string['log_details_warnings'] = 'Warnings';
$string['log_details_warning_type'] = 'Type';
$string['log_details_warning_title'] = 'Description';
$string['log_details_warning_start'] = 'Start';
$string['log_details_warning_end'] = 'End';

$string['visible_warnings'] = 'Visible warnings';
$string['warning_extra_user_in_frame'] = 'Наличие еще одного человека в кадре';
$string['warning_substitution_user'] = 'Подмена тестируемого';
$string['warning_no_user_in_frame'] = 'Отсутствие тестируемого';
$string['warning_avert_eyes'] = 'Увод взгляда с экрана';
$string['warning_timeout'] = 'Таймаут, соединение отсутствует';
$string['warning_change_active_window_on_computer'] = 'Смена активного окна на компьютере';
$string['warning_talk'] = 'Разговор во время экзамена';
$string['warning_forbidden_software'] = 'Используются запрещенные сайты/ПО';
$string['warning_forbidden_device'] = 'Используются запрещенные тех. средства';
$string['warning_voice_detected'] = 'Звуки голосов в трансляции';
$string['warning_extra_display'] = 'Используется второй монитор';
$string['warning_books'] = 'Использование книг/конспекта';
$string['warning_cheater'] = 'Нарушитель';
$string['warning_mic_muted'] = 'Микрофон отключен';
$string['warning_mic_no_sound'] = 'Нет звука';
$string['warning_mic_no_device_connected'] = 'Микрофон не подключен';
$string['warning_camera_no_picture'] = 'Нет изображения с камеры';
$string['warning_camera_no_device_connected'] = 'Камера не подключена';
$string['warning_nonverbal'] = 'Невербальное общение';
$string['warning_phone'] = 'Используется телефон';
$string['warning_phone_screen'] = 'Демонстрируется экран телефона';
$string['warning_no_ping'] = 'Приложение студента потеряло связь с сервером';
$string['warning_desktop_request_pending'] = 'Отсутствует доступ к рабочему столу';

