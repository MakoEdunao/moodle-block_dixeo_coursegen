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
 * Generation status class.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dixeo_coursegen\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External API class for retrieving the generation status of a course creation task.
 *
 * @package    blocks_dixeo_coursegen
 * @copyright  2024 Your Name or Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class generation_status extends external_api {
    /**
     * Returns the parameters required for the get_status external function.
     *
     * @return external_function_parameters The parameters definition, including taskid and sesskey.
     */
    public static function get_status_parameters(): external_function_parameters {
        return new external_function_parameters([
            'taskid' => new external_value(PARAM_TEXT, 'Task id', VALUE_REQUIRED),
            'sesskey' => new external_value(PARAM_RAW, 'Session key', VALUE_REQUIRED),
        ]);
    }

    /**
     * Retrieves the generation status for a given task ID.
     *
     * @param string $taskid The unique identifier for the generation task.
     * @param string $sesskey The session key for validating the request.
     * @return array An associative array containing the status and the time it was updated.
     */
    public static function get_status(string $taskid, string $sesskey): array {
        global $DB, $USER;

        self::validate_parameters(self::get_status_parameters(), ['taskid' => $taskid, 'sesskey' => $sesskey]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_capability('block/dixeo_coursegen:create', $context);
        require_sesskey();

        // Check which generator is available.
        $pluginmanager = \core_plugin_manager::instance();
        $localedai = $pluginmanager->get_plugin_info('local_edai');

        if ($localedai) {
            $coursecreator = new \local_edai\course_creator($DB, $USER);
            $status = $coursecreator->get_generation_status($taskid);
        } else {
            $status = 0; // TODO: Handle LTI status updates.
        }

        return [
            'status' => $status,
            'timeupdated' => time(),
        ];
    }

    /**
     * Returns the structure describing the generation status.
     *
     * @return external_single_structure Structure containing the status and time updated.
     */
    public static function get_status_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, 'Status'),
            'timeupdated' => new external_value(PARAM_INT, 'Unix time'),
        ]);
    }
}
