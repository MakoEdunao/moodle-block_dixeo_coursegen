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
 * Dixeo Course Generator block
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_dixeo_coursegen extends block_base {

    public function init() {
        $this->title = get_string('blocktitle', 'block_dixeo_coursegen');
    }

    public function applicable_formats() {
        return [
            'course-view' => false,
            'site' => true,
            'mod' => false,
            'my' => true,
        ];
    }

    /**
     * It can be configured.
     *
     * @return bool
     */
    public function has_config() {
        global $CFG;

        $pluginmanager = \core_plugin_manager::instance();
        $localedai = $pluginmanager->get_plugin_info('local_edai');

        return !$localedai || !empty($CFG->overridegenerationurl);
    }

    public function specialization() {
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('blocktitle', 'block_dixeo_coursegen');
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function get_content() {
        global $OUTPUT, $COURSE, $CFG;

        // Note: do NOT include files at the top of this file.
        require_once($CFG->libdir . '/filelib.php');

        // We can exit early if the current user doesn't have the capability to create courses.
        if (!has_capability('moodle/course:create', $this->context)) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $coursedescription = optional_param('course_description', '', PARAM_TEXT);

        $this->content = new stdClass();
        $this->content->footer = '';

        // Check which generator is available.
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

        if (str_contains($generationurl, 'dixeo_coursegen')) {
            $configerrors = \block_dixeo_coursegen\course_generator::check_configuration();
            if ($configerrors) {
                $settingsurl = new \moodle_url($CFG->wwwroot . '/admin/settings.php', ['section' => 'blocksettingdixeo_coursegen']);
                $settingslink = \html_writer::link($settingsurl, $settingsurl->out());
                $notregistered = get_string('error_platform_not_registered', 'block_dixeo_coursegen', $settingslink);
                $this->content->text = $OUTPUT->notification($notregistered, 'notifyerror');
                return $this->content;
            }
        }

        $context = [
            'generationurl' => $generationurl,
            'course_description' => $coursedescription,
        ];
        $text = $OUTPUT->render_from_template('block_dixeo_coursegen/course_generator', $context);

        $this->content->text = $text;

        return $this->content;
    }
}

