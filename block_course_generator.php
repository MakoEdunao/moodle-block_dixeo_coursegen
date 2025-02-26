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
        global $COURSE, $CFG;

        //note: do NOT include files at the top of this file
        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $text = html_writer::start_div('body');
        $text .= html_writer::tag('h3', get_string('heading', 'block_course_generator'), array('class' => 'text-center mb-4'));

        // Example prompts
        $prompts = [
            get_string('prompt1', 'block_course_generator'),
            get_string('prompt2', 'block_course_generator'),
            get_string('prompt3', 'block_course_generator'),
            get_string('prompt4', 'block_course_generator'),
            get_string('prompt5', 'block_course_generator'),
        ];
        $promptshtml = '';
        foreach ($prompts as $key => $prompt) {
            $promptshtml .= html_writer::tag('div', $prompt, array('class' => 'carousel-item' . ($key == 0 ? ' active' : '')));
        }

        // Slider Controls
        $controls = html_writer::tag(
            'a', 
            html_writer::tag('span', '<i class="fa fa-chevron-left fs-1"></i>', array('aria-hidden' => 'true')),
            array('class' => 'carousel-control-prev', 'href' => '#carouselControls', 'role' => 'button', 'data-slide' => 'prev')
        );
        $controls .= html_writer::tag(
            'a',
            html_writer::tag('span', '<i class="fa fa-chevron-right fs-1"></i>', array('aria-hidden' => 'true')),
            array('class' => 'carousel-control-next', 'href' => '#carouselControls', 'role' => 'button', 'data-slide' => 'next')
        );

        // Carousel Wrapper
        $text .= html_writer::start_div('carousel slide m-4 p-4 text-center', array('id' => 'carouselControls', 'data-ride' => 'carousel'));
        $text .= html_writer::div($promptshtml, 'carousel-inner') . $controls;
        $text .= html_writer::end_div();

        $text .= '
        <div id="edai_course_generator_form" class="container">
            <form>
                <div class="form-group position-relative mb-0">
                    <label for="course_description" class="d-none"></label>
                    <textarea id="course_description" name="course_description" class="form-control px-6" style="overflow-y: hidden; resize:none;" placeholder="Introduce el curso que deseas generar: tema, número de secciones y quiz si es necesario." data-initial-value=""></textarea>
                    <button id="generate_course" class="btn btn-secondary btn-circle position-absolute text-white" style="right: 10px; top: 50%; transform: translateY(-50%); width: 35px; height: 35px; border-radius: 50%; padding: 0;" title="Generar">
                        <img src="https://123.edunao.com/local/edai/img/logo_edunao.webp" alt="Logo" style="width: 32px; height: 32px;">
                    </button>
                    <label for="course_files" class="position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                        <i class="fa fa-paperclip fa-2x"></i>
                    </label>
                    <input type="file" id="course_files" name="course_files[]" class="form-control d-none" multiple="">
                </div>
                <div id="file_names" class="mt-1"></div>
            </form>

            <div id="loader" class="d-none justify-content-center align-items-center mt-5">
                <div class="spinner-border text-primary" role="status"></div>
                <span class="ml-3">Generando tu curso, por favor espera... (~1 minuto)</span>
            </div>
            <div id="success_message_container" class="d-none justify-content-center align-items-center mt-5"></div>
        </div>';

        $text .= html_writer::end_div();

        $this->content->text = $text;

        return $this->content;
    }
}

