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

namespace availability_examus;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Outputs detailed info about log entry
 */
class log_details {
    /**
     * @var integer Entry id
     */
    protected $id = null;

    /**
     * @var string URL
     */
    protected $url = null;

    /**
     * Constructor
     * @param integer $id Entry id
     * @param string $url
     */
    public function __construct($id, $url) {
        $this->id = $id;
    }

    /**
     * Renders and echoes log entry detail page
     */
    public function render() {
        global $DB;
        $entry = $DB->get_record('availability_examus', ['id' => $this->id]);
        $user = $DB->get_record('user', ['id' => $entry->userid]);

        $course = $DB->get_record('course', ['id' => $entry->courseid]);
        if (!empty($course)) {
            $modinfo = get_fast_modinfo($course);
            $cm = $modinfo->get_cm($entry->cmid);
        }

        $warnings = [];
        $lang = current_language();

        if (!$entry->warnings) {
            $warningsraw = [];
        } else {
            $warningsraw = json_decode($entry->warnings, true);
        }

        $titles = @json_decode($entry->warning_titles, true);
        if (empty($titles) || !is_array($titles)) {
            $titles = [];
        }

        foreach ($warningsraw as $warningraw) {
            $warning = [];
            if (is_string($warningraw)) {
                $warning = @json_decode($warningraw, true);
                if (!$warning) {
                    $warning = ['type' => $warningraw];
                }
            } else {
                $warning = $warningraw;
            }

            $type  = isset($warning['type']) ? $warning['type'] : null;
            $title = isset($titles[$type]) ? $titles[$type] : null;

            if (is_array($title)) {
                $localized = null;

                // Try current language.
                if (isset($title[$lang])) {
                    $localized = $title[$lang];
                }

                // Default to english.
                if (isset($title['en']) && !$localized) {
                    $localized = $title['en'];
                }

                // Default to first.
                if (!$localized) {
                    $localized = reset($title);
                }

                $warning['title'] = $localized;
            }

            if (isset($warning['start']) && !is_numeric($warning['start'])) {
                $datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $warning['start']);
                $warning['start'] = $datetime->getTimestamp();
            }

            if (isset($warning['end']) && !is_numeric($warning['end'])) {
                $datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $warning['end']);
                $warning['end'] = $datetime->getTimestamp();
            }

            $warnings[] = $warning;
        }

        $table = new \flexible_table('availability_examus_show');

        $table->define_columns(['key', 'value']);
        $table->define_headers(['Key', 'Value']);
        $table->sortable(false);
        $table->set_attribute('class', 'generaltable generalbox');
        $table->define_baseurl($this->url);
        $table->setup();

        $threshold = $entry->threshold ? json_decode($entry->threshold) : (object)['attention' => null, 'rejected' => null];

        $table->add_data([
            get_string('date_modified', 'availability_examus'),
            common::format_date($entry->timemodified)
        ]);

        $table->add_data([
            get_string('time_scheduled', 'availability_examus'),
            common::format_date($entry->timescheduled)
        ]);

        $table->add_data([
            get_string('user'),
            $user->firstname . " " . $user->lastname . "<br>" . $user->email
        ]);

        $table->add_data([
            get_string('course'),
            !empty($course) ? $course->fullname : null,
        ]);

        $table->add_data([
            get_string('module', 'availability_examus'),
            !empty($course) ? $cm->get_formatted_name() : null,
        ]);
        $table->add_data([
            get_string('status', 'availability_examus'),
            $entry->status,
        ]);

        if ($entry->review_link !== null) {
            $reviewlink = "<a href='" . $entry->review_link . "'>" . get_string('link', 'availability_examus') . "</a>";
        } else {
            $reviewlink = "-";
        }

        $table->add_data([
            get_string('review', 'availability_examus'),
            $reviewlink,
        ]);
        $table->add_data([
            get_string('score', 'availability_examus'),
            $entry->score,
        ]);

        $table->add_data([
            get_string('threshold_attention', 'availability_examus'),
            $threshold->attention,
        ]);

        $table->add_data([
            get_string('threshold_rejected', 'availability_examus'),
            $threshold->rejected,
        ]);

        $table->add_data([
            get_string('session_start', 'availability_examus'),
            common::format_date($entry->session_start),

        ]);
        $table->add_data([
            get_string('session_end', 'availability_examus'),
            common::format_date($entry->session_end),
        ]);
        $table->add_data([
            get_string('comment', 'availability_examus'),
            $entry->comment,
        ]);
        $table->print_html();

        if (count($warnings) == 0) {
            return;
        }

        echo "<hr>";
        echo "<h2>".get_string('log_details_warnings', 'availability_examus')."</h2>";

        $table = new \flexible_table('availability_examus_show');

        $table->define_columns(['type', 'title', 'start', 'end']);
        $table->define_headers([
            get_string('log_details_warning_type', 'availability_examus'),
            get_string('log_details_warning_title', 'availability_examus'),
            get_string('log_details_warning_start', 'availability_examus'),
            get_string('log_details_warning_end', 'availability_examus'),
        ]);
        $table->sortable(false);
        $table->set_attribute('class', 'generaltable generalbox');
        $table->define_baseurl($this->url);
        $table->setup();

        foreach ($warnings as $warning) {
            $table->add_data([
                (isset($warning['type']) ? $warning['type'] : ''),
                (isset($warning['title']) ? $warning['title'] : ''),
                (isset($warning['start']) ? common::format_date($warning['start']) : ''),
                (isset($warning['end']) ? common::format_date($warning['end']) : '')
            ]);
        }

        $table->print_html();
    }
}
