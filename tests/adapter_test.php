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
use block_dixeo_designer\adapter\designer_persistence_adapter;

/**
 * Tests for the designer persistence adapter (local_dixeo interface implementation).
 *
 * @package    block_dixeo_designer
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_dixeo_designer\adapter\designer_persistence_adapter
 */
final class adapter_test extends advanced_testcase {

    /** @var designer_persistence_adapter */
    private $adapter;

    /** @var \stdClass */
    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->adapter = new designer_persistence_adapter();
    }

    public function test_get_submission_returns_null_when_missing(): void {
        $this->assertNull($this->adapter->get_submission('job-' . uniqid()));
    }

    public function test_get_or_create_submission_creates_and_returns_submission(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'My prompt', 'tpl-1');
        $this->assertInstanceOf(\stdClass::class, $sub);
        $this->assertEquals($jobid, $sub->jobid);
        $this->assertEquals($this->user->id, $sub->userid);
        $this->assertEquals('My prompt', $sub->prompt);
        $this->assertEquals('tpl-1', $sub->templateid);
        $this->assertEquals('draft', $sub->status);
        $this->assertNull($sub->courseid);
        $this->assertNull($sub->remotejobid);
        $this->assertNotEmpty($sub->id);
    }

    public function test_get_or_create_submission_updates_prompt_and_template_on_existing(): void {
        $jobid = 'job-' . uniqid();
        $this->adapter->get_or_create_submission($jobid, $this->user->id, 'First', null);
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'Second', 'tpl-2');
        $this->assertEquals('Second', $sub->prompt);
        $this->assertEquals('tpl-2', $sub->templateid);
    }

    public function test_get_submission_returns_existing_submission(): void {
        $jobid = 'job-' . uniqid();
        $this->adapter->get_or_create_submission($jobid, $this->user->id, 'Prompt', null);
        $sub = $this->adapter->get_submission($jobid);
        $this->assertNotNull($sub);
        $this->assertEquals($jobid, $sub->jobid);
        $this->assertEquals('Prompt', $sub->prompt);
    }

    public function test_update_submission_persists_changes(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'P', null);
        $sub->prompt = 'Updated prompt';
        $this->adapter->update_submission($sub);
        $fetched = $this->adapter->get_submission($jobid);
        $this->assertEquals('Updated prompt', $fetched->prompt);
    }

    public function test_set_draft_and_remote_job(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->adapter->set_draft_and_remote_job($sub, $course->id, 'remote-uuid-123');
        $fetched = $this->adapter->get_submission($jobid);
        $this->assertEquals($course->id, $fetched->courseid);
        $this->assertEquals('remote-uuid-123', $fetched->remotejobid);
        $this->assertEquals('generating_structure', $fetched->status);
    }

    public function test_attach_course(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->adapter->attach_course($sub, $course->id);
        $fetched = $this->adapter->get_submission($jobid);
        $this->assertEquals($course->id, $fetched->courseid);
        $this->assertEquals('course_created', $fetched->status);
    }

    public function test_clear_course(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->adapter->set_draft_and_remote_job($sub, $course->id, 'remote-1');
        $this->adapter->clear_course($sub);
        $fetched = $this->adapter->get_submission($jobid);
        $this->assertNull($fetched->courseid);
        $this->assertNull($fetched->remotejobid);
        $this->assertEquals('draft', $fetched->status);
    }

    public function test_get_submission_files_returns_empty_array_when_no_files(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'P', null);
        $files = $this->adapter->get_submission_files((int) $sub->id);
        $this->assertIsArray($files);
        $this->assertEmpty($files);
    }

    public function test_copy_submission_files_to_course_no_files_does_not_throw(): void {
        $jobid = 'job-' . uniqid();
        $sub = $this->adapter->get_or_create_submission($jobid, $this->user->id, 'P', null);
        $course = $this->getDataGenerator()->create_course();
        $this->adapter->copy_submission_files_to_course((int) $sub->id, $course->id, $this->user->id);
        $this->assertTrue(true);
    }

    public function test_save_structure_version_inserts_record(): void {
        global $DB;
        $jobid = 'job-' . uniqid();
        $result = ['data' => ['title' => 'Test', 'sections' => []]];
        $this->adapter->save_structure_version($jobid, $this->user->id, 'Desc', $result);
        $count = $DB->count_records('block_dixeo_designer_structure', ['jobid' => $jobid, 'userid' => $this->user->id]);
        $this->assertSame(1, $count);
        $record = $DB->get_record('block_dixeo_designer_structure', ['jobid' => $jobid]);
        $this->assertEquals('Desc', $record->description);
        $decoded = json_decode($record->structure, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('data', $decoded);
    }
}
