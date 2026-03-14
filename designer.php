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

/**
 * Designer page for course design structure.
 *
 * @package    block_dixeo_designer
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

global $PAGE, $OUTPUT;

$jobid = optional_param('id', '', PARAM_TEXT);
$hasexistingjob = ($jobid !== '');
$coursedescription = optional_param('course_description', '', PARAM_TEXT);
$templateid = optional_param(
    'templateid',
    \block_dixeo_designer\course_template_helper::get_selected_course_template(),
    PARAM_TEXT
);

if (!$hasexistingjob) {
    $jobid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

// Set up the page.
$urlparams = [];
if ($hasexistingjob) {
    $urlparams['id'] = $jobid;
}
$PAGE->set_url(new moodle_url('/blocks/dixeo_designer/designer.php', $urlparams));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'block_dixeo_designer'));
$PAGE->set_heading(''); // Empty heading (no page title)

echo $OUTPUT->header();

if (!$hasexistingjob) {
    // Render the designer start page when opened from the Courses admin section.
    $templateoptions = \block_dixeo_designer\course_template_helper::get_course_template_options($templateid);

    echo html_writer::div($OUTPUT->render_from_template('block_dixeo_designer/course_designer', [
        'course_description' => $coursedescription,
        'job_id' => $jobid,
        'has_template_options' => !empty($templateoptions),
        'template_options' => $templateoptions,
    ]), 'block_dixeo_designer');
} else {
    // Render the structure designer for an existing job.
    echo $OUTPUT->render_from_template('block_dixeo_designer/review', [
        'jobid' => $jobid,
        'loading' => get_string('designer_loading', 'block_dixeo_designer'),
        'save' => get_string('designer_save', 'block_dixeo_designer'),
        'cancel' => get_string('designer_cancel', 'block_dixeo_designer'),
        'reload' => get_string('designer_reload', 'block_dixeo_designer'),
        'save_now' => get_string('designer_save_now', 'block_dixeo_designer'),
        'autosave_in' => get_string('designer_autosave_in', 'block_dixeo_designer'),
        'undo' => get_string('designer_undo', 'block_dixeo_designer'),
        'redo' => get_string('designer_redo', 'block_dixeo_designer'),
    ]);
}

echo $OUTPUT->footer();
