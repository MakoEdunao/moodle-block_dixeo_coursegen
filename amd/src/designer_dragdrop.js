// This file is part of Moodle - http://moodle.org/
//
// @module     block_dixeo_designer/designer_dragdrop
// @copyright  2026 Dixeo
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * Section/module drag-and-drop for the structure designer.
 * Merged onto {@link module:block_dixeo_designer/designer} via jQuery.extend.
 *
 * @module block_dixeo_designer/designer_dragdrop
 */
define(['jquery'], function($) {
    'use strict';

    return {
    /**
     * Remove all drop insertion indicators (line/ghost box)
     */
    removeDropIndicators: function() {
        $('#page-blocks-dixeo_designer-designer .drop-insertion-indicator').remove();
    },

    /**
     * Set up drag and drop handlers
     */
    setupDragAndDrop: function() {
        var self = this;

        // Make sections draggable
        $('.section-item').attr('draggable', 'true');

        $('.section-item').on('dragstart', function(e) {
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('type', 'section');
            e.originalEvent.dataTransfer.setData('index', $(this).data('section-idx'));
            $(this).addClass('dragging');
        });

        $('.section-item').on('dragend', function() {
            $(this).removeClass('dragging');
            self.removeDropIndicators();
        });

        $('.section-item').on('dragover', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            if ($(this).hasClass('dragging')) {
                return;
            }
            var $target = $(this);
            var sectionIdx = $target.data('section-idx');
            var offsetY = e.originalEvent.offsetY;
            var height = $target.outerHeight();
            var insertBefore = offsetY < height / 2;
            var toIndex = insertBefore ? sectionIdx : sectionIdx + 1;
            $target.data('drop-insert-index', toIndex);

            self.removeDropIndicators();
            var $indicator = $('<div class="drop-insertion-indicator" aria-hidden="true"></div>');
            if (insertBefore) {
                $indicator.insertBefore($target);
            } else {
                $indicator.insertAfter($target);
            }
        });

        $('.section-item').on('dragleave', function(e) {
            var $next = $(e.relatedTarget);
            if (!$next.closest('.section-item').length) {
                self.removeDropIndicators();
            }
        });

        $('.section-item').on('drop', function(e) {
            e.preventDefault();
            var type = e.originalEvent.dataTransfer.getData('type');
            var fromIndex = parseInt(e.originalEvent.dataTransfer.getData('index'));
            var toIndex = $(this).data('drop-insert-index');
            if (toIndex === undefined) {
                toIndex = $(this).data('section-idx');
            }
            self.removeDropIndicators();

            if (type === 'section' && fromIndex !== toIndex) {
                var expandedSections = self.captureCollapseState();
                var section = self.structure.sections.splice(fromIndex, 1)[0];
                self.structure.sections.splice(toIndex, 0, section);
                self.pushHistory();
                var adjustedExpanded = self.adjustExpandedIndices(expandedSections, fromIndex, toIndex);
                self.pendingCollapseState = adjustedExpanded;
                self.renderStructure();
            }
        });

        // Make modules draggable
        $('.module-item').attr('draggable', 'true');

        $('.module-item').on('dragstart', function(e) {
            e.stopPropagation();
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('type', 'module');
            e.originalEvent.dataTransfer.setData('sectionIndex', $(this).closest('.section-item').data('section-idx'));
            e.originalEvent.dataTransfer.setData('moduleIndex', $(this).data('module-idx'));
            $(this).addClass('dragging');
        });

        $('.module-item').on('dragend', function() {
            $(this).removeClass('dragging');
            self.removeDropIndicators();
        });

        $('.module-item').on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            if ($(this).hasClass('dragging')) {
                return;
            }
            var $target = $(this);
            var toSectionIdx = $target.closest('.section-item').data('section-idx');
            var moduleIdx = $target.data('module-idx');
            var offsetY = e.originalEvent.offsetY;
            var height = $target.outerHeight();
            var insertBefore = offsetY < height / 2;
            var toModuleIdx = insertBefore ? moduleIdx : moduleIdx + 1;
            $target.data('drop-insert-section-index', toSectionIdx);
            $target.data('drop-insert-module-index', toModuleIdx);

            self.removeDropIndicators();
            var $indicator = $('<div class="drop-insertion-indicator" aria-hidden="true"></div>');
            if (insertBefore) {
                $indicator.insertBefore($target);
            } else {
                $indicator.insertAfter($target);
            }
        });

        $('.module-item').on('dragleave', function(e) {
            var $next = $(e.relatedTarget);
            if (!$next.closest('.module-item').length && !$next.closest('.modules-list').length) {
                self.removeDropIndicators();
            }
        });

        $('.module-item').on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var type = e.originalEvent.dataTransfer.getData('type');

            if (type === 'module') {
                var toSectionIdx = $(this).data('drop-insert-section-index');
                var toModuleIdx = $(this).data('drop-insert-module-index');
                if (toSectionIdx === undefined) {
                    toSectionIdx = $(this).closest('.section-item').data('section-idx');
                    toModuleIdx = $(this).data('module-idx');
                }
                self.removeDropIndicators();

                var fromSectionIdx = parseInt(e.originalEvent.dataTransfer.getData('sectionIndex'));
                var fromModuleIdx = parseInt(e.originalEvent.dataTransfer.getData('moduleIndex'));

                var expandedSections = self.captureCollapseState();
                var module = self.structure.sections[fromSectionIdx].modules.splice(fromModuleIdx, 1)[0];
                self.structure.sections[toSectionIdx].modules.splice(toModuleIdx, 0, module);
                self.pushHistory();
                self.pendingCollapseState = expandedSections;
                self.renderStructure();
            }
        });

        // Allow dropping modules at end of section (empty area of list)
        $('.modules-list').on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            var $list = $(this);
            self.removeDropIndicators();
            var $indicator = $('<div class="drop-insertion-indicator" aria-hidden="true"></div>');
            $indicator.appendTo($list);
        });

        $('.modules-list').on('dragleave', function(e) {
            var $related = $(e.relatedTarget);
            if (!$related.closest('.modules-list').is(this)) {
                self.removeDropIndicators();
            }
        });

        $('.modules-list').on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var type = e.originalEvent.dataTransfer.getData('type');

            if (type === 'module') {
                var toSectionIdx = $(this).closest('.section-item').data('section-idx');
                self.removeDropIndicators();

                var fromSectionIdx = parseInt(e.originalEvent.dataTransfer.getData('sectionIndex'));
                var fromModuleIdx = parseInt(e.originalEvent.dataTransfer.getData('moduleIndex'));

                var expandedSections = self.captureCollapseState();
                var module = self.structure.sections[fromSectionIdx].modules.splice(fromModuleIdx, 1)[0];
                self.structure.sections[toSectionIdx].modules.push(module);
                self.pushHistory();
                self.pendingCollapseState = expandedSections;
                self.renderStructure();
            }
        });
    }
};
});
