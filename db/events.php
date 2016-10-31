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
    ]
];
