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

namespace block_dixeo_designer;

defined('MOODLE_INTERNAL') || die();

use local_dixeo\api\exception\api_exception;
use local_dixeo\external\service_factory;

/**
 * Helper for course template prompt options.
 *
 * @package    block_dixeo_designer
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_template_helper {
    /**
     * Returns remote course template choices from the API.
     *
     * @return array
     */
    public static function get_remote_course_template_choices(): array {
        try {
            $templates = service_factory::get_course_template_service()->list_templates();
            return self::normalise_templates($templates);
        } catch (api_exception $e) {
            debugging('Unable to load course templates from Dixeo API: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Returns available course template choices including the empty option.
     *
     * @return array
     */
    public static function get_course_template_choices(): array {
        return ['' => get_string('coursetemplate_none', 'block_dixeo_designer')] + self::get_remote_course_template_choices();
    }

    /**
     * Returns the configured course template id.
     *
     * @return string
     */
    public static function get_selected_course_template(): string {
        return (string)get_config('block_dixeo_designer', 'coursetemplate');
    }

    /**
     * Returns template options for the prompt select.
     *
     * @param string|null $selectedtemplateid Selected template id.
     * @return array
     */
    public static function get_course_template_options(?string $selectedtemplateid = null): array {
        $selectedtemplateid = $selectedtemplateid ?? self::get_selected_course_template();

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

    /**
     * Normalises template API responses into select-ready items.
     *
     * @param array $templates Raw API response.
     * @return array
     */
    private static function normalise_templates(array $templates): array {
        if (isset($templates['data']) && is_array($templates['data'])) {
            $templates = $templates['data'];
        } else if (isset($templates['items']) && is_array($templates['items'])) {
            $templates = $templates['items'];
        }

        $result = [];
        foreach ($templates as $template) {
            if (!is_array($template)) {
                continue;
            }

            $value = (string)($template['id'] ?? $template['uuid'] ?? $template['templateId'] ?? '');
            $label = trim((string)($template['name'] ?? $template['title'] ?? ''));

            if ($value === '' || $label === '') {
                continue;
            }

            $result[$value] = $label;
        }

        return $result;
    }
}
