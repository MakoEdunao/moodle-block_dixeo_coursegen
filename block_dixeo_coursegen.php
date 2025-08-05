<?php

/**
 * AI Course Generator block
 *
 * @package    block_dixeo_coursegen
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

class block_dixeo_coursegen extends block_base {

    function init() {
        $this->title = get_string('blocktitle','block_dixeo_coursegen');
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
        return true;
    }

    function specialization() {
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('blocktitle', 'block_dixeo_coursegen');
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

        $coursedescription = optional_param('course_description', '', PARAM_TEXT);

        $this->content = new stdClass();
        $this->content->footer = '';

        // Check which generator is available
        $pluginmanager = \core_plugin_manager::instance();
        $localedai = $pluginmanager->get_plugin_info('local_edai');

        $generationurl = $CFG->wwwroot . '/local/edai/ajax/generate_course.php';
        if (!$localedai) {
            $generationurl = $CFG->wwwroot . '/blocks/dixeo_coursegen/ajax/generate_course.php';
        }

        // Add to config.php to override the generation processor URL.
        if (!empty($CFG->overridegenerationurl)) {
            $generationurl = $CFG->overridegenerationurl;
        }

        if (str_contains($generationurl, 'edai_course_generator_client')) {
            $configerrors = \block_dixeo_coursegen\course_generator::check_configuration();
            if ($configerrors) {
                $this->content->text = $OUTPUT->notification($configerrors, 'notifyerror');
                return $this->content;
            }
        }

        $context = [
            'logourl' => $OUTPUT->image_url('edunao', 'block_dixeo_coursegen'),
            'generationurl' => $generationurl,
            'course_description' => $coursedescription
        ];
        $text = $OUTPUT->render_from_template('block_dixeo_coursegen/course_generator', $context);

        $this->content->text = $text;

        return $this->content;
    }
}

