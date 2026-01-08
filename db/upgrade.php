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

    return true;
}
