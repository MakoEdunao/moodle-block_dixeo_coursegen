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
use block_dixeo_designer\service\course_template_helper;
use local_dixeo\external\service_factory;
use local_dixeo\service\course_template_service;

/**
 * Tests for course template option selection behavior.
 *
 * @package    block_dixeo_designer
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_dixeo_designer\service\course_template_helper
 */
final class course_template_helper_test extends advanced_testcase {
    protected function tearDown(): void {
        service_factory::reset();
        parent::tearDown();
    }

    public function test_empty_selected_template_falls_back_to_config_default(): void {
        $this->resetAfterTest(true);

        set_config('coursetemplate', 'tpl-default', 'block_dixeo_designer');

        $mockservice = $this->createMock(course_template_service::class);
        $mockservice->method('is_configured')->willReturn(true);
        $mockservice->method('get_cached_choices')->willReturn([
            'tpl-default' => 'Default template',
            'tpl-alt' => 'Alternative template',
        ]);
        service_factory::set_test_course_template_service($mockservice);

        $options = course_template_helper::get_course_template_options('');

        $selected = array_values(array_filter($options, static function(array $option): bool {
            return !empty($option['selected']);
        }));

        $this->assertCount(1, $selected);
        $this->assertSame('tpl-default', $selected[0]['value']);
    }

    public function test_explicit_selected_template_takes_precedence_over_config_default(): void {
        $this->resetAfterTest(true);

        set_config('coursetemplate', 'tpl-default', 'block_dixeo_designer');

        $mockservice = $this->createMock(course_template_service::class);
        $mockservice->method('is_configured')->willReturn(true);
        $mockservice->method('get_cached_choices')->willReturn([
            'tpl-default' => 'Default template',
            'tpl-alt' => 'Alternative template',
        ]);
        service_factory::set_test_course_template_service($mockservice);

        $options = course_template_helper::get_course_template_options('tpl-alt');

        $selected = array_values(array_filter($options, static function(array $option): bool {
            return !empty($option['selected']);
        }));

        $this->assertCount(1, $selected);
        $this->assertSame('tpl-alt', $selected[0]['value']);
    }
}
