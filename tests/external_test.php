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
require_once($CFG->dirroot . '/blocks/dixeo_designer/classes/external/generate_course.php');
require_once($CFG->dirroot . '/blocks/dixeo_designer/classes/external/get_structure_status.php');
require_once($CFG->dirroot . '/blocks/dixeo_designer/classes/external/finalize_course.php');
require_once($CFG->dirroot . '/blocks/dixeo_designer/classes/external/cancel_draft.php');

use advanced_testcase;
use block_dixeo_designer\external\generate_course;
use block_dixeo_designer\external\get_structure_status;
use block_dixeo_designer\external\finalize_course;
use block_dixeo_designer\external\cancel_draft;
use block_dixeo_designer\service\designer_service;
use block_dixeo_designer\service\designer_service_factory;

/**
 * External API tests with mocked block designer_service.
 *
 * @package    block_dixeo_designer
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_dixeo_designer\external\generate_course
 * @covers     \block_dixeo_designer\external\get_structure_status
 * @covers     \block_dixeo_designer\external\finalize_course
 * @covers     \block_dixeo_designer\external\cancel_draft
 */
final class external_test extends advanced_testcase {

    /** @var \stdClass */
    private $user;

    /** @var string */
    private $sesskey;

    /** @var \PHPUnit\Framework\MockObject\MockObject|designer_service */
    private $mockdesignerservice;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
        $this->assign_capability();
        $this->sesskey = sesskey();
        $_POST['sesskey'] = $this->sesskey;
        $this->mockdesignerservice = $this->getMockBuilder(designer_service::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['start_generation', 'get_structure_status', 'finalize_course', 'cancel_draft'])
            ->getMock();
        designer_service_factory::set_test_designer_service($this->mockdesignerservice);
    }

    protected function tearDown(): void {
        designer_service_factory::reset();
        parent::tearDown();
    }

    private function assign_capability(): void {
        $sysctx = \context_system::instance();
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('block/dixeo_designer:create', CAP_ALLOW, $roleid, $sysctx->id);
        role_assign($roleid, $this->user->id, $sysctx->id);
    }

    public function test_generate_course_returns_start_result_from_service(): void {
        $this->mockdesignerservice->method('start_generation')
            ->with('job-123', $this->user->id, 'My course', null)
            ->willReturn((object) ['courseid' => 42, 'remotejobid' => 'remote-uuid']);

        $result = generate_course::generate_course(
            'job-123',
            'My course',
            null,
            $this->sesskey,
            false
        );

        $this->assertSame(42, $result['courseid']);
        $this->assertSame('remote-uuid', $result['remotejobid']);
    }

    public function test_generate_course_passes_templateid_to_service(): void {
        $this->mockdesignerservice->method('start_generation')
            ->with('job-456', $this->user->id, 'Desc', 'template-uuid')
            ->willReturn((object) ['courseid' => 1, 'remotejobid' => 'r']);

        generate_course::generate_course('job-456', 'Desc', 'template-uuid', $this->sesskey, false);
    }

    public function test_generate_course_requires_sesskey(): void {
        $_POST['sesskey'] = 'wrong-sesskey';
        $this->expectException(\moodle_exception::class);
        generate_course::generate_course('job-1', 'D', null, 'wrong-sesskey', false);
    }

    public function test_get_structure_status_returns_status_from_service(): void {
        $this->mockdesignerservice->method('get_structure_status')
            ->with('job-1', $this->user->id)
            ->willReturn((object) [
                'status' => 'processing',
                'progress' => 50,
                'completed' => false,
                'failed' => false,
                'result' => null,
                'error' => null,
            ]);

        $result = get_structure_status::get_structure_status('job-1', $this->sesskey);

        $this->assertSame('processing', $result['status']);
        $this->assertSame(50, $result['progress']);
        $this->assertFalse($result['completed']);
        $this->assertFalse($result['failed']);
    }

    public function test_get_structure_status_completed_with_result(): void {
        $this->mockdesignerservice->method('get_structure_status')
            ->willReturn((object) [
                'status' => 'completed',
                'progress' => 100,
                'completed' => true,
                'failed' => false,
                'result' => ['data' => ['title' => 'Course']],
                'error' => null,
            ]);

        $result = get_structure_status::get_structure_status('job-1', $this->sesskey);

        $this->assertTrue($result['completed']);
        $this->assertIsString($result['result']);
        $this->assertSame(['data' => ['title' => 'Course']], json_decode($result['result'], true));
    }

    public function test_finalize_course_returns_course_when_createcourse_true(): void {
        $course = (object) ['id' => 10, 'fullname' => 'My Course'];
        $this->mockdesignerservice->method('finalize_course')
            ->with('job-1', $this->user->id, true)
            ->willReturn($course);

        $result = finalize_course::finalize_course('job-1', true, $this->sesskey);

        $this->assertSame(10, $result['courseid']);
        $this->assertSame('My Course', $result['coursename']);
    }

    public function test_finalize_course_returns_empty_when_structure_only(): void {
        $this->mockdesignerservice->method('finalize_course')
            ->with('job-1', $this->user->id, false)
            ->willReturn(null);

        $result = finalize_course::finalize_course('job-1', false, $this->sesskey);

        $this->assertSame(0, $result['courseid']);
        $this->assertSame('', $result['coursename']);
    }

    public function test_cancel_draft_returns_success_from_service(): void {
        $this->mockdesignerservice->method('cancel_draft')
            ->with('job-1', $this->user->id)
            ->willReturn(true);

        $result = cancel_draft::cancel_draft('job-1', $this->sesskey);

        $this->assertTrue($result['success']);
    }

    public function test_cancel_draft_returns_false_when_service_returns_false(): void {
        $this->mockdesignerservice->method('cancel_draft')
            ->willReturn(false);

        $result = cancel_draft::cancel_draft('job-unknown', $this->sesskey);

        $this->assertFalse($result['success']);
    }

    public function test_external_requires_create_capability(): void {
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($other);
        // $other has no block/dixeo_designer:create capability.

        $this->expectException(\required_capability_exception::class);
        generate_course::generate_course('job-1', 'D', null, sesskey(), false);
    }
}
