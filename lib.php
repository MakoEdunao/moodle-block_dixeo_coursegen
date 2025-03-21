<?php

/**
 * AI Course Generator block
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

function block_course_generator_before_standard_top_of_body_html() {
    return before_top_of_body();
}

function before_top_of_body() {
    global $SESSION, $OUTPUT;

    $output = '';

    if (!isloggedin()) {
        return $output;
    }

    if (isset($SESSION->aigeneration)) {
        if ($SESSION->aigeneration == 'inprogress') {
            // Course generation is in progress, display the loading widget
            $context = [
                'logourl' => $OUTPUT->image_url('edunao', 'block_course_generator'),
            ];
            $output = $OUTPUT->render_from_template('block_course_generator/widget', $context);
        } else {
            // If the course generation is complete, display a success message
            $message = get_string('course_generated', 'block_course_generator', $SESSION->aigeneration);
            \core\notification::add($message, \core\notification::SUCCESS);
            unset($SESSION->aigeneration);
        }
    }

    return $output;
}
