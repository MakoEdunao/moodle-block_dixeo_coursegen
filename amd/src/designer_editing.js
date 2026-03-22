// This file is part of Moodle - http://moodle.org/
//
// @module     block_dixeo_designer/designer_editing
// @copyright  2026 Dixeo
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * Inline editing for editable fields in the structure designer.
 *
 * @module block_dixeo_designer/designer_editing
 */
define([
    'jquery',
    'block_dixeo_designer/designer_text'
], function($, TextUtil) {
    'use strict';

    return {
    /**
     * Set up editable field handlers
     */
    setupEditableHandlers: function() {
        var self = this;

        $('.editable').off('click').on('click', function() {
            if (self.currentlyEditing) {
                return;
            }

            self.startEditing($(this));
        });
    },

    /**
     * Start editing a field
     * @param {jQuery} $element Element to edit
     */
    startEditing: function($element) {
        var self = this;
        this.currentlyEditing = $element;

        var originalText = $element.text();
        var path = $element.data('path');

        $element.addClass('editing');
        $element.attr('contenteditable', 'true');
        $element.focus();

        // Select all text
        var range = document.createRange();
        range.selectNodeContents($element[0]);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);

        // Show save/cancel buttons
        var $controls = $element.next('.edit-controls');
        $controls.html($('#edit-controls-template').html());
        $controls.show();

        // Save button
        $controls.find('.save-edit').off('click').on('click', function() {
            var newText = $element.text().trim();
            self.saveEdit($element, path, newText);
        });

        // Cancel button
        $controls.find('.cancel-edit').off('click').on('click', function() {
            $element.text(originalText);
            self.cancelEdit($element);
        });

        // Save on Enter (except for multi-line fields)
        if (!$element.hasClass('module-summary') && !$element.hasClass('module-instructions') &&
                !$element.hasClass('course-summary')) {
            $element.off('keydown').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $controls.find('.save-edit').click();
                } else if (e.key === 'Escape') {
                    $element.text(originalText);
                    self.cancelEdit($element);
                }
            });
        }
    },

    /**
     * Save edited field
     * @param {jQuery} $element Element being edited
     * @param {string} path Data path
     * @param {string} value New value
     */
    saveEdit: function($element, path, value) {
        // Decode HTML entities before saving (e.g., &amp; -> &)
        var decodedValue = TextUtil.decodeHtml(value);

        // Update structure in memory
        this.setValueByPath(this.structure, path, decodedValue);
        this.pushHistory();

        // Clean up editing state
        this.cancelEdit($element);

        // Update displayed value (no section number prefix)
        $element.text(decodedValue);
    },

    /**
     * Cancel editing
     * @param {jQuery} $element Element being edited
     */
    cancelEdit: function($element) {
        $element.removeClass('editing');
        $element.removeAttr('contenteditable');
        $element.next('.edit-controls').hide().empty();
        this.currentlyEditing = null;
    }
};
});
