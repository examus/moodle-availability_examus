<?php
if ($hassiteconfig) {
	$ADMIN->add('root', new admin_externalpage('availability_examus_settings', get_string('settings','availability_examus'), $CFG->wwwroot . '/availability/condition/examus/index.php'));
}
