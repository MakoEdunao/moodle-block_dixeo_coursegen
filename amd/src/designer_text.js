// This file is part of Moodle - http://moodle.org/
//
// @module     block_dixeo_designer/designer_text
// @copyright  2026 Dixeo
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * HTML escape/decode helpers for the structure designer (editable fields).
 *
 * @module block_dixeo_designer/designer_text
 */
define([], function() {
    'use strict';

    return {
        /**
         * Escape HTML by assigning as text and reading innerHTML.
         *
         * @param {string} text
         * @returns {string}
         */
        escapeHtml: function(text) {
            if (!text) {
                return '';
            }
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Decode HTML entities (e.g. &amp; → &).
         *
         * @param {string} text
         * @returns {string}
         */
        decodeHtml: function(text) {
            if (!text) {
                return '';
            }
            var textarea = document.createElement('textarea');
            textarea.innerHTML = text;
            return textarea.value;
        }
    };
});
