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

namespace block_dixeo_designer\adapter;

defined('MOODLE_INTERNAL') || die();

use local_dixeo\service\designer_persistence_interface;

/**
 * Adapts block_dixeo_designer submission and file services to local_dixeo persistence interface.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class designer_persistence_adapter implements designer_persistence_interface {

    /** @var \block_dixeo_designer\submission_service */
    private $submissions;

    /** @var \block_dixeo_designer\submission_repository */
    private $repository;

    /** @var \block_dixeo_designer\submission_file_service */
    private $files;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->repository = new \block_dixeo_designer\submission_repository();
        $this->submissions = new \block_dixeo_designer\submission_service($this->repository);
        $this->files = new \block_dixeo_designer\submission_file_service();
    }

    /**
     * @inheritdoc
     */
    public function get_submission(string $jobid): ?\stdClass {
        return $this->submissions->get_submission($jobid);
    }

    /**
     * @inheritdoc
     */
    public function get_or_create_submission(string $jobid, int $userid, string $prompt, ?string $templateid): \stdClass {
        return $this->submissions->save_submission($jobid, $userid, $prompt, $templateid);
    }

    /**
     * @inheritdoc
     */
    public function update_submission(\stdClass $submission): void {
        $this->repository->update($submission);
    }

    /**
     * @inheritdoc
     */
    public function set_draft_and_remote_job(\stdClass $submission, int $courseid, ?string $remotejobid): void {
        $this->submissions->set_draft_and_remote_job($submission, $courseid, $remotejobid);
    }

    /**
     * @inheritdoc
     */
    public function attach_course(\stdClass $submission, int $courseid): void {
        $this->submissions->attach_course($submission, $courseid);
    }

    /**
     * @inheritdoc
     */
    public function clear_course(\stdClass $submission): void {
        $this->submissions->clear_course($submission);
    }

    /**
     * @inheritdoc
     */
    public function get_submission_files(int $submissionid): array {
        return $this->files->get_files($submissionid);
    }

    /**
     * @inheritdoc
     */
    public function copy_submission_files_to_course(int $submissionid, int $courseid, int $userid): void {
        $this->files->copy_files_to_course_resources($submissionid, $courseid, $userid);
    }

    /**
     * @inheritdoc
     */
    public function save_structure_version(string $jobid, int $userid, string $description, array $result): void {
        global $DB;

        $record = (object) [
            'jobid' => $jobid,
            'userid' => $userid,
            'description' => $description,
            'structure' => json_encode($result),
            'version' => date('YmdHis') . '-' . random_int(1000, 9999),
            'timecreated' => time(),
        ];

        $DB->insert_record('block_dixeo_designer_structure', $record);
    }

    /**
     * @inheritdoc
     */
    public function add_files_to_submission(int $submissionid, int $userid, array $normalizedfiles): array {
        return $this->files->store_uploaded_files($submissionid, $userid, $normalizedfiles);
    }

    /**
     * @inheritdoc
     */
    public function delete_submission_file(int $submissionid, int $userid, int $fileid): array {
        return $this->files->delete_file($submissionid, $fileid);
    }

    /**
     * @inheritdoc
     */
    public function get_file_context_for_ui(int $submissionid): array {
        return $this->files->get_template_context($submissionid);
    }
}
