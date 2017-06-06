<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_deleted',
        'callback' => '\availability_examus\condition::course_module_deleted'
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\availability_examus\condition::user_enrolment_deleted'
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => '\availability_examus\condition::attempt_submitted'
    ]
];
