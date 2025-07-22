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
    global $OUTPUT;

    $output = '';

    // Check if the request is being made inside an iframe.
    if (isset($_GET['component'])) {
        return $output; // Do not execute the hook inside iframes.
    }

    if (!isloggedin()) {
        return $output;
    }

    $generation = get_user_preferences('aigeneration', '');
    $expires = get_user_preferences('aigeneration_expires', 0);

    if (!empty($generation)) {
        if ($generation == 'inprogress') {
            if ($expires < time()) {
                // Generation should take this long, remove the loading widget after 5 minutes.
                set_user_preference('aigeneration', null);
            } else {
                // Course generation is in progress, display the loading widget
                $context = ['logourl' => $OUTPUT->image_url('edunao', 'block_course_generator')];
                $output = $OUTPUT->render_from_template('block_course_generator/widget', $context);
            }
        } else {
            // If the course generation is complete, display a success message
            $message = get_string('course_generated', 'block_course_generator', $generation);
            \core\notification::add($message, \core\notification::SUCCESS);
            set_user_preference('aigeneration', null);
        }
    }

    return $output;
}
