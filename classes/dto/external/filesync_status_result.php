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

namespace block_dixeo_designer\dto\external;

defined('MOODLE_INTERNAL') || die();

/**
 * DTO for block_dixeo_designer get_filesync_status external response.
 */
final class filesync_status_result {
    public function __construct(
        public string $status,
        public ?float $progresspercent,
        public ?int $filestotal,
        public ?int $filescompleted,
        public ?string $errormessage
    ) {
    }

    public static function from_service(object $status): self {
        return new self(
            (string) ($status->status ?? 'none'),
            isset($status->progresspercent) ? (float) $status->progresspercent : null,
            isset($status->filestotal) ? (int) $status->filestotal : null,
            isset($status->filescompleted) ? (int) $status->filescompleted : null,
            isset($status->errormessage) ? (string) $status->errormessage : null
        );
    }

    public function to_array(): array {
        return [
            'status' => $this->status,
            'progresspercent' => $this->progresspercent,
            'filestotal' => $this->filestotal,
            'filescompleted' => $this->filescompleted,
            'errormessage' => $this->errormessage,
        ];
    }
}

