<?php

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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localwstemplate
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'availability_examus_user_proctored_modules' => array(
                'classname'   => 'availability_examus_external',
                'methodname'  => 'user_proctored_modules',
                'classpath'   => 'availability/condition/examus/externallib.php',
                'description' => 'Returns modules exams for user',
                'type'        => 'write',
                'services'    => 'Examus',
        ),
        'availability_examus_submit_proctoring_review' => array(
                'classname'   => 'availability_examus_external',
                'methodname'  => 'submit_proctoring_review',
                'classpath'   => 'availability/condition/examus/externallib.php',
                'description' => 'Accepts review for proctoring session',
                'type'        => 'write',
                'services'    => 'Examus',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Examus' => array(
                'functions' => array ('availability_examus_user_proctored_modules', 'availability_examus_submit_proctoring_review'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);
