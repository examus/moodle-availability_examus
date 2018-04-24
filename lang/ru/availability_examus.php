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

$string['description'] = 'Позволяет студентам использовать сервис прокторинга "Экзамус"';
$string['pluginname'] = 'Прокторинг "Экзамус"';
$string['title'] = 'Экзамус';

$string['use_examus'] = 'Используйте приложение "Экзамус", чтобы получить доступ к модулю';
$string['settings'] = 'Настройки прокторинга "Экзамус"';
$string['status'] = 'Статус';
$string['review'] = 'Результат';
$string['module'] = 'Модуль';
$string['new_entry'] = 'Новая запись';
$string['error_setduration'] = 'Длительность в минутах должна быть кратна 30 (30, 60, 90)';
$string['duration'] = 'Длительность в минутах, кратная 30';
$string['link'] = 'Архив';

$string['new_entry_created'] = 'Новая запись успешно создана';
$string['entry_exist'] = 'Новая запись уже существует';
$string['date_modified'] = 'Дата последнего изменения';

$string['mode'] = 'Режим прокторинга';
$string['normal_mode'] = 'Нормальный (Ручной прокторинг)';
$string['olympics_mode'] = 'Олимпиадный (Полностью автоматический)';
$string['identification_mode'] = 'Идентификация (Ручная идентификация, автоматический прокторинг)';

$string['rules'] = 'Правила';

$string['time_scheduled'] = 'Время записи в календаре';

$string['allow_to_use_websites'] = 'Разрешить использование веб-сайтов';
$string['allow_to_use_books'] = 'Разрешить использование книг';
$string['allow_to_use_paper'] = 'Разрешить использование черновиков';
$string['allow_to_use_messengers'] = 'Разрешить использование мессенджеров';
$string['allow_to_use_calculator'] = 'Разрешить использование калькулятора';
$string['allow_to_use_excel'] = 'Разрешить использование Excel';
$string['allow_to_use_human_assistant'] = 'Разрешить использование помощи людей';
$string['allow_absence_in_frame'] = 'Разрешить выход из комнаты';
$string['allow_voices'] = 'Разрешить голоса';
$string['allow_wrong_gaze_direction'] = 'Разрешить взгляд в сторону';

$string['scheduling_required'] = 'Обязательна запись в календаре';
