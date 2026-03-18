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

namespace block_dixeo_designer;

defined('MOODLE_INTERNAL') || die();

/**
 * Constants for designer workflow phases and submission statuses.
 *
 * Keeping these in one place reduces the risk of typos and drift between
 * persistence, workflow orchestration, and UI polling payloads.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class workflow_constants {
    private function __construct() {
        // Static class.
    }

    // Submission statuses.
    public const SUBMISSION_STATUS_DRAFT = 'draft';
    public const SUBMISSION_STATUS_GENERATING_STRUCTURE = 'generating_structure';
    public const SUBMISSION_STATUS_SYNCING_FILES = 'syncing_files';
    public const SUBMISSION_STATUS_NOOP_GENERATION = 'noop_generation';
    public const SUBMISSION_STATUS_NOOP_COMPLETED = 'noop_completed';
    public const SUBMISSION_STATUS_COURSE_CREATED = 'course_created';

    // Finalize progress phases (polled by the UI).
    public const FINALIZE_PHASE_GENERATING_CONTENT = 'generating_content';
    public const FINALIZE_PHASE_FINALIZING = 'finalizing';
    public const FINALIZE_PHASE_DONE = 'done';

    // Remote structure generation validates the minimum instruction length.
    public const MIN_INSTRUCTIONS_LEN = 20;
}

