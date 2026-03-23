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

global $CFG;

use advanced_testcase;
use block_dixeo_designer\service\designer_service;
use block_dixeo_designer\service\designer_course_creation_service;

/**
 * Tests for designer_service finalization behavior.
 *
 * @package    block_dixeo_designer
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class designer_service_test extends advanced_testcase {

    /** @var \stdClass */
    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
    }

    public function test_finalize_course_deletes_submission_after_success_when_createcourse_true(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $draftcourse = $this->getDataGenerator()->create_course();

        $submission = (object) [
            'userid' => $userid,
            'courseid' => $draftcourse->id,
            'remotejobid' => 'remote-1',
            'prompt' => 'Prompt',
        ];

        $structureJson = json_encode([
            'course_structure' => [
                'title' => 'Course title',
                'sections' => [],
            ],
        ]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);

        $mockSubmissions->expects($this->once())
            ->method('attach_course')
            ->with($this->identicalTo($submission), 77);

        $mockSubmissions->expects($this->once())
            ->method('delete_submission')
            ->with($jobid, $userid)
            ->willReturn(true);

        $mockFiles = $this->createMock(\block_dixeo_designer\service\submission\file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);
        $mockStructures->expects($this->once())
            ->method('delete_by_jobid')
            ->with($jobid);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);

        $expectedResult = json_decode($structureJson, true);
        $expectedResult = is_array($expectedResult) ? $expectedResult : [];

        $mockCourseCreation->expects($this->once())
            ->method('finalize_draft_course')
            ->with((int) $draftcourse->id, $expectedResult, $userid, $jobid)
            ->willReturn((object) ['id' => 77]);

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation);

        $course = $service->finalize_course($jobid, $userid, true);

        $this->assertNotNull($course);
        $this->assertSame(77, (int) $course->id);
    }

    public function test_finalize_course_does_not_delete_submission_when_createcourse_false(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;

        $submission = (object) [
            'userid' => $userid,
            'courseid' => 123,
            'remotejobid' => 'remote-1',
            'prompt' => 'Prompt',
        ];

        $structureJson = json_encode([
            'course_structure' => [
                'title' => 'Course title',
                'sections' => [],
            ],
        ]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);
        $mockSubmissions->expects($this->never())->method('attach_course');
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockFiles = $this->createMock(\block_dixeo_designer\service\submission\file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);
        $mockStructures->expects($this->never())->method('delete_by_jobid');

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->never())->method('finalize_draft_course');

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation);

        $course = $service->finalize_course($jobid, $userid, false);

        $this->assertNull($course);
    }

    public function test_finalize_course_does_not_delete_submission_when_course_finalization_fails(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $draftcourse = $this->getDataGenerator()->create_course();

        $submission = (object) [
            'userid' => $userid,
            'courseid' => $draftcourse->id,
            'remotejobid' => 'remote-1',
            'prompt' => 'Prompt',
        ];

        $structureJson = json_encode([
            'course_structure' => [
                'title' => 'Course title',
                'sections' => [],
            ],
        ]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);

        $mockSubmissions->expects($this->never())->method('attach_course');
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockFiles = $this->createMock(\block_dixeo_designer\service\submission\file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);
        $mockStructures->expects($this->never())->method('delete_by_jobid');

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())
            ->method('finalize_draft_course')
            ->willReturn((object) []);

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation);

        $course = $service->finalize_course($jobid, $userid, true);

        $this->assertNull($course);
    }

    public function test_finalize_course_self_heal_recreates_draft_and_runs_sync_preflight_before_fill(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $submission = (object) [
            'id' => 999,
            'userid' => $userid,
            'courseid' => null,
            'remotejobid' => 'remote-1',
            'prompt' => 'Prompt',
        ];
        $structureJson = json_encode([
            'course_structure' => [
                'title' => 'Course title',
                'sections' => [],
            ],
        ]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->expects($this->exactly(2))
            ->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);
        $mockSubmissions->expects($this->once())
            ->method('set_draft_and_remote_job')
            ->with($this->identicalTo($submission), 555, 'remote-1');
        $mockSubmissions->expects($this->once())
            ->method('attach_course')
            ->with($this->identicalTo($submission), 77);
        $mockSubmissions->expects($this->once())
            ->method('delete_submission')
            ->with($jobid, $userid)
            ->willReturn(true);

        $mockFiles = $this->createMock(\block_dixeo_designer\service\submission\file_service::class);
        $mockFiles->expects($this->once())
            ->method('copy_files_to_course_resources')
            ->with(999, 555, $userid);
        $mockFiles->expects($this->once())
            ->method('get_files')
            ->with(999)
            ->willReturn([]);

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);
        $mockStructures->expects($this->once())
            ->method('delete_by_jobid')
            ->with($jobid);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())
            ->method('create_draft_course')
            ->with($userid)
            ->willReturn((object) ['id' => 555]);
        $expectedResult = json_decode($structureJson, true);
        $expectedResult = is_array($expectedResult) ? $expectedResult : [];
        $mockCourseCreation->expects($this->once())
            ->method('enable_draft_file_sync_and_wait')
            ->with(555, $userid);
        $mockCourseCreation->expects($this->once())
            ->method('finalize_draft_course')
            ->with(555, $expectedResult, $userid, $jobid)
            ->willReturn((object) ['id' => 77]);

        $mockRemoteApi = $this->createMock(\block_dixeo_designer\service\remote\dixeo_remote_adapter::class);
        $mockRemoteApi->expects($this->once())
            ->method('sync_files_to_remote')
            ->with($jobid, []);

        $service = new designer_service(
            $mockSubmissions,
            $mockFiles,
            $mockStructures,
            $mockCourseCreation,
            $mockRemoteApi
        );

        $course = $service->finalize_course($jobid, $userid, true);
        $this->assertNotNull($course);
        $this->assertSame(77, (int) $course->id);
    }

    public function test_submit_structure_generation_appends_default_prompt_when_instructions_too_short(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;

        $submission = (object) [
            'userid' => $userid,
            'courseid' => 55,
            'templateid' => null,
            'prompt' => 'short',
            'status' => 'draft',
            'remotejobid' => null,
        ];

        $expectedDefaultPrompt = get_string('designer_default_file_prompt', 'block_dixeo_designer');
        $expectedInstructions = trim($submission->prompt . ' ' . $expectedDefaultPrompt);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);

        $mockSubmissions->expects($this->once())
            ->method('set_draft_and_remote_job')
            ->with($this->identicalTo($submission), 55, 'remote-uuid');

        $mockSubmissions->expects($this->once())
            ->method('mark_status')
            ->with($this->identicalTo($submission), workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE);

        $mockFiles = $this->createMock(\block_dixeo_designer\service\submission\file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);

        $mockRemoteApi = $this->createMock(\block_dixeo_designer\service\remote\dixeo_remote_adapter::class);
        $mockRemoteApi->expects($this->once())
            ->method('submit_course_structure_generation')
            ->with($expectedInstructions, null, 55)
            ->willReturn((object) ['jobid' => 'remote-uuid']);

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation, $mockRemoteApi);

        $result = $service->submit_structure_generation($jobid, $userid);

        $this->assertSame('remote-uuid', $result->remotejobid);
        $this->assertSame(55, (int) $result->courseid);
    }

    // --- Cancellation tests: desired rollback behaviour (DB + remote jobs). ---

    public function test_cancel_draft_returns_false_when_submission_missing(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn(null);

        $service = new designer_service($mockSubmissions, null, null, null, null, null, null);
        $this->assertFalse($service->cancel_draft($jobid, $userid));
    }

    public function test_cancel_draft_returns_false_when_wrong_user(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $submission = (object) [
            'userid' => $userid + 1,
            'courseid' => 10,
            'remotejobid' => null,
            'status' => workflow_constants::SUBMISSION_STATUS_SYNCING_FILES,
        ];

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $service = new designer_service($mockSubmissions, null, null, null, null, null, null);
        $this->assertFalse($service->cancel_draft($jobid, $userid));
    }

    /**
     * Cancel during file upload: submission has courseid, status syncing_files.
     * Desired: draft course deleted, submission cleared; full rollback so file sync disabled.
     */
    public function test_cancel_draft_during_file_upload_deletes_draft_clears_submission_disables_sync(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 42;
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => null,
            'status' => workflow_constants::SUBMISSION_STATUS_SYNCING_FILES,
        ];

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->once())->method('delete_submission')->with($jobid, $userid);

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn(null);
        $mockStructures->expects($this->once())->method('delete_by_jobid')->with($jobid);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())
            ->method('delete_draft_course')
            ->with($courseid);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->once())
            ->method('disable_sync')
            ->with($courseid, $userid, true);

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, null, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));
    }

    /**
     * Cancel during structure generation: remotejobid set, courseid set, no structure yet.
     * Desired: draft deleted, cancel_job(remotejobid), disable_sync (full rollback), submission cleared.
     */
    public function test_cancel_draft_during_structure_generation_cancels_remote_job_and_disables_sync(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 100;
        $remotejobid = 'remote-structure-uuid';
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => $remotejobid,
            'status' => workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE,
        ];

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->once())->method('delete_submission')->with($jobid, $userid);

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn(null);
        $mockStructures->expects($this->once())->method('delete_by_jobid')->with($jobid);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())->method('delete_draft_course')->with($courseid);

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->once())->method('cancel_job')->with($remotejobid)->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->once())
            ->method('disable_sync')
            ->with($courseid, $userid, true);

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));
    }

    /**
     * Cancel during content generation (quick or normal): structure already saved.
     * Desired: keep progress for resume (draft + structure + submission preserved);
     * cancel in-flight remote jobs only; no disable_sync.
     */
    public function test_cancel_draft_during_content_generation_structure_exists_content_only_rollback(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 200;
        $remotejobid = 'remote-structure-uuid';
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => $remotejobid,
            'status' => workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE,
        ];
        $savedstructure = json_encode(['course_structure' => ['title' => 'Test', 'sections' => []]]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn($savedstructure);
        $mockStructures->expects($this->never())->method('delete_by_jobid');

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->never())->method('delete_draft_course');

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->once())->method('cancel_job')->with($remotejobid)->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->never())->method('disable_sync');

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));
    }

    /**
     * Cancel during finalize (module fill in progress): current_fill_jobid in cache is cancelled first.
     */
    public function test_cancel_draft_during_content_fill_cancels_fill_job_then_structure_job(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 201;
        $remotejobid = 'remote-structure-uuid';
        $filljobid = 'remote-fill-module-uuid';
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => $remotejobid,
            'status' => workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE,
        ];
        $savedstructure = json_encode(['course_structure' => ['title' => 'Test', 'sections' => []]]);

        $cache = \cache::make('block_dixeo_designer', 'finalize_progress');
        $cache->set($jobid, [
            'phase' => workflow_constants::FINALIZE_PHASE_GENERATING_CONTENT,
            'section_index' => 1,
            'section_total' => 2,
            'current_fill_jobid' => $filljobid,
        ]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn($savedstructure);
        $mockStructures->expects($this->never())->method('delete_by_jobid');

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->never())->method('delete_draft_course');

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->exactly(2))
            ->method('cancel_job')
            ->withConsecutive([$this->identicalTo($filljobid)], [$this->identicalTo($remotejobid)])
            ->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->never())->method('disable_sync');

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));

        $progress = $cache->get($jobid);
        $this->assertIsArray($progress);
        $this->assertTrue(!empty($progress['cancelled']));
    }

    /**
     * Cancel during finalizing structure: same as content generation — keep progress for resume.
     */
    public function test_cancel_draft_during_finalizing_structure_content_only_rollback(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 300;
        $remotejobid = 'remote-finalize-uuid';
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => $remotejobid,
            'status' => workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE,
        ];
        $savedstructure = json_encode(['course_structure' => ['title' => 'Final', 'sections' => []]]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn($savedstructure);
        $mockStructures->expects($this->never())->method('delete_by_jobid');

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->never())->method('delete_draft_course');

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->once())->method('cancel_job')->with($remotejobid)->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->never())->method('disable_sync');

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));
    }

    /**
     * Cancel with no structure (full rollback): DB submission cleared, remote job cancelled, file sync disabled.
     */
    public function test_cancel_draft_no_structure_full_rollback_calls_disable_sync(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 50;
        $remotejobid = 'remote-any';
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => $remotejobid,
            'status' => workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE,
        ];

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->once())->method('delete_submission')->with($jobid, $userid);

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn(null);
        $mockStructures->expects($this->once())->method('delete_by_jobid')->with($jobid);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())->method('delete_draft_course')->with($courseid);

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->once())->method('cancel_job')->with($remotejobid)->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->once())->method('disable_sync')->with($courseid, $userid, true);

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));
    }

    /**
     * Integration-style: cancel during file upload leaves submission in draft with no courseid/remotejobid in DB.
     */
    public function test_cancel_draft_during_upload_db_state_after_rollback(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;

        $submissions = new \block_dixeo_designer\service\submission\service();
        $structures = new \block_dixeo_designer\service\structure\repository();
        $submissions->save_submission($jobid, $userid, 'Prompt', null);
        $sub = $submissions->get_submission($jobid);
        $course = $this->getDataGenerator()->create_course();
        $submissions->set_draft_and_remote_job($sub, $course->id, 'remote-1');
        $submissions->mark_status($sub, workflow_constants::SUBMISSION_STATUS_SYNCING_FILES);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())->method('delete_draft_course')->with($course->id);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->once())->method('disable_sync')->with($course->id, $userid, true);

        $service = new designer_service(
            $submissions, null, $structures, $mockCourseCreation, null, null, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));

        $after = $submissions->get_submission($jobid);
        $this->assertNull($after);
    }

    /**
     * Integration-style: cancel during content generation (structure exists) preserves resume state.
     */
    public function test_cancel_draft_during_content_generation_structure_remains_in_db(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;

        $submissions = new \block_dixeo_designer\service\submission\service();
        $structures = new \block_dixeo_designer\service\structure\repository();
        $submissions->save_submission($jobid, $userid, 'Prompt', null);
        $sub = $submissions->get_submission($jobid);
        $course = $this->getDataGenerator()->create_course();
        $submissions->set_draft_and_remote_job($sub, $course->id, 'remote-2');

        $structure = ['course_structure' => ['title' => 'Kept', 'sections' => []]];
        $structures->save_structure_version($jobid, $userid, 'v1', $structure);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->never())->method('delete_draft_course');

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->once())->method('cancel_job')->with('remote-2')->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->never())->method('disable_sync');

        $service = new designer_service(
            $submissions, null, $structures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid));

        $after = $submissions->get_submission($jobid);
        $this->assertNotNull($after);
        $this->assertSame((int) $course->id, (int) $after->courseid);
        $this->assertSame('remote-2', (string) $after->remotejobid);

        $json = $structures->get_latest_structure($jobid);
        $this->assertNotNull($json);
        $decoded = json_decode($json, true);
        $this->assertSame('Kept', $decoded['course_structure']['title'] ?? null);
    }

    public function test_cancel_draft_footer_hard_reset_disables_sync_and_deletes_structure_even_when_saved(): void {
        $jobid = 'job-' . uniqid();
        $userid = $this->user->id;
        $courseid = 301;
        $remotejobid = 'remote-footer-uuid';
        $submission = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'remotejobid' => $remotejobid,
            'status' => workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE,
        ];
        $savedstructure = json_encode(['course_structure' => ['title' => 'Final', 'sections' => []]]);

        $mockSubmissions = $this->createMock(\block_dixeo_designer\service\submission\service::class);
        $mockSubmissions->method('get_submission')->with($jobid)->willReturn($submission);
        $mockSubmissions->expects($this->once())->method('delete_submission')->with($jobid, $userid);

        $mockStructures = $this->createMock(\block_dixeo_designer\service\structure\repository::class);
        $mockStructures->method('get_latest_structure')->with($jobid)->willReturn($savedstructure);
        $mockStructures->expects($this->once())->method('delete_by_jobid')->with($jobid);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())->method('delete_draft_course')->with($courseid);

        $mockJobService = $this->createMock(\local_dixeo\service\job_service::class);
        $mockJobService->expects($this->once())->method('cancel_job')->with($remotejobid)->willReturn([]);

        $mockFileSync = $this->createMock(\local_dixeo\service\file_sync_service::class);
        $mockFileSync->expects($this->once())->method('disable_sync')->with($courseid, $userid, true);

        $service = new designer_service(
            $mockSubmissions, null, $mockStructures, $mockCourseCreation, null, $mockJobService, $mockFileSync
        );

        $this->assertTrue($service->cancel_draft($jobid, $userid, true));
    }
}

