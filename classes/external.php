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
 * Return data for the course generator block.
 *
 * @package   block_dixeo_coursegen
 * @copyright 2025 Josemaria Bolanos <admin@mako.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dixeo_coursegen;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function for getting availabe modules for the course generator block.
 */
class external extends external_api {

    /**
     * Define parameters for external function.
     *
     * @return external_function_parameters
     */
    public static function generate_course_parameters(): external_function_parameters {
        return new external_function_parameters([
            'description' => new external_value(PARAM_TEXT, 'Course description', VALUE_DEFAULT, ''),
            'coursefiles' => new external_multiple_structure(
                new external_value(PARAM_FILE, 'File'),
                'Optional course files',
                VALUE_DEFAULT,
                []
            ),
        ]);
    }

    /**
     * Return a list of the required fields for a given entity type.
     *
     * @param string $description Course description
     * @param array $coursefiles Array of course files
     * @return array
     */
    public static function generate_course(string $description, array $coursefiles): array {
        global $CFG;

        require_once($CFG->dirroot . '/course/lib.php');

        require_login();

        $context = context_system::instance();
        require_capability('moodle/course:create', $context);

        // Unlock the session early to not stop other http requests.
        \core\session\manager::write_close();

        // Parameter validation.
        self::validate_parameters(self::generate_course_parameters(), [
            'description' => $description,
            'coursefiles' => $coursefiles,
        ]);

        try {
            $generationtype = course_generator::get_generator_type();
            if ($generationtype === 'remote') {
                $generator = new course_generator($description, $coursefiles);
                $course = $generator->generate_course();

                return [
                    'success' => true,
                    'courseid' => $course->id,
                    'coursename' => $course->fullname,
                    'errormessage' => '',
                ];
            } else if ($generationtype == 'local') {
                // Check that the local generator is available.
                $pluginmanager = \core_plugin_manager::instance();
                $localedai = $pluginmanager->get_plugin_info('local_edai');

                if (!$localedai) {
                    return [
                        'success' => false,
                        'courseid' => null,
                        'coursename' => null,
                        'errormessage' => get_string('error_generator_notfound', 'block_dixeo_coursegen'),
                    ];
                } else {
                    require_once($CFG->dirroot . '/local_edai/externallib.php');
                    $course = \local_edai_course_generator_external::generate_course($description, $coursefiles);

                    return [
                        'success' => true,
                        'courseid' => $course->id,
                        'coursename' => $course->fullname,
                        'errormessage' => '',
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'courseid' => null,
                    'coursename' => null,
                    'errormessage' => get_string('error_invalid_generationtype', 'block_dixeo_coursegen', $generationtype),
                ];
            }
        } catch (Exception $e) {
            $debug = '';
            if (debugging('', DEBUG_DEVELOPER)) {
                $debug = '<br><br>Error:<br>' . $e->getMessage() . '<br><br>' . $e->getTraceAsString();
            }

            return [
                'success' => false,
                'courseid' => null,
                'coursename' => null,
                'errormessage' => get_string('error_generation_failed', 'block_dixeo_coursegen') . $debug,
            ];
        }
    }

    /**
     * Define return values.
     *
     * Return required fields
     *
     * @return external_single_structure
     */
    public static function generate_course_returns(): external_single_structure {
        return new external_single_structure(
            [
                'success' => new external_value(PARAM_BOOL, 'If the operation was successful'),
                'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_OPTIONAL),
                'coursename' => new external_value(PARAM_TEXT, 'Course name', VALUE_OPTIONAL),
                'errormessage' => new external_value(PARAM_TEXT, 'Error message', VALUE_OPTIONAL),
            ]
        );
    }
}
