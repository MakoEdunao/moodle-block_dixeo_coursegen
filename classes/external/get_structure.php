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
 * External API for retrieving course generation structure.
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
 * External API class for retrieving course generation structure.
 *
 * @package    block_dixeo_coursegen
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_structure extends external_api {

    /**
     * Returns description of get_structure parameters
     *
     * @return external_function_parameters
     */
    public static function get_structure_parameters(): external_function_parameters {
        return new external_function_parameters([
            'jobid' => new external_value(PARAM_TEXT, 'Job ID', VALUE_REQUIRED),
            'index' => new external_value(PARAM_INT, 'History index (optional, loads latest if not specified)', VALUE_DEFAULT, -1),
        ]);
    }

    /**
     * Get structure by job ID
     *
     * @param string $jobid The job identifier
     * @param int $index Optional history index (loads latest if -1)
     * @return array Structure data
     */
    public static function get_structure(string $jobid, int $index = -1): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_structure_parameters(), [
            'jobid' => $jobid,
            'index' => $index,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_login();

        // Get all versions ordered by time (oldest first)
        $allversions = $DB->get_records('block_dixeo_coursegen_structure',
            ['jobid' => $params['jobid']],
            'timecreated ASC',
            'id'
        );

        if (empty($allversions)) {
            throw new \moodle_exception('structurenotfound', 'block_dixeo_coursegen');
        }

        $totalversions = count($allversions);
        $versionsarray = array_values($allversions);

        if ($params['index'] === -1 || $params['index'] >= $totalversions) {
            // Load latest version (last in array)
            $targetid = $versionsarray[$totalversions - 1]->id;
            $currentindex = $totalversions - 1;
        } else {
            // Load specific index
            $targetid = $versionsarray[$params['index']]->id;
            $currentindex = $params['index'];
        }

        $structure = $DB->get_record('block_dixeo_coursegen_structure', ['id' => $targetid], '*', MUST_EXIST);

        // Check user owns this structure (or has manage capability).
        if ($structure->userid != $USER->id) {
            require_capability('block/dixeo_coursegen:manage', $context);
        }

        return [
            'structure' => $structure->structure,
            'version' => $structure->version,
            'jobid' => $structure->jobid,
            'index' => $currentindex,
            'total' => $totalversions,
        ];
    }

    /**
     * Returns description of get_structure return value
     *
     * @return external_single_structure
     */
    public static function get_structure_returns(): external_single_structure {
        return new external_single_structure([
            'structure' => new external_value(PARAM_RAW, 'JSON structure'),
            'version' => new external_value(PARAM_TEXT, 'Version identifier'),
            'jobid' => new external_value(PARAM_TEXT, 'Job ID'),
            'index' => new external_value(PARAM_INT, 'Current index in history'),
            'total' => new external_value(PARAM_INT, 'Total number of versions'),
        ]);
    }
}
