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

namespace block_dixeo_designer\external\draft\dto;

defined('MOODLE_INTERNAL') || die();

/**
 * DTO for block_dixeo_designer cancel_draft external response.
 */
final class cancel_draft_result {
    public function __construct(
        public bool $success
    ) {
    }

    public static function from_bool(bool $ok): self {
        return new self($ok);
    }

    public function to_array(): array {
        return [
            'success' => $this->success,
        ];
    }
}

