<?php
// This file is part of Moodle - http://moodle.org/
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
 * External API for saving course generation structure.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External API class for saving course generation structure.
 *
 * @package    block_dixeo_coursegen
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class save_structure extends external_api {

    /**
     * Returns description of save_structure parameters
     *
     * @return external_function_parameters
     */
    public static function save_structure_parameters(): external_function_parameters {
        return new external_function_parameters([
            'jobid' => new external_value(PARAM_TEXT, 'Job ID', VALUE_REQUIRED),
            'structure' => new external_value(PARAM_RAW, 'JSON structure', VALUE_REQUIRED),
            'current_index' => new external_value(PARAM_INT, 'Current history index user is working from', VALUE_REQUIRED),
        ]);
    }

    /**
     * Save structure (creates new version)
     *
     * @param string $jobid The job identifier
     * @param string $structure JSON structure data
     * @param int $currentindex Current history index user is working from
     * @return array Save result
     */
    public static function save_structure(
        string $jobid,
        string $structure,
        int $currentindex = -1
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::save_structure_parameters(), [
            'jobid' => $jobid,
            'structure' => $structure,
            'current_index' => $currentindex,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_login();

        // Get all versions ordered by time (oldest first)
        $allversions = $DB->get_records('block_dixeo_coursegen_structure',
            ['jobid' => $params['jobid']],
            'timecreated ASC',
            'id, userid, description'
        );

        if (empty($allversions)) {
            throw new \moodle_exception('structurenotfound', 'block_dixeo_coursegen');
        }

        $totalversions = count($allversions);
        $versionsarray = array_values($allversions);

        // Check user owns this structure (or has manage capability)
        $first = reset($versionsarray);
        if ($first->userid != $USER->id) {
            require_capability('block/dixeo_coursegen:manage', $context);
        }

        // Validate JSON.
        $decoded = json_decode($params['structure']);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception('invalidjson', 'block_dixeo_coursegen');
        }

        // Determine if we're saving from a previous state
        $latestindex = $totalversions - 1;
        $savingfromprevious = ($params['current_index'] >= 0 && $params['current_index'] < $latestindex);

        // If saving from a previous state, delete all future versions
        if ($savingfromprevious) {
            $versionsToDelete = array_slice($versionsarray, $params['current_index'] + 1);
            foreach ($versionsToDelete as $versionToDelete) {
                $DB->delete_records('block_dixeo_coursegen_structure', ['id' => $versionToDelete->id]);
            }
        }

        // Get the current version (or latest if index is invalid)
        $currentversion = ($params['current_index'] >= 0 && $params['current_index'] < $totalversions)
            ? $versionsarray[$params['current_index']]
            : end($versionsarray);

        // Create new version with timestamp as version identifier
        $newversion = (string)time();

        $newrecord = new \stdClass();
        $newrecord->jobid = $params['jobid'];
        $newrecord->userid = $USER->id;
        $newrecord->description = $currentversion->description;
        $newrecord->structure = $params['structure'];
        $newrecord->version = $newversion;
        $newrecord->timecreated = time();

        $newid = $DB->insert_record('block_dixeo_coursegen_structure', $newrecord);

        // Get new total count after deletion
        $newtotal = $DB->count_records('block_dixeo_coursegen_structure', ['jobid' => $params['jobid']]);

        return [
            'id' => (int)$newid,
            'version' => $newrecord->version,
            'index' => $newtotal - 1, // New version is now the latest (last index)
            'total' => $newtotal,
            'success' => true,
        ];
    }

    /**
     * Bump version number (deprecated - kept for backward compatibility but not used)
     *
     * @param string $version Current version (e.g., "1.2")
     * @param bool $major If true, bump major (1.2 -> 2.0); if false, bump minor (1.2 -> 1.3)
     * @return string New version
     */
    private static function bump_version(string $version, bool $major = false): string {
        // Parse version.
        if (strpos($version, '.') === false) {
            // Old integer format, convert to major.minor.
            $version = $version . '.0';
        }

        list($maj, $min) = explode('.', $version, 2);
        $maj = (int)$maj;
        $min = (int)$min;

        if ($major) {
            // Bump major version, reset minor to 0.
            return ($maj + 1) . '.0';
        } else {
            // Bump minor version.
            return $maj . '.' . ($min + 1);
        }
    }

    /**
     * Returns description of save_structure return value
     *
     * @return external_single_structure
     */
    public static function save_structure_returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'New record ID'),
            'version' => new external_value(PARAM_TEXT, 'New version identifier'),
            'index' => new external_value(PARAM_INT, 'New index in history'),
            'total' => new external_value(PARAM_INT, 'Total number of versions'),
            'success' => new external_value(PARAM_BOOL, 'Success status'),
        ]);
    }
}
