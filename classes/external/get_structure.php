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
 * External API for retrieving course design structure.
 *
 * @package    block_dixeo_designer
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_designer\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External API class for retrieving course design structure.
 *
 * @package    block_dixeo_designer
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
        ]);
    }

    /**
     * Get the latest structure by job ID (no versioning; single structure per job).
     *
     * @param string $jobid The job identifier
     * @return array Structure data
     */
    public static function get_structure(string $jobid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_structure_parameters(), [
            'jobid' => $jobid,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_login();

        $records = $DB->get_records(
            'block_dixeo_designer_structure',
            ['jobid' => $params['jobid']],
            'timecreated DESC',
            '*',
            0,
            1
        );

        $structure = reset($records);
        if (!$structure) {
            // No DB record yet (e.g. user just arrived from generator after structure generation).
            // Fall back to completed job result from the API and persist it.
            $persistence = new \block_dixeo_designer\adapter\designer_persistence_adapter();
            $service = \local_dixeo\external\service_factory::get_course_designer_service($persistence);
            $status = $service->get_structure_status($params['jobid'], (int) $USER->id);
            if (!$status->completed || $status->result === null) {
                throw new \moodle_exception('structurenotfound', 'block_dixeo_designer');
            }
            $result = $status->result;
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                $result = is_array($decoded) ? $decoded : ['course_structure' => ['title' => '', 'sections' => []]];
            }
            $persistence->save_structure_version($params['jobid'], (int) $USER->id, '', $result);
            $structureJson = json_encode($result);
            return [
                'structure' => $structureJson,
                'jobid' => $params['jobid'],
            ];
        }

        // Check user owns this structure (or has manage capability).
        if ($structure->userid != $USER->id) {
            require_capability('block/dixeo_designer:manage', $context);
        }

        return [
            'structure' => $structure->structure,
            'jobid' => $structure->jobid,
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
            'jobid' => new external_value(PARAM_TEXT, 'Job ID'),
        ]);
    }
}
