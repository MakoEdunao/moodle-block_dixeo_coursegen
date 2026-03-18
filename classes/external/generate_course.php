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
 * Course designer external API.
 *
 * @package    block_dixeo_designer
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dixeo_designer\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External API class for retrieving the generation status of a course creation task.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo (contact@dixeo.com)
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
            'templateid' => new external_value(PARAM_TEXT, 'Course template id', VALUE_DEFAULT, null, NULL_ALLOWED),
            'sesskey' => new external_value(PARAM_RAW, 'Session key', VALUE_REQUIRED),
            'skip' => new external_value(PARAM_BOOL, 'Skip structure generation', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Why: generation is asynchronous. We start the draft + file sync, submit
     * the remote structure job, then return identifiers so the UI can poll
     * progress without blocking.
     *
     * @param string $job_id The unique identifier for the generation job.
     * @param string $description The course description.
     * @param string|null $templateid The selected template identifier.
     * @param string $sesskey The session key for security verification.
     * @param bool $skip Whether to skip creating the full course (structure-only; used when finalizing).
     * @return array { courseid, remotejobid }
     */
    public static function generate_course(
        string $job_id,
        string $description,
        ?string $templateid,
        string $sesskey,
        bool $skip = false
    ): array {
        global $USER;

        self::validate_parameters(self::generate_course_parameters(), [
            'job_id' => $job_id,
            'description' => $description,
            'templateid' => $templateid,
            'sesskey' => $sesskey,
            'skip' => $skip,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        require_capability('block/dixeo_designer:create', $context);
        require_sesskey();

        $service = \block_dixeo_designer\service\designer_service_factory::get_designer_service();
        $start = $service->start_generation($job_id, (int) $USER->id, $description, $templateid);

        return [
            'courseid' => $start->courseid,
            'remotejobid' => $start->remotejobid,
        ];
    }

    /**
     * Returns the structure describing the start generation response.
     *
     * @return external_single_structure
     */
    public static function generate_course_returns(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Draft course ID'),
            'remotejobid' => new external_value(PARAM_TEXT, 'Remote structure job ID for polling'),
        ]);
    }
}
