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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace block_dixeo_designer\service;

defined('MOODLE_INTERNAL') || die();

use block_dixeo_designer\submission_repository;
use block_dixeo_designer\submission_service;
use block_dixeo_designer\submission_file_service;
use block_dixeo_designer\structure_repository;

/**
 * Designer workflow: start generation, poll status, finalize or cancel.
 *
 * Uses block repositories/services and local_dixeo API only (no persistence interface).
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class designer_service {

    /** @var submission_service */
    private submission_service $submissions;

    /** @var submission_file_service */
    private submission_file_service $files;

    /** @var structure_repository */
    private structure_repository $structures;

    /** @var designer_course_creation_service */
    private $coursecreation;

    /**
     * Constructor.
     *
     * @param submission_service|null $submissions
     * @param submission_file_service|null $files
     * @param structure_repository|null $structures
     * @param designer_course_creation_service|null $coursecreation
     */
    public function __construct(
        ?submission_service $submissions = null,
        ?submission_file_service $files = null,
        ?structure_repository $structures = null,
        ?designer_course_creation_service $coursecreation = null
    ) {
        $this->submissions = $submissions ?? new submission_service();
        $this->files = $files ?? new submission_file_service();
        $this->structures = $structures ?? new structure_repository();
        $this->coursecreation = $coursecreation ?? new designer_course_creation_service();
    }

    /**
     * Start async generation: create draft course, sync files, submit structure job.
     *
     * @param string $jobid
     * @param int $userid
     * @param string $description
     * @param string|null $templateid
     * @return object { courseid: int, remotejobid: string }
     */
    public function start_generation(string $jobid, int $userid, string $description, ?string $templateid): object {
        $submission = $this->submissions->save_submission($jobid, $userid, $description, $templateid);
        $this->submissions->mark_status($submission, 'generating_structure');

        $course = $this->coursecreation->create_draft_course($userid);
        $this->submissions->set_draft_and_remote_job($submission, (int) $course->id, null);

        try {
            $this->files->copy_files_to_course_resources((int) $submission->id, (int) $course->id, $userid);
            $this->coursecreation->enable_draft_file_sync_and_wait((int) $course->id, $userid);
            $this->sync_submission_files_to_remote((int) $submission->id, $jobid);

            $instructions = trim($description);
            if ($instructions === '') {
                $instructions = get_string('designer_default_file_prompt', 'block_dixeo_designer');
            }

            $struct = \local_dixeo\external\service_factory::get_course_structure_service();
            $op = $struct->submit_generate(
                $instructions,
                $templateid,
                null,
                (string) $course->id
            );
            $this->submissions->set_draft_and_remote_job($submission, (int) $course->id, $op->jobid);

            return (object) [
                'courseid' => (int) $course->id,
                'remotejobid' => $op->jobid,
            ];
        } catch (\Throwable $e) {
            $this->coursecreation->delete_draft_course((int) $course->id);
            $this->submissions->clear_course($submission);
            throw $e;
        }
    }

    /**
     * Get the status of the remote structure generation job.
     *
     * @param string $jobid
     * @param int $userid
     * @return object { status, progress, completed, failed, result, error }
     */
    public function get_structure_status(string $jobid, int $userid): object {
        $submission = $this->submissions->get_submission($jobid);
        if (!$submission || (int) $submission->userid !== $userid || empty($submission->remotejobid)) {
            return (object) [
                'status' => 'unknown',
                'progress' => 0,
                'completed' => false,
                'failed' => false,
                'result' => null,
                'error' => null,
            ];
        }

        $jobstatus = \local_dixeo\external\service_factory::get_job_service()->get_job_status($submission->remotejobid);
        $result = $jobstatus->result;
        if (is_string($result)) {
            $decoded = json_decode($result, true);
            $result = is_array($decoded) ? $decoded : null;
        }

        return (object) [
            'status' => $jobstatus->status,
            'progress' => $jobstatus->progress,
            'completed' => $jobstatus->is_completed(),
            'failed' => $jobstatus->is_failed(),
            'result' => $jobstatus->is_completed() ? $result : null,
            'error' => $jobstatus->errormessage,
        ];
    }

    /**
     * Finalize the draft course after structure is ready.
     *
     * @param string $jobid
     * @param int $userid
     * @param bool $createcourse
     * @return \stdClass|null
     */
    public function finalize_course(string $jobid, int $userid, bool $createcourse): ?\stdClass {
        $submission = $this->submissions->get_submission($jobid);
        if (!$submission || (int) $submission->userid !== $userid || empty($submission->courseid)) {
            return null;
        }

        $structureJson = $this->structures->get_latest_structure($jobid);
        if ($structureJson !== null) {
            $result = json_decode($structureJson, true);
            $result = is_array($result) ? $result : [];
        } else {
            $jobstatus = \local_dixeo\external\service_factory::get_job_service()->get_job_status($submission->remotejobid);
            if (!$jobstatus->is_completed() || empty($jobstatus->result)) {
                return null;
            }
            $result = $jobstatus->result;
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                $result = is_array($decoded) ? $decoded : [];
            }
            $this->structures->save_structure_version($jobid, $userid, $submission->prompt ?? '', $result);
        }

        if (!$createcourse) {
            return null;
        }

        $course = $this->coursecreation->finalize_draft_course(
            (int) $submission->courseid,
            $result,
            $userid
        );
        $this->submissions->attach_course($submission, (int) $course->id);

        return $course;
    }

    /**
     * Cancel the draft: delete draft course and reset submission.
     *
     * @param string $jobid
     * @param int $userid
     * @return bool
     */
    public function cancel_draft(string $jobid, int $userid): bool {
        $submission = $this->submissions->get_submission($jobid);
        if (!$submission || (int) $submission->userid !== $userid) {
            return false;
        }
        if (!empty($submission->courseid)) {
            $this->coursecreation->delete_draft_course((int) $submission->courseid);
        }
        $this->submissions->clear_course($submission);
        return true;
    }

    /**
     * Upload submission files to the remote API (vector store by jobid).
     *
     * @param int $submissionid
     * @param string $jobid
     * @return void
     */
    private function sync_submission_files_to_remote(int $submissionid, string $jobid): void {
        $client = \local_dixeo\external\service_factory::get_client();
        $files = $this->files->get_files($submissionid);

        try {
            $client->delete_files($jobid);
        } catch (\Throwable $e) {
            // Ignore so first-time uploads work.
        }

        if (!empty($files)) {
            $client->upload_files($jobid, $files);
        }
    }
}
