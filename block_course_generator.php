<?php

/**
 * AI Course Generator block
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

class block_course_generator extends block_base {

    function init() {
        $this->title = get_string('blocktitle','block_course_generator');
    }

    function applicable_formats() {
        return array(
            'course-view' => false,
            'site' => true,
            'mod' => false,
            'my' => true,
        );
    }

    /**
     * It can be configured.
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    function specialization() {
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('blocktitle', 'block_course_generator');
    }

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $OUTPUT, $COURSE, $CFG;

        //note: do NOT include files at the top of this file
        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $context = [
            'logourl' => $OUTPUT->image_url('edunao', 'block_course_generator'),
        ];
        $text = $OUTPUT->render_from_template('block_course_generator/course_generator', $context);

        $this->content->text = $text;

        return $this->content;
    }
}

