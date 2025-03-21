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
 * Hook to allow plugins to add any elements to the top of the body.
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_generator;

/**
 * Hook to allow plugins to add any elements to the top of the body.
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output {
    /**
     * Callback to add top of body elements.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function hook_before_top_of_body(\core\hook\output\before_standard_top_of_body_html_generation $hook) {
        global $CFG;

        // Require  library.
        require_once($CFG->dirroot.'/blocks/course_generator/lib.php');

        // Call callback implementation.
        $hook->add_html(before_top_of_body());
    }
}
