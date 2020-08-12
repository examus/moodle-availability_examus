<?php
namespace availability_examus;
use \stdClass;
use \html_writer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class log {
    protected $entries = [];
    protected $entries_count = null;
    protected $per_page = 30;
    protected $page = 0;

    protected $table = null;
    protected $url = null;
    protected $filters = null;

    public function __construct($filters, $page) {
        global $PAGE;

        $this->url = $PAGE->url;
        $this->filters = $filters;
        $this->page = $page;

        $this->url->params($filters);

        $this->setup_table();
        $this->fetch_data();
    }

    protected function fetch_data(){
        global $DB;
        $select = [
            'e.id id',
            'e.timemodified timemodified',
            'timescheduled',
            'u.firstname u_firstname',
            'u.lastname u_lastname',
            'u.email u_email',
            'u.id userid',
            'e.status status',
            'review_link',
            'cmid',
            'courseid'
        ];

        $where = ['TRUE'];
        $params = $this->filters;

        if(isset($params['from[day]']) && isset($params['from[month]']) && isset($params['from[year]'])){
            $month = $params['from[month]'];
            $day = $params['from[day]'];
            $year = $params['from[year]'];
            unset($params['from[month]'], $params['from[day]'], $params['from[year]']);

            $params['from'] = mktime(0,0,0, $month, $day, $year);
        };

        if(isset($params['to[day]']) && isset($params['to[month]']) && isset($params['to[year]'])){
            $month = $params['to[month]'];
            $day = $params['to[day]'];
            $year = $params['to[year]'];
            unset($params['to[month]'], $params['to[day]'], $params['to[year]']);

            $params['to'] = mktime(23,59,59, $month, $day, $year);
        };


        foreach($params as $key => $value){
            if(empty($value)){
                continue;
            }
            switch($key){
                case 'from':
                    $where[]= 'e.timemodified > :'.$key;
                    break;

                case 'to':
                    $where[]= 'e.timemodified <= :'.$key;
                    break;

                case 'userquery':
                    $params[$key] = $value.'%';
                    $where[]= 'u.email LIKE :'.$key;
                    break;

                default:
                    $where[]= $key.' = :'.$key;
            }
        }

        $course_ids = array_keys($this->get_course_list());
        if(!empty($course_ids)){
            $where[]= 'courseid IN('.implode(',', $course_ids).')';
        }else{
            $where[]= 'FALSE';
        }

        $orderBy = $this->table->get_sql_sort();

        $query = 'SELECT '.implode(', ', $select).' FROM {availability_examus} e '
               . ' LEFT JOIN {user} u ON u.id=e.userid '
               . ' WHERE '.implode(' AND ', $where)
               . ($orderBy ? ' ORDER BY '. $orderBy : '')
               . ' LIMIT '.$this->per_page.' OFFSET '.($this->page * $this->per_page)
               ;

        $queryCount = 'SELECT count(e.id) as count FROM {availability_examus} e '
                    . ' LEFT JOIN {user} u ON u.id=e.userid '
                    . ' WHERE '.implode(' AND ', $where);

        $this->entries = $DB->get_records_sql($query, $params);

        $result = $DB->get_records_sql($queryCount, $params);
        $this->entries_count = reset($result)->count;

        $this->table->pagesize($this->per_page, $this->entries_count);
    }

    protected function setup_table(){
        $table = new \flexible_table('availability_examus_table');

        $table->define_columns([
            'timemodified', 'timescheduled',  'u_email', 'courseid',
            'cmid', 'status', 'review_link', 'create_entry'
        ]);

        $table->define_headers([
            get_string('date_modified', 'availability_examus'),
            get_string('time_scheduled', 'availability_examus'),
            get_string('user'),
            get_string('course'),
            get_string('module', 'availability_examus'),
            get_string('status', 'availability_examus'),
            get_string('review', 'availability_examus'),
            ''
        ]);

        $table->define_baseurl($this->url);
        $table->sortable(true, 'date_modified');
        $table->no_sorting('courseid');
        $table->no_sorting('cmid');
        $table->set_attribute('id', 'entries');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->setup();
        $this->table = $table;
    }

    public function render_table() {
        $entries = $this->entries;
        $table = $this->table;

        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $row = [];

                $date = usergetdate($entry->timemodified);
                $row[] = '<b>' . $date['year'] . '.' . $date['mon'] . '.' . $date['mday'] . '</b> ' .
                       $date['hours'] . ':' . $date['minutes'];

                if ($entry->timescheduled) {
                    $timescheduled = usergetdate($entry->timescheduled);
                    $row[] = '<b>' . $timescheduled['year'] . '.' . $timescheduled['mon'] . '.' . $timescheduled['mday'] . '</b> ' .
                           $timescheduled['hours'] . ':' . $timescheduled['minutes'];
                } else {
                    $row[] = '';
                }

                $row[] = $entry->u_firstname . " " . $entry->u_lastname . "<br>" . $entry->u_email;

                $course = get_course($entry->courseid);
                $modinfo = get_fast_modinfo($course);
                $cm = $modinfo->get_cm($entry->cmid);

                $row[] = $course->fullname;
                $row[] = $cm->get_formatted_name();
                $row[] = $entry->status;
                if ($entry->review_link !== null) {
                    $row[] = "<a href='" . $entry->review_link . "'>" . get_string('link', 'availability_examus') . "</a>";
                } else {
                    $row[] = "-";
                }

                $scheduled =  $entry->status == 'Scheduled' && $entry->timescheduled;

                $not_started = $entry->status == 'Not inited' || $scheduled;

                $expired = time() > $entry->timescheduled + condition::EXPIRATION_SLACK;

                // Changed condition. Allow to reset all entries
                // Consequences unknown
                // if (!$not_started || ($scheduled && $expired) ) {
                if (!$not_started) {
                    $row[] =
                        "<form action='index.php' method='post'>" .
                           "<input type='hidden' name='id' value='" . $entry->id . "'>" .
                           "<input type='hidden' name='action' value='renew'>" .
                           "<input type='submit' value='" . get_string('new_entry', 'availability_examus') . "'>".
                        "</form>";
                } else {
                    // $row[] = "-";
                    $row[] =
                        "<form action='index.php' method='post'>" .
                           "<input type='hidden' name='id' value='" . $entry->id . "'>" .
                           "<input type='hidden' name='force' value='true'>" .
                           "<input type='hidden' name='action' value='renew'>" .
                           "<input type='submit' value='" . get_string('new_entry_force', 'availability_examus') . "'>".
                        "</form>";
                }
                $table->add_data($row);
            }
            $table->print_html();
        }

    }

    /**
     * Return list of modules to show in selector.
     *
     * @return array list of courses.
     */
    public function get_module_list() {
        global $DB, $SITE;

        $courses = ['' => 'All modules'];

        $sitecontext = \context_system::instance();
        // First check to see if we can override showcourses and showusers.
        $numcourses = $DB->count_records("course");

        if ($courserecords = $DB->get_records("module", null, "fullname", "id,shortname,fullname,category")) {
            foreach ($courserecords as $course) {
                if ($course->id == SITEID) {
                    $courses[$course->id] = format_string($course->fullname) . ' (' . get_string('site') . ')';
                } else {
                    $courses[$course->id] = format_string(get_course_display_name_for_list($course));
                }
            }
        }
        \core_collator::asort($courses);

        return $courses;
    }

    /**
     * Return list of courses to show in selector.
     *
     * @return array list of courses.
     */
    public function get_course_list() {
        global $DB, $SITE;

        $courses = [];

        $site_context = \context_system::instance();
        // First check to see if we can override showcourses and showusers.
        $numcourses = $DB->count_records("course");

        if ($courserecords = $DB->get_records("course", null, "fullname", "id,shortname,fullname,category")) {
            foreach ($courserecords as $course) {
                $course_context = \context_course::instance($course->id);
                if(!has_capability('availability/examus:logaccess_all', $site_context)){
                    if(!has_capability('availability/examus:logaccess_course', $course_context)){
                        continue;
                    }
                }

                if ($course->id == SITEID) {
                    $courses[$course->id] = format_string($course->fullname) . ' (' . get_string('site') . ')';
                } else {
                    $courses[$course->id] = format_string(get_course_display_name_for_list($course));
                }
            }
        }
        \core_collator::asort($courses);

        return $courses;
    }

    /**
     * Return list of courses to show in selector.
     *
     * @return array list of courses.
     */
    public function get_status_list() {
        $statuses = [
            'Started' => 'Started',
            'Not inited' => 'Not inited',
            'Rules Violation' => 'Rules Violation',
            'Clean' => 'Clean',
            'Suspicious' => 'Suspicious',
        ];


        return $statuses;
    }

    /**
     * Return list of users.
     *
     * @return array list of users.
     */
    public function get_user_list() {
        global $CFG, $SITE;

        $courseid = $SITE->id;
        if (!empty($this->course)) {
            $courseid = $this->course->id;
        }
        $context = \context_course::instance($courseid);
        $limitfrom = 0;
        $limitnum  = 10000;
        $courseusers = get_enrolled_users($context, '', null, 'u.id, ' . get_all_user_name_fields(true, 'u'),
                null, $limitfrom, $limitnum);

        $users = array();
        if ($courseusers) {
            foreach ($courseusers as $courseuser) {
                $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
            }
        }
        $users[$CFG->siteguest] = get_string('guestuser');

        return $users;
    }

    /**
     * Return list of date options.
     *
     * @return array date options.
     */
    public function get_date_options() {
        global $SITE;

        $strftimedate = get_string("strftimedate");
        $strftimedaydate = get_string("strftimedaydate");

        // Get all the possible dates.
        // Note that we are keeping track of real (GMT) time and user time.
        // User time is only used in displays - all calcs and passing is GMT.
        $timenow = time(); // GMT.

        // What day is it now for the user, and when is midnight that day (in GMT).
        $timemidnight = usergetmidnight($timenow);

        // Put today up the top of the list.
        $dates = array("$timemidnight" => get_string("today").", ".userdate($timenow, $strftimedate) );

        // If course is empty, get it from frontpage.
        $course = $SITE;
        if (!empty($this->course)) {
            $course = $this->course;
        }
        if (!$course->startdate or ($course->startdate > $timenow)) {
            $course->startdate = $course->timecreated;
        }

        $numdates = 1;
        while ($timemidnight > $course->startdate and $numdates < 365) {
            $timemidnight = $timemidnight - 86400;
            $timenow = $timenow - 86400;
            $dates["$timemidnight"] = userdate($timenow, $strftimedaydate);
            $numdates++;
        }
        return $dates;
    }

    public function render_filter_form() {
        global $OUTPUT;

        $courseid = $this->filters['courseid'];

        $userquery = $this->filters['userquery'];
        $date = $this->filters['timemodified'];
        $status = $this->filters['status'];

        echo html_writer::start_tag('form', ['class' => 'examuslogselecform', 'action' => $this->url, 'method' => 'get']);
        echo html_writer::start_div();

        // Add course selector.
        $sitecontext = \context_system::instance();
        $courses = $this->get_course_list();
        $users = $this->get_user_list();
        $dates = $this->get_date_options();
        $statuses = $this->get_status_list();

        echo html_writer::start_div(null, ['class' => '', 'style' => 'padding: 0 0 0.8rem;']);
        echo html_writer::label(get_string('selectacourse'), 'menuid', false, ['class' => 'accesshide']);
        echo html_writer::select($courses, "courseid", $courseid, get_string('allcourses', 'availability_examus'), ['style'=>'height: 2.5rem;margin-right: 0.5rem']);


        // Add user selector.
        echo html_writer::label(get_string('selctauser'), 'menuuser', false, ['class' => 'accesshide']);
        echo html_writer::empty_tag('input', [
            'name' => 'userquery',
            'value' => $userquery,
            'placeholder' => get_string("userquery", 'availability_examus'),
            'class' => 'form-control',
            'style' => 'width: auto;clear: none;display: inline-block;vertical-align: middle;font-size:inherit;height: 2.5rem;margin-right: 0.5rem'
        ]);

        // Add status selector.
        //echo html_writer::label(get_string('selectstatus'), 'menuuser', false, array('class' => 'accesshide'));
        echo html_writer::select($statuses, "status", $status, get_string('allstatuses', 'availability_examus'), ['style'=>'height: 2.5rem;margin-right: 0.5rem']);

        /*
        // Add date selector.
        echo html_writer::label(get_string('date'), 'menudate', false, ['class' => 'accesshide']);
        echo html_writer::select($dates, "timemodified", $date, get_string('alldays'));
        */


        // Get the calendar type used - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();
        $dateformat = $calendartype->get_date_order(2000, date('Y'));
        // Reverse date element (Day, Month, Year), in RTL mode.
        if (right_to_left()) {
            $dateformat = array_reverse($dateformat);
        }

        echo html_writer::end_div();

        // From date
        echo html_writer::start_div(null, ['class' => 'fdate_selector', 'style' => 'padding: 0 0 0.8rem;']);

        echo html_writer::label(get_string('fromdate',  'availability_examus'), '', false, ['style' => 'width: 12%;']);

        foreach ($dateformat as $key => $value) {
            $name = 'from['.$key.']';
            echo html_writer::select($value, $name, $this->filters[$name], null, ['style'=>'height: 2.5rem;margin-right: 0.5rem']);
        }
        // The YUI2 calendar only supports the gregorian calendar type so only display the calendar image if this is being used.
        if ($calendartype->get_name() === 'gregorian') {
            form_init_date_js();
            echo html_writer::start_tag('a', [
                'href' => '#',
                'title' => get_string('calendar', 'calendar'),
                'class' => 'visibleifjs',
                'name' => 'from[calendar]'
            ]);
            echo $OUTPUT->pix_icon('i/calendar', get_string('calendar', 'calendar') , 'moodle');
            echo html_writer::end_tag('a');
        }
        echo html_writer::end_div();

        // To date
        echo html_writer::start_div(null, ['class' => 'fdate_selector', 'style' => 'padding: 0 0 0.8rem;']);

        echo html_writer::label(get_string('todate',  'availability_examus'), '', false, ['style' => 'width: 12%;']);

        foreach ($dateformat as $key => $value) {
            $name = 'to['.$key.']';
            echo html_writer::select($value, $name, $this->filters[$name], null, ['style'=>'height: 2.5rem;margin-right: 0.5rem']);
        }
        // The YUI2 calendar only supports the gregorian calendar type so only display the calendar image if this is being used.
        if ($calendartype->get_name() === 'gregorian') {
            form_init_date_js();
            echo html_writer::start_tag('a', [
                'href' => '#',
                'title' => get_string('calendar', 'calendar'),
                'class' => 'visibleifjs',
                'name' => 'to[calendar]'
            ]);
            echo $OUTPUT->pix_icon('i/calendar', get_string('calendar', 'calendar') , 'moodle');
            echo html_writer::end_tag('a');
        }
        echo html_writer::end_div();


        echo html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => get_string('apply_filter', 'availability_examus'),
            'class' => 'btn btn-secondary'
        ]);

        echo html_writer::end_div();
        echo html_writer::end_tag('form');



    }
}
