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
 * @package    block_dixeo_designer
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_designer\external\course;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External API class for retrieving all versions for a job.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_versions extends external_api {

    /**
     * Web service parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function get_versions_parameters(): external_function_parameters {
        return new external_function_parameters([
            'job_id' => new external_value(PARAM_TEXT, 'Job ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Returns structure version history for a job so the designer can show earlier drafts.
     *
     * @param string $job_id The job identifier
     * @return array Array of version objects
     */
    public static function get_versions(string $job_id): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_versions_parameters(), [
            'job_id' => $job_id,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_login();

        // Oldest first so history navigation is stable.
        $records = $DB->get_records('block_dixeo_designer_structure',
            ['jobid' => $params['job_id']],
            'timecreated ASC',
            'id, version, timecreated, userid'
        );

        if (empty($records)) {
            throw new \moodle_exception('structurenotfound', 'block_dixeo_designer');
        }

        // Non-owners need manage capability to avoid leaking other users' structures.
        $first = reset($records);
        if ($first->userid != $USER->id) {
            require_capability('block/dixeo_designer:manage', $context);
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
     * Web service return value definitions.
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
