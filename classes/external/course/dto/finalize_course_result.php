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

namespace block_dixeo_designer\external\course\dto;

defined('MOODLE_INTERNAL') || die();

/**
 * DTO for block_dixeo_designer finalize_course external response.
 */
final class finalize_course_result {
    public function __construct(
        public int $courseid,
        public string $coursename
    ) {
    }

    public static function from_course(?object $course): self {
        if ($course === null) {
            return new self(0, '');
        }

        return new self((int) ($course->id ?? 0), (string) ($course->fullname ?? ''));
    }

    public function to_array(): array {
        return [
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
        ];
    }
}

