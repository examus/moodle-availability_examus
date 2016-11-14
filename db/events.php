<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_deleted',
        'callback' => '\availability_examus\condition::course_module_deleted'
    ],
    [
        'eventname' => '\core\event\course_module_updated',
        'callback' => '\availability_examus\condition::course_module_updated'
    ],
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\availability_examus\condition::user_enrolment_created_updated'
    ],
    [
        'eventname' => '\core\event\user_enrolment_updated',
        'callback' => '\availability_examus\condition::user_enrolment_created_updated'
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\availability_examus\condition::user_enrolment_deleted'
    ],
];
