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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace block_dixeo_designer\service;

defined('MOODLE_INTERNAL') || die();

use local_dixeo\external\service_factory;

/**
 * Thin helper for course template prompt options (UI only).
 *
 * Template list and cache live in local_dixeo course_template_service.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_template_helper {

    /**
     * Returns remote course template choices from local_dixeo (cached there).
     *
     * When the Dixeo API is not configured (e.g. during install), returns an empty list
     * so settings and UI do not trigger API calls or debugging output.
     *
     * @return array Map of template id => label.
     */
    public static function get_remote_course_template_choices(): array {
        $service = service_factory::get_course_template_service();
        if (!$service->is_configured()) {
            return [];
        }
        return $service->get_cached_choices();
    }

    /**
     * Returns available course template choices including the empty option.
     *
     * @return array Map of value => label for the select.
     */
    public static function get_course_template_choices(): array {
        return ['' => get_string('coursetemplate_none', 'block_dixeo_designer')] + self::get_remote_course_template_choices();
    }

    /**
     * Returns the configured course template id from block settings.
     *
     * @return string
     */
    public static function get_selected_course_template(): string {
        return (string)get_config('block_dixeo_designer', 'coursetemplate');
    }

    /**
     * Returns template options for the prompt select (value, label, selected).
     *
     * @param string|null $selectedtemplateid Selected template id.
     * @return array
     */
    public static function get_course_template_options(?string $selectedtemplateid = null): array {
        if ($selectedtemplateid === null || $selectedtemplateid === '') {
            $selectedtemplateid = self::get_selected_course_template();
        }

        $remotechoices = self::get_remote_course_template_choices();
        if (empty($remotechoices)) {
            return [];
        }

        $options = ['' => get_string('coursetemplate_none', 'block_dixeo_designer')] + $remotechoices;

        $result = [];
        foreach ($options as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
                'selected' => ((string)$value === (string)$selectedtemplateid),
            ];
        }

        return $result;
    }
}
