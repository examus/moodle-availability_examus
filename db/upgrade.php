<?php

function xmldb_availability_examus_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    /// Add a new column newcol to the mdl_myqtype_options
    if ($oldversion < 2017061602) {
        $table = new xmldb_table('availability_examus');

        $field = new xmldb_field('duration');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('timescheduled', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Conditionally launch add field timescheduled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Examus savepoint reached.
        upgrade_plugin_savepoint(true, 2017061602, 'availability', 'examus');

    }

    return true;
}