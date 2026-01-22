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
 * Review page for course generation structure.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

global $PAGE, $OUTPUT;

$jobid = required_param('id', PARAM_TEXT);

// Set up the page.
$urlparams = ['id' => $jobid];
$PAGE->set_url(new moodle_url('/blocks/dixeo_coursegen/review.php', $urlparams));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'block_dixeo_coursegen'));
$PAGE->set_heading(''); // Empty heading (no page title)

echo $OUTPUT->header();

// Render the review template.
echo $OUTPUT->render_from_template('block_dixeo_coursegen/review', [
    'jobid' => $jobid,
    'loading' => get_string('editor_loading', 'block_dixeo_coursegen'),
    'save' => get_string('editor_save', 'block_dixeo_coursegen'),
    'cancel' => get_string('editor_cancel', 'block_dixeo_coursegen'),
    'reload' => get_string('editor_reload', 'block_dixeo_coursegen'),
    'save_now' => get_string('editor_save_now', 'block_dixeo_coursegen'),
    'autosave_in' => get_string('editor_autosave_in', 'block_dixeo_coursegen'),
    'undo' => get_string('editor_undo', 'block_dixeo_coursegen'),
    'redo' => get_string('editor_redo', 'block_dixeo_coursegen'),
]);

echo $OUTPUT->footer();
