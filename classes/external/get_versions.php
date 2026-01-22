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
 * External API for retrieving all versions for a job.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External API class for retrieving all versions for a job.
 *
 * @package    block_dixeo_coursegen
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_versions extends external_api {

    /**
     * Returns description of get_versions parameters
     *
     * @return external_function_parameters
     */
    public static function get_versions_parameters(): external_function_parameters {
        return new external_function_parameters([
            'jobid' => new external_value(PARAM_TEXT, 'Job ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Get all versions for a job
     *
     * @param string $jobid The job identifier
     * @return array Array of version objects
     */
    public static function get_versions(string $jobid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_versions_parameters(), [
            'jobid' => $jobid,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_login();

        // Get all versions for this job, ordered by time created (oldest first for history navigation)
        $records = $DB->get_records('block_dixeo_coursegen_structure',
            ['jobid' => $params['jobid']],
            'timecreated ASC',
            'id, version, timecreated, userid'
        );

        if (empty($records)) {
            throw new \moodle_exception('structurenotfound', 'block_dixeo_coursegen');
        }

        // Check user owns this structure (or has manage capability) - check first record
        $first = reset($records);
        if ($first->userid != $USER->id) {
            require_capability('block/dixeo_coursegen:manage', $context);
        }

        $versions = [];
        $index = 0;
        foreach ($records as $record) {
            $versions[] = [
                'index' => $index,
                'version' => $record->version,
                'timecreated' => $record->timecreated,
            ];
            $index++;
        }

        return $versions;
    }

    /**
     * Returns description of get_versions return value
     *
     * @return external_multiple_structure
     */
    public static function get_versions_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'index' => new external_value(PARAM_INT, 'Index in history (0 = oldest)'),
                'version' => new external_value(PARAM_TEXT, 'Version identifier'),
                'timecreated' => new external_value(PARAM_INT, 'Timestamp when version was created'),
            ])
        );
    }
}
