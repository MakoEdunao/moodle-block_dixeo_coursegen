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

        $mockSubmissions = $this->createMock(\block_dixeo_designer\submission_service::class);
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

        $mockFiles = $this->createMock(\block_dixeo_designer\submission_file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\structure_repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);

        $expectedResult = json_decode($structureJson, true);
        $expectedResult = is_array($expectedResult) ? $expectedResult : [];

        $mockCourseCreation->expects($this->once())
            ->method('finalize_draft_course')
            ->with(123, $expectedResult, $userid, $jobid)
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

        $mockSubmissions = $this->createMock(\block_dixeo_designer\submission_service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);
        $mockSubmissions->expects($this->never())->method('attach_course');
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockFiles = $this->createMock(\block_dixeo_designer\submission_file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\structure_repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->never())->method('finalize_draft_course');

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation);

        $course = $service->finalize_course($jobid, $userid, false);

        $this->assertNull($course);
    }

    public function test_finalize_course_does_not_delete_submission_when_course_finalization_fails(): void {
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

        $mockSubmissions = $this->createMock(\block_dixeo_designer\submission_service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);

        $mockSubmissions->expects($this->never())->method('attach_course');
        $mockSubmissions->expects($this->never())->method('delete_submission');

        $mockFiles = $this->createMock(\block_dixeo_designer\submission_file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\structure_repository::class);
        $mockStructures->method('get_latest_structure')
            ->with($jobid)
            ->willReturn($structureJson);

        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);
        $mockCourseCreation->expects($this->once())
            ->method('finalize_draft_course')
            ->willReturn((object) []);

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation);

        $course = $service->finalize_course($jobid, $userid, true);

        $this->assertNull($course);
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

        $mockSubmissions = $this->createMock(\block_dixeo_designer\submission_service::class);
        $mockSubmissions->method('get_submission')
            ->with($jobid)
            ->willReturn($submission);

        $mockSubmissions->expects($this->once())
            ->method('set_draft_and_remote_job')
            ->with($this->identicalTo($submission), 55, 'remote-uuid');

        $mockSubmissions->expects($this->once())
            ->method('mark_status')
            ->with($this->identicalTo($submission), workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE);

        $mockFiles = $this->createMock(\block_dixeo_designer\submission_file_service::class);
        $mockStructures = $this->createMock(\block_dixeo_designer\structure_repository::class);
        $mockCourseCreation = $this->createMock(designer_course_creation_service::class);

        $mockRemoteApi = $this->createMock(\block_dixeo_designer\service\dixeo_remote_adapter::class);
        $mockRemoteApi->expects($this->once())
            ->method('submit_course_structure_generation')
            ->with($expectedInstructions, null, 55)
            ->willReturn((object) ['jobid' => 'remote-uuid']);

        $service = new designer_service($mockSubmissions, $mockFiles, $mockStructures, $mockCourseCreation, $mockRemoteApi);

        $result = $service->submit_structure_generation($jobid, $userid);

        $this->assertSame('remote-uuid', $result->remotejobid);
        $this->assertSame(55, (int) $result->courseid);
    }
}

