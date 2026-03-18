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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace block_dixeo_designer\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Creates and finalizes Moodle courses for the designer workflow (block-owned).
 *
 * Draft courses use idnumber prefix dixeo_draft_*. Uses local_dixeo for file sync and module generation API only.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class designer_course_creation_service {

    /** @var string idnumber prefix for draft courses (cleanup matches this). */
    public const IDNUMBER_DRAFT_PREFIX = 'dixeo_draft_';

    /**
     * Create an empty draft course for structure generation.
     *
     * @param int $userid
     * @return \stdClass
     */
    public function create_draft_course(int $userid): \stdClass {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/course/lib.php');

        $categoryid = $this->resolve_category_id();
        $idnumber = self::IDNUMBER_DRAFT_PREFIX . gmdate('Ymd_His');
        $shortname = 'draft-' . gmdate('Ymd-His');

        $candidate = $shortname;
        $suffix = 1;
        while ($DB->record_exists('course', ['shortname' => $candidate])) {
            $candidate = $shortname . '-' . $suffix++;
        }
        $shortname = $candidate;

        $fullname = $this->get_draft_course_name();

        $coursedata = (object) [
            'category' => $categoryid,
            'fullname' => $fullname,
            'shortname' => $shortname,
            'idnumber' => $idnumber,
            'summary' => '',
            'summaryformat' => FORMAT_HTML,
            'format' => 'topics',
            'lang' => '',
            'newsitems' => 0,
            'visible' => 1,
            'enablecompletion' => 0,
            'startdate' => time(),
            'numsections' => 1,
        ];

        $course = \create_course($coursedata);
        $this->enrol_user((int) $course->id, $userid);

        return $course;
    }

    /**
     * Delete a draft course by id (only if idnumber matches dixeo_draft_*).
     *
     * @param int $courseid
     * @return bool
     */
    public function delete_draft_course(int $courseid): bool {
        global $CFG, $DB;

        $course = $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING);
        if (!$course) {
            return false;
        }
        if (strpos($course->idnumber ?? '', self::IDNUMBER_DRAFT_PREFIX) !== 0) {
            return true;
        }

        require_once($CFG->dirroot . '/course/lib.php');
        return \delete_course($course, false);
    }

    /**
     * Finalize a draft course after structure is ready: rename, sections, materialize modules.
     *
     * @param int $courseid
     * @param array $result Structure API result (course_structure.title, course_structure.sections, etc.)
     * @param int $userid
     * @return \stdClass
     */
    public function finalize_draft_course(int $courseid, array $result, int $userid): \stdClass {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/course/lib.php');

        $data = $result['course_structure'] ?? [];
        $sections = $data['sections'] ?? [];
        $title = $data['title'] ?? get_string('blocktitle', 'block_dixeo_designer');

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $course->fullname = $title;
        $course->shortname = $this->generate_unique_shortname($title);
        $course->summary = $data['summary'] ?? '';
        $course->summaryformat = FORMAT_HTML;
        $course->format = $data['format'] ?? 'topics';
        $course->numsections = count($sections);
        $DB->update_record('course', $course);

        foreach (array_values($sections) as $index => $sectiondata) {
            $sectionnumber = $index + 1;
            course_create_sections_if_missing($courseid, [$sectionnumber]);

            $section = $DB->get_record('course_sections', [
                'course' => $courseid,
                'section' => $sectionnumber,
            ], '*', MUST_EXIST);
            $section->name = $sectiondata['title'] ?? '';
            $section->summary = $sectiondata['summary'] ?? '';
            $section->summaryformat = FORMAT_HTML;
            $DB->update_record('course_sections', $section);
        }

        $this->materialize_structure_modules($courseid, $sections);

        return $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    }

    /**
     * Enable file sync for the draft course and wait for initial sync to complete.
     *
     * Caller must copy submission files into the course before calling this.
     *
     * @param int $courseid
     * @param int $userid
     * @return void
     */
    public function enable_draft_file_sync_and_wait(int $courseid, int $userid): void {
        $filesync = \local_dixeo\external\service_factory::get_file_sync_service();
        $filesync->enable_sync($courseid, $userid);
        $filesync->trigger_sync($courseid);
        $this->wait_for_initial_file_sync($filesync, $courseid);
    }

    /**
     * Delete draft courses older than the given seconds (by startdate).
     *
     * @param int $olderthanseconds
     * @return int Number of courses deleted.
     */
    public function cleanup_draft_courses_older_than(int $olderthanseconds): int {
        global $DB;

        $prefix = self::IDNUMBER_DRAFT_PREFIX;
        $olderthan = time() - $olderthanseconds;

        $courses = $DB->get_recordset_sql(
            "SELECT id, idnumber FROM {course}
             WHERE " . $DB->sql_like('idnumber', ':prefix', false, false) . "
             AND startdate < :olderthan",
            ['prefix' => $prefix . '%', 'olderthan' => $olderthan]
        );

        $deleted = 0;
        foreach ($courses as $course) {
            if ($this->delete_draft_course((int) $course->id)) {
                $deleted++;
            }
        }
        $courses->close();

        return $deleted;
    }

    private function get_draft_course_name(): string {
        return get_string('designer_draft_course_name', 'block_dixeo_designer');
    }

    private function resolve_category_id(): int {
        $categoryname = get_config('block_dixeo_designer', 'categoryname');
        if (empty($categoryname)) {
            $categoryname = get_string('default_categoryname', 'block_dixeo_designer');
        }

        global $DB;

        $existingid = $DB->get_field('course_categories', 'id', ['name' => $categoryname, 'parent' => 0]);
        if ($existingid) {
            return (int) $existingid;
        }

        $created = \core_course_category::create([
            'name' => $categoryname,
            'parent' => 0,
        ]);

        return (int) $created->id;
    }

    private function generate_unique_shortname(string $basename): string {
        global $DB;

        $shortname = trim(preg_replace('/\s+/', '-', \core_text::strtolower($basename)), '-');
        $shortname = clean_param($shortname, PARAM_ALPHANUMEXT);
        if ($shortname === '') {
            $shortname = 'dixeo-course';
        }

        $candidate = $shortname;
        $suffix = 1;
        while ($DB->record_exists('course', ['shortname' => $candidate])) {
            $candidate = $shortname . '-' . $suffix++;
        }

        return $candidate;
    }

    private function enrol_user(int $courseid, int $userid): void {
        global $CFG;

        $enrol = enrol_get_plugin('manual');
        if (!$enrol) {
            return;
        }

        foreach (enrol_get_instances($courseid, false) as $instance) {
            if ($instance->enrol !== 'manual') {
                continue;
            }

            $enrol->enrol_user($instance, $userid, $CFG->creatornewroleid);
        }
    }

    private function wait_for_initial_file_sync(\local_dixeo\service\file_sync_service $filesync, int $courseid): void {
        $status = $filesync->get_status($courseid);
        if ($status->filestotal === 0) {
            return;
        }

        $deadline = time() + 120;
        while (time() < $deadline) {
            $status = $filesync->poll_status($courseid);
            if ($status->status === 'synchronized' || $status->status === 'none') {
                return;
            }
            if ($status->status === 'error') {
                throw new \moodle_exception('designer_filesyncfailed', 'block_dixeo_designer', '', $status->errormessage);
            }
            sleep(2);
        }

        throw new \moodle_exception('designer_filesynctimeout', 'block_dixeo_designer');
    }

    private function materialize_structure_modules(int $courseid, array $sections): void {
        $moduleservice = \local_dixeo\external\service_factory::get_module_generation_service();
        $jobservice = \local_dixeo\external\service_factory::get_job_service();

        foreach (array_values($sections) as $sectionindex => $sectiondata) {
            $sectionnumber = $sectionindex + 1;
            foreach (($sectiondata['modules'] ?? []) as $module) {
                $modulename = $module['type'] ?? 'page';
                $title = trim((string) ($module['title'] ?? ''));
                $summary = trim((string) ($module['summary'] ?? ''));
                $instructions = $this->build_module_instructions($module, $sectiondata);

                $operation = $moduleservice->submit_fill_job_for_course(
                    $modulename,
                    $instructions,
                    $courseid,
                    $sectionnumber,
                    $title !== '' ? $title : get_string('designer_new_module_title', 'block_dixeo_designer'),
                    $summary
                );

                $waitResult = $jobservice->wait_for_job($operation->jobid, 'fill_module');
                if (!$waitResult->is_completed()) {
                    throw new \moodle_exception(
                        'designer_module_timeout',
                        'block_dixeo_designer',
                        '',
                        $title !== '' ? $title : $modulename
                    );
                }

                $result = \local_dixeo\external\create_module_from_job::execute(
                    $operation->jobid,
                    $courseid,
                    $sectionnumber,
                    null,
                    $title !== '' ? $title : null,
                    $summary !== '' ? format_text($summary, FORMAT_PLAIN) : null
                );

                if (empty($result['success'])) {
                    $errmsg = !empty($result['errormessage']) ? $result['errormessage'] : 'Unknown error';
                    $debuginfo = isset($result['errorcode']) ? $result['errorcode'] . ': ' . $errmsg : $errmsg;
                    throw new \moodle_exception(
                        'error_generation_failed',
                        'block_dixeo_designer',
                        '',
                        $errmsg,
                        $debuginfo
                    );
                }
            }
        }
    }

    private function build_module_instructions(array $module, array $section): string {
        $parts = [];

        if (!empty($module['instructions'])) {
            $parts[] = trim((string) $module['instructions']);
        }
        $modulesummary = trim((string) ($module['summary'] ?? ''));
        if ($modulesummary !== '') {
            $parts[] = 'Module summary: ' . $modulesummary;
        }
        if (!empty($section['title'])) {
            $parts[] = 'Section: ' . trim((string) $section['title']);
        }

        $instructions = trim(implode("\n\n", array_filter($parts)));
        if ($instructions !== '') {
            return $instructions;
        }

        return get_string('designer_default_module_prompt', 'block_dixeo_designer');
    }
}
