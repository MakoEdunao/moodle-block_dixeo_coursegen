// This file is part of Moodle - http://moodle.org/
//
// @module     block_dixeo_designer/designer_collapse
// @copyright  2026 Dixeo
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * Section collapse/expand state and handlers for the structure designer.
 *
 * @module block_dixeo_designer/designer_collapse
 */
define(['jquery'], function($) {
    'use strict';

    return {
    /**
     * Capture current collapse state (which sections are expanded)
     * @return {Object} Object mapping section index to expanded state
     */
    captureCollapseState: function() {
        var self = this;
        var expandedSections = {};
        $('.section-item').each(function() {
            var sectionIdx = $(this).data('section-idx');
            var sectionId = 'section-' + self.jobid + '-' + sectionIdx;
            var collapseTarget = document.getElementById(sectionId);
            expandedSections[sectionIdx] = Boolean(collapseTarget && collapseTarget.classList.contains('show'));
        });
        return expandedSections;
    },

    /**
     * Adjust expanded section indices after a section reorder
     * @param {Object} expandedSections Original expanded sections map
     * @param {number} fromIndex Original index of moved section
     * @param {number} toIndex New index of moved section
     * @return {Object} Adjusted expanded sections map
     */
    adjustExpandedIndices: function(expandedSections, fromIndex, toIndex) {
        if (fromIndex === toIndex) {
            return expandedSections;
        }

        var adjusted = {};

        Object.keys(expandedSections).forEach(function(idxStr) {
            var idx = parseInt(idxStr);
            var wasExpanded = expandedSections[idx];

            if (idx === fromIndex) {
                // The moved section will be at toIndex
                if (wasExpanded) {
                    adjusted[toIndex] = true;
                }
            } else if (fromIndex < toIndex) {
                // Moving forward: sections between fromIndex and toIndex shift back
                if (idx > fromIndex && idx <= toIndex) {
                    adjusted[idx - 1] = wasExpanded;
                } else {
                    adjusted[idx] = wasExpanded;
                }
            } else {
                // Moving backward: sections between toIndex and fromIndex shift forward
                if (idx >= toIndex && idx < fromIndex) {
                    adjusted[idx + 1] = wasExpanded;
                } else {
                    adjusted[idx] = wasExpanded;
                }
            }
        });

        return adjusted;
    },

    /**
     * Restore collapse state after re-rendering
     * @param {Object} expandedSections Object mapping section index to expanded state
     */
    restoreCollapseState: function(expandedSections) {
        var self = this;
        Object.keys(expandedSections).forEach(function(sectionIdx) {
            if (expandedSections[sectionIdx]) {
                var sectionId = 'section-' + self.jobid + '-' + sectionIdx;
                var collapseTarget = document.getElementById(sectionId);
                var toggleButton = document.querySelector('[data-target="#' + sectionId + '"]');
                var $toggleButton = toggleButton ? $(toggleButton) : $();
                if (collapseTarget) {
                    collapseTarget.classList.add('show');
                }
                var icon = $toggleButton.find('i');
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                $toggleButton.attr('aria-expanded', 'true').removeClass('collapsed');
            }
        });
    },

    /**
     * Set up collapse/expand handlers
     */
    setupCollapseHandlers: function() {
        $('[data-toggle="collapse"]').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var targetSel = $(this).data('target');
            var targetId = (targetSel && typeof targetSel === 'string' && targetSel.indexOf('#') === 0)
                ? targetSel.slice(1)
                : null;
            var targetEl = targetId ? document.getElementById(targetId) : null;
            var target = targetEl ? $(targetEl) : $();
            var icon = $(this).find('i');

            if (!target.length) {
                return;
            }
            if (target.hasClass('show')) {
                target.removeClass('show');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $(this).attr('aria-expanded', 'false').addClass('collapsed');
            } else {
                target.addClass('show');
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                $(this).attr('aria-expanded', 'true').removeClass('collapsed');
            }
        });
    }
};
});
