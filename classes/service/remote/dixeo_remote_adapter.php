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

namespace block_dixeo_designer\service\remote;

defined('MOODLE_INTERNAL') || die();

/**
 * Adapter for Dixeo "remote API" calls used by the designer workflow.
 *
 * Keeps designer workflow orchestration (designer_service) focused on persistence
 * + state transitions, while encapsulating local_dixeo calls here.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dixeo_remote_adapter {

    /**
     * Submit an async course-structure generation request.
     *
     * @param string $instructions
     * @param string|null $templateid
     * @param int $courseid Draft course id (source of files/resources)
     * @return object Remote op object { jobid: string }
     */
    public function submit_course_structure_generation(string $instructions, ?string $templateid, int $courseid): object {
        $struct = \local_dixeo\external\service_factory::get_course_structure_service();
        return $struct->submit_generate(
            $instructions,
            $templateid,
            null,
            (string) $courseid
        );
    }

    /**
     * Get the remote job status.
     *
     * @param string $remotejobid
     * @return object
     */
    public function get_job_status(string $remotejobid): object {
        return \local_dixeo\external\service_factory::get_job_service()->get_job_status($remotejobid);
    }

    /**
     * Poll (if needed) file sync status for a draft course.
     *
     * @param int $courseid
     * @return object { status, progresspercent, filestotal, filescompleted, errormessage, lastsynccompleted }
     */
    public function get_file_sync_progress(int $courseid): object {
        $filesync = \local_dixeo\external\service_factory::get_file_sync_service();

        // Avoid remote polling when the course is already synchronized/none.
        $status = $filesync->get_status($courseid);
        $localStatus = (string) ($status->status ?? 'none');
        $uploadbytes = (int) ($status->uploadbytes ?? 0);
        $uploadtotal = (int) ($status->uploadbytestotal ?? 0);
        $outbounduploadactive = $localStatus === 'syncing' && $uploadtotal > 0 && $uploadbytes < $uploadtotal;
        if (!in_array($localStatus, ['synchronized', 'none'], true) && !$outbounduploadactive) {
            $status = $filesync->poll_status($courseid);
        }

        return (object) [
            'status' => $status->status ?? 'none',
            'progresspercent' => $status->progresspercent ?? null,
            'filestotal' => $status->filestotal ?? null,
            'filescompleted' => $status->filescompleted ?? null,
            'uploadbytes' => $status->uploadbytes ?? null,
            'uploadbytestotal' => $status->uploadbytestotal ?? null,
            'errormessage' => $status->errormessage ?? null,
            'lastsynccompleted' => $status->lastsynccompleted ?? null,
        ];
    }

    /**
     * Upload submission files to remote vector store for the given job.
     *
     * Failure is handled by caller (currently "delete_files" is best-effort).
     *
     * @param string $jobid
     * @param array $files Array of \stored_file instances
     * @return void
     */
    public function sync_files_to_remote(string $jobid, array $files): void {
        $client = \local_dixeo\external\service_factory::get_client();

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

