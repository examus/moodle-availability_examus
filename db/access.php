<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'availability/examus:logaccess' => [
        'riskbitmask' => RISK_PERSONAL | RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => []
    ],
];
