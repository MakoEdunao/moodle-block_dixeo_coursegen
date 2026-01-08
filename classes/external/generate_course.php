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
 * Course generation class.
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
use block_dixeo_modulegen\helper\config;

/**
 * External API class for retrieving the generation status of a course creation task.
 *
 * @package    blocks_dixeo_coursegen
 * @copyright  2024 Your Name or Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class generate_course extends external_api {
    /**
     * Returns the parameters required for the generate_course external function.
     *
     * @return external_function_parameters The parameters definition, including job_id and sesskey.
     */
    public static function generate_course_parameters(): external_function_parameters {
        return new external_function_parameters([
            'job_id' => new external_value(PARAM_TEXT, 'Job id', VALUE_REQUIRED),
            'description' => new external_value(PARAM_TEXT, 'Course description', VALUE_REQUIRED),
            'sesskey' => new external_value(PARAM_RAW, 'Session key', VALUE_REQUIRED),
            'skip' => new external_value(PARAM_BOOL, 'Skip structure generation', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Retrieves the generation status for a given job ID.
     *
     * @param string $job_id The unique identifier for the generation job.
     * @param string $description The course description.
     * @param string $sesskey The session key for security verification.
     * @param bool $skip Whether to skip structure generation.
     * @return array An associative array containing the status and the time it was updated.
     */
    public static function generate_course(string $job_id, string $description, string $sesskey, bool $skip = false): array {
        global $DB, $USER;

        self::validate_parameters(self::generate_course_parameters(), ['job_id' => $job_id, 'description' => $description, 'sesskey' => $sesskey, 'skip' => $skip]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_capability('block/dixeo_coursegen:create', $context);
        require_sesskey();

        try {
            $generator = new \block_dixeo_coursegen\course_generator($job_id, $description, $skip);
            $course = $generator->generate_course();

            if (!$course) {
                return [
                    'courseid' => 0,
                    'coursename' => '',
                ];
            }

            return [
                'courseid' => $course->id,
                'coursename' => $course->fullname
            ];
        } catch (Exception $e) {
            $debug = '';
            if (debugging('', DEBUG_DEVELOPER)) {
                $debug = '<br><br>Error:<br>' . $e->getMessage() . '<br><br>' . $e->getTraceAsString();
            }

            return [
                'error' => get_string('error_generation_failed', 'block_dixeo_coursegen') . $debug,
            ];
        }
    }

    /**
     * Returns the structure describing the generation status.
     *
     * @return external_single_structure Structure containing the status and time updated.
     */
    public static function generate_course_returns(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_OPTIONAL),
            'coursename' => new external_value(PARAM_TEXT, 'Course name', VALUE_OPTIONAL),
            'error' => new external_value(PARAM_TEXT, 'Error message', VALUE_OPTIONAL),
        ]);
    }
}
