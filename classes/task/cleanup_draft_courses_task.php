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

namespace block_dixeo_designer\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task: delete draft courses (idnumber dixeo_draft_*) older than 1 hour.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_draft_courses_task extends \core\task\scheduled_task {

    /**
     * Task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_cleanup_draft_courses', 'block_dixeo_designer');
    }

    /**
     * Delete draft courses older than 1 hour (delegates to local_dixeo).
     */
    public function execute(): void {
        $creation = new \block_dixeo_designer\service\designer_course_creation_service();
        $deleted = $creation->cleanup_draft_courses_older_than(3600);
        if ($deleted > 0) {
            mtrace("[block_dixeo_designer] Deleted {$deleted} draft course(s).");
        }
    }
}
