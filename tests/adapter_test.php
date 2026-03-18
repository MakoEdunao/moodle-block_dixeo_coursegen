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

use advanced_testcase;
use block_dixeo_designer\submission_service;
use block_dixeo_designer\structure_repository;

/**
 * Tests for submission_service and structure_repository (block-owned persistence).
 *
 * Replaces former adapter tests; persistence is now in the block.
 *
 * @package    block_dixeo_designer
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_dixeo_designer\submission_service
 * @covers     \block_dixeo_designer\structure_repository
 */
final class adapter_test extends advanced_testcase {

    /** @var submission_service */
    private $submissions;

    /** @var structure_repository */
    private $structures;

    /** @var \stdClass */
    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->submissions = new submission_service();
        $this->structures = new structure_repository();
    }

    public function test_get_submission_returns_null_when_missing(): void {
        $this->assertNull($this->submissions->get_submission('job-' . uniqid()));
    }

    public function test_get_or_create_submission_creates_and_returns_submission(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->submissions->save_submission($jobid, $this->user->id, 'My prompt', 'tpl-1');
        $this->assertInstanceOf(\stdClass::class, $sub);
        $this->assertEquals($jobid, $sub->jobid);
        $this->assertEquals($this->user->id, $sub->userid);
        $this->assertEquals('My prompt', $sub->prompt);
        $this->assertEquals('tpl-1', $sub->templateid);
        $this->assertEquals(workflow_constants::SUBMISSION_STATUS_DRAFT, $sub->status);
        $this->assertNull($sub->courseid);
        $this->assertNull($sub->remotejobid);
        $this->assertNotEmpty($sub->id);
    }

    public function test_get_or_create_submission_updates_prompt_and_template_on_existing(): void {
        $jobid = 'job-' . uniqid();
        $this->submissions->save_submission($jobid, $this->user->id, 'First', null);
        $sub = $this->submissions->save_submission($jobid, $this->user->id, 'Second', 'tpl-2');
        $this->assertEquals('Second', $sub->prompt);
        $this->assertEquals('tpl-2', $sub->templateid);
    }

    public function test_get_submission_returns_existing_submission(): void {
        $jobid = 'job-' . uniqid();
        $this->submissions->save_submission($jobid, $this->user->id, 'Prompt', null);
        $sub = $this->submissions->get_submission($jobid);
        $this->assertNotNull($sub);
        $this->assertEquals($jobid, $sub->jobid);
        $this->assertEquals('Prompt', $sub->prompt);
    }

    public function test_set_draft_and_remote_job(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->submissions->save_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->submissions->set_draft_and_remote_job($sub, $course->id, 'remote-uuid-123');
        $fetched = $this->submissions->get_submission($jobid);
        $this->assertEquals($course->id, $fetched->courseid);
        $this->assertEquals('remote-uuid-123', $fetched->remotejobid);
        $this->assertEquals(workflow_constants::SUBMISSION_STATUS_GENERATING_STRUCTURE, $fetched->status);
    }

    public function test_attach_course(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->submissions->save_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->submissions->attach_course($sub, $course->id);
        $fetched = $this->submissions->get_submission($jobid);
        $this->assertEquals($course->id, $fetched->courseid);
        $this->assertEquals(workflow_constants::SUBMISSION_STATUS_COURSE_CREATED, $fetched->status);
    }

    public function test_clear_course(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->submissions->save_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->submissions->set_draft_and_remote_job($sub, $course->id, 'remote-1');
        $this->submissions->clear_course($sub);
        $fetched = $this->submissions->get_submission($jobid);
        $this->assertNull($fetched->courseid);
        $this->assertNull($fetched->remotejobid);
        $this->assertEquals(workflow_constants::SUBMISSION_STATUS_DRAFT, $fetched->status);
    }

    public function test_save_structure_version_inserts_record(): void {
        $jobid = 'job-' . uniqid();
        $result = ['data' => ['title' => 'Test', 'sections' => []]];
        $this->structures->save_structure_version($jobid, $this->user->id, 'Desc', $result);
        $json = $this->structures->get_latest_structure($jobid);
        $this->assertNotNull($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('data', $decoded);
    }

    public function test_get_latest_structure_returns_null_when_missing(): void {
        $this->assertNull($this->structures->get_latest_structure('job-' . uniqid()));
    }
}
