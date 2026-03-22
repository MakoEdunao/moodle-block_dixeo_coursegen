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
// along with Moodle. If not, see <http://www.moodle.org/license>.

namespace block_dixeo_designer\service\structure;

defined('MOODLE_INTERNAL') || die();

/**
 * Repository for designer structure versions (block_dixeo_designer_structure).
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository {

    /** @var string Table name. */
    private const TABLE = 'block_dixeo_designer_structure';

    /**
     * Get the latest saved structure JSON for a job.
     *
     * @param string $jobid
     * @return string|null JSON string or null
     */
    public function get_latest_structure(string $jobid): ?string {
        global $DB;

        $records = $DB->get_records(
            self::TABLE,
            ['jobid' => $jobid],
            'timecreated DESC',
            'structure',
            0,
            1
        );
        $record = reset($records);
        return $record ? $record->structure : null;
    }

    /**
     * Persist a structure version.
     *
     * @param string $jobid
     * @param int $userid
     * @param string $description
     * @param array $result
     * @return void
     */
    public function save_structure_version(string $jobid, int $userid, string $description, array $result): void {
        global $DB;

        $record = (object) [
            'jobid' => $jobid,
            'userid' => $userid,
            'description' => $description,
            'structure' => json_encode($result),
            'version' => date('YmdHis') . '-' . random_int(1000, 9999),
            'timecreated' => time(),
        ];
        $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Delete all stored structure versions for a job.
     *
     * @param string $jobid
     * @return int Number of deleted records.
     */
    public function delete_by_jobid(string $jobid): int {
        global $DB;
        return $DB->delete_records(self::TABLE, ['jobid' => $jobid]);
    }
}
