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
 * DTO for block_dixeo_designer get_finalize_progress external response.
 */
final class finalize_progress_result {
    public function __construct(
        public string $phase,
        public int $section_index,
        public int $section_total,
        public int $module_index,
        public int $module_total,
        public int $courseid,
        public string $coursename
    ) {
    }

    public static function from_cache_array(array $data): self {
        return new self(
            (string) ($data['phase'] ?? ''),
            (int) ($data['section_index'] ?? 0),
            (int) ($data['section_total'] ?? 0),
            (int) ($data['module_index'] ?? 0),
            (int) ($data['module_total'] ?? 0),
            (int) ($data['courseid'] ?? 0),
            (string) ($data['coursename'] ?? '')
        );
    }

    public function to_array(): array {
        return [
            'phase' => $this->phase,
            'section_index' => $this->section_index,
            'section_total' => $this->section_total,
            'module_index' => $this->module_index,
            'module_total' => $this->module_total,
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
        ];
    }
}

