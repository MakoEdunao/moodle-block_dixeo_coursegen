<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Upgrade code for the dixeo_coursegen block module.
 *
 * @package    block_dixeo_coursegen
 * @copyright  2026 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the dixeo_coursegen block module.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_block_dixeo_coursegen_upgrade($oldversion) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/certificate/db/upgradelib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2025093002) {

        // Define table block_dixeo_coursegen_structure to be created.
        $table = new xmldb_table('block_dixeo_coursegen_structure');

        // Adding fields to table block_dixeo_coursegen_structure.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('jobid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('structure', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('version', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_dixeo_coursegen_structure.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('jobid_version_uk', XMLDB_KEY_UNIQUE, ['jobid', 'version']);

        // Adding indexes to table block_dixeo_coursegen_structure.
        $table->add_index('jobid', XMLDB_INDEX_NOTUNIQUE, ['jobid']);

        // Conditionally launch create table for block_dixeo_coursegen_structure.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Dixeo_coursegen savepoint reached.
        upgrade_block_savepoint(true, 2025093002, 'dixeo_coursegen');
    }

    if ($oldversion < 2026012001) {
        // Change version column from INT to VARCHAR to support major.minor format.
        $table = new xmldb_table('block_dixeo_coursegen_structure');
        $field = new xmldb_field('version', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '1.0', 'structure');
        $key = new xmldb_key('jobid_version_uk', XMLDB_KEY_UNIQUE, ['jobid', 'version']);

        // Step 1: Drop the unique key (required before changing column type).
        $dbman->drop_key($table, $key);

        // Step 2: Convert existing integer versions to major.minor format.
        $records = $DB->get_records('block_dixeo_coursegen_structure');
        foreach ($records as $record) {
            // Convert integer version to major.minor (e.g., 1 -> 1.0, 2 -> 2.0).
            $newversion = $record->version . '.0';
            $DB->set_field('block_dixeo_coursegen_structure', 'version', $newversion, ['id' => $record->id]);
        }

        // Step 3: Change the field type from INT to VARCHAR.
        $dbman->change_field_type($table, $field);

        // Step 4: Recreate the unique key with the new column type.
        $dbman->add_key($table, $key);

        // Dixeo_coursegen savepoint reached.
        upgrade_block_savepoint(true, 2026012001, 'dixeo_coursegen');
    }

    return true;
}
