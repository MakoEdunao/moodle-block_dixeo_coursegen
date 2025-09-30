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
 * AJAX handler for course generation.
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

require_login();

$context = context_system::instance();
require_capability('block/dixeo_coursegen:create', $context);

// Unlock the session early to not stop other http requests.
\core\session\manager::write_close();

// Get required params.
$description = required_param('description', PARAM_TEXT);

$files = $_FILES['course_files'] ?? null;

try {
    $generator = new \block_dixeo_coursegen\course_generator($description, $files);
    $course = $generator->generate_course();

    http_response_code(200);

    $response = ['courseid' => $course->id, 'coursename' => $course->fullname];
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);

    $debug = '';
    if (debugging('', DEBUG_DEVELOPER)) {
        $debug = '<br><br>Error:<br>' . $e->getMessage() . '<br><br>' . $e->getTraceAsString();
    }

    echo json_encode([
        'error' => get_string('error_generation_failed', 'block_dixeo_coursegen') . $debug,
    ]);
}
