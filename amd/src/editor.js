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
 * Course structure JSON editor
 *
 * @module     block_dixeo_coursegen/editor
 * @package
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2026 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str', 'core/templates'],
    function($, Ajax, Notification, Str, Templates) {

    /** Module type options for the type select: value (stored in JSON), label, icon. */
    var MODULE_TYPE_OPTIONS = [
        {value: 'Page', label: 'Page', icon: 'fa-file-alt'},
        {value: 'Text and Media area', label: 'Text and Media area', icon: 'fa-book'},
        {value: 'Glossary', label: 'Glossary', icon: 'fa-list-alt'},
        {value: 'Slideshow', label: 'Slideshow', icon: 'fa-images'},
        {value: 'URL', label: 'URL', icon: 'fa-link'},
        {value: 'Simple Quiz', label: 'Simple Quiz', icon: 'fa-question-circle'},
        {value: 'Quiz', label: 'Quiz', icon: 'fa-check-square'},
        {value: 'H5P Quiz', label: 'H5P Quiz', icon: 'fa-puzzle-piece'},
        {value: 'Flash Cards', label: 'Flash Cards', icon: 'fa-id-card'},
        {value: 'Crosswords', label: 'Crosswords', icon: 'fa-th-large'},
        {value: 'Find the words', label: 'Find the words', icon: 'fa-search'}
    ];

    var Editor = {
        jobid: null,
        version: '',
        structure: null,
        historyIndex: -1,
        historyTotal: 0,
        versions: [],
        autoSaveInterval: null,
        countdownInterval: null,
        countdown: 60,
        currentlyEditing: null,
        hasUnsavedChanges: false,
        autoSaveEnabled: false,
        pendingCollapseState: null,

        /**
         * Initialize the editor
         * @param {string} jobid
         */
        init: function(jobid) {
            this.jobid = jobid;

            // Show loading indicator initially
            this.showLoading();

            // Load versions first to get history, then structure
            this.loadVersions();

            // Set up auto-save
            this.startAutoSave();

            // Set up event handlers
            this.setupEventHandlers();
        },

        /**
         * Show loading indicator
         */
        showLoading: function() {
            var container = $('.course-structure-container');
            Str.get_string('editor_loading', 'block_dixeo_coursegen').done(function(str) {
                container.html('<div id="loading-indicator" class="text-center py-5">' +
                    '<i class="fa fa-spinner fa-spin fa-3x"></i>' +
                    '<p class="mt-3">' + str + '</p>' +
                    '</div>');
            });
        },

        /**
         * Load structure from server
         * @param {number} index Optional history index (loads latest if -1 or not provided)
         */
        loadStructure: function(index) {
            var self = this;
            var loadIndex = (index !== undefined) ? index : -1;

            Ajax.call([{
                methodname: 'block_dixeo_coursegen_get_structure',
                args: {
                    jobid: this.jobid,
                    index: loadIndex
                },
                done: function(response) {
                    self.structure = JSON.parse(response.structure);
                    self.version = response.version;
                    self.historyIndex = response.index;
                    self.historyTotal = response.total;
                    self.renderStructure();
                    self.updateUndoRedoButtons();
                },
                fail: function(error) {
                    Notification.exception(error);
                }
            }]);
        },

        /**
         * Load all versions to track history
         */
        loadVersions: function() {
            var self = this;

            Ajax.call([{
                methodname: 'block_dixeo_coursegen_get_versions',
                args: {
                    jobid: this.jobid
                },
                done: function(versions) {
                    self.versions = versions;
                    self.historyTotal = versions.length;
                    // Load structure after versions are loaded
                    self.loadStructure();
                },
                fail: function(error) {
                    Notification.exception(error);
                }
            }]);
        },

        /**
         * Save structure to server (creates new version)
         */
        saveStructure: function() {
            var self = this;

            // Don't save if there are no unsaved changes
            if (!this.hasUnsavedChanges) {
                return;
            }

            // Show saving indicator
            this.showSavingIndicator();

            Ajax.call([{
                methodname: 'block_dixeo_coursegen_save_structure',
                args: {
                    jobid: this.jobid,
                    structure: JSON.stringify(this.structure),
                    current_index: this.historyIndex
                },
                done: function(response) {
                    self.version = response.version;
                    self.historyIndex = response.index;
                    self.historyTotal = response.total;
                    // Reload versions list to update history
                    self.loadVersions();
                    self.showSavedIndicator();

                    // Disable auto-save after saving until a new modification is made
                    self.clearModified();

                    // Update undo/redo buttons
                    self.updateUndoRedoButtons();
                },
                fail: function(error) {
                    Notification.exception(error);
                }
            }]);
        },

        /**
         * Render the structure as HTML using Mustache templates
         */
        renderStructure: function() {
            var self = this;
            var container = $('.course-structure-container');
            container.empty();

            if (!this.structure || !this.structure.title) {
                var self = this;
                Str.get_string('editor_invalid_data', 'block_dixeo_coursegen').done(function(str) {
                    container.html('<div class="alert alert-danger">' + str + '</div>');
                });
                return;
            }

            // Prepare template context
            // Note: We don't escape HTML here because Mustache auto-escapes {{}} variables
            var templateContext = {
                title: this.structure.title || '',
                summary: this.structure.summary || null,
                image: this.structure.image || null,
                jobid: this.jobid,
                hasSections: this.structure.sections && this.structure.sections.length > 0,
                sections: []
            };

            // Process sections
            if (this.structure.sections && this.structure.sections.length > 0) {
                this.structure.sections.forEach(function(section, sectionIdx) {
                    var sectionData = {
                        index: sectionIdx,
                        number: sectionIdx + 1,
                        title: section.title || '',
                        summary: section.summary || null,
                        jobid: self.jobid,
                        hasModules: section.modules && section.modules.length > 0,
                        modules: []
                    };

                    // Process modules
                    if (section.modules && section.modules.length > 0) {
                        section.modules.forEach(function(module, moduleIdx) {
                            var iconClass = self.getModuleIcon(module.type);
                            var moduleType = module.type || '';
                            sectionData.modules.push({
                                index: moduleIdx,
                                sectionIndex: sectionIdx,
                                type: moduleType,
                                typeUpper: moduleType.toUpperCase(),
                                title: module.title || '',
                                hints: module.hints || null,
                                icon: iconClass,
                                jobid: self.jobid,
                                moduleTypeOptions: MODULE_TYPE_OPTIONS
                            });
                        });
                    }

                    templateContext.sections.push(sectionData);
                });
            }

            // Update hasSections after populating (in case structure was empty)
            templateContext.hasSections = templateContext.sections.length > 0;

            // Load language strings and render template
            var stringsPromise = Str.get_strings([
                {key: 'editor_edit', component: 'block_dixeo_coursegen'},
                {key: 'editor_duplicate', component: 'block_dixeo_coursegen'},
                {key: 'editor_delete', component: 'block_dixeo_coursegen'},
                {key: 'editor_add_section', component: 'block_dixeo_coursegen'},
                {key: 'editor_add_activity', component: 'block_dixeo_coursegen'},
                {key: 'editor_change_activity_type', component: 'block_dixeo_coursegen'}
            ]);

            stringsPromise.then(function(strings) {
                templateContext.strings = {
                    edit: strings[0],
                    duplicate: strings[1],
                    delete: strings[2],
                    add_section: strings[3],
                    add_activity: strings[4],
                    change_activity_type: strings[5]
                };

                return Templates.render('block_dixeo_coursegen/course_structure', templateContext);
            }).then(function(html) {
                container.html(html);
                self.setupEventHandlersAfterRender();
            }).catch(function(error) {
                Notification.exception(error);
                Str.get_string('editor_invalid_data', 'block_dixeo_coursegen').done(function(str) {
                    container.html('<div class="alert alert-danger">' + str + '</div>');
                });
            });
        },

        /**
         * Set up event handlers after rendering
         */
        setupEventHandlersAfterRender: function() {

            // Set up collapse handlers
            this.setupCollapseHandlers();

            // Set up editable handlers
            this.setupEditableHandlers();

            // Set up action button handlers
            this.setupActionHandlers();

            // Set up module type select (icon → dropdown)
            this.setupModuleTypeSelectHandlers();

            // Set up drag and drop
            this.setupDragAndDrop();

            // Restore collapse state if pending (e.g., after drag-and-drop)
            if (this.pendingCollapseState) {
                this.restoreCollapseState(this.pendingCollapseState);
                this.pendingCollapseState = null;
            }
        },

        /**
         * Get Font Awesome icon class for module type
         * @param {string} type Module type
         * @return {string} Font Awesome icon class
         */
        getModuleIcon: function(type) {
            if (!type) {
                return 'fa-file-alt';
            }
            var t = type.toLowerCase();
            var i;
            for (i = 0; i < MODULE_TYPE_OPTIONS.length; i++) {
                if (MODULE_TYPE_OPTIONS[i].value.toLowerCase() === t) {
                    return MODULE_TYPE_OPTIONS[i].icon;
                }
            }
            return 'fa-file-alt';
        },

        /**
         * Escape HTML
         * @param {string} text Text to escape
         * @return {string} Escaped HTML
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
         * Decode HTML entities
         * @param {string} text Text with HTML entities
         * @return {string} Decoded text
         */
        decodeHtml: function(text) {
            if (!text) {
                return '';
            }
            var textarea = document.createElement('textarea');
            textarea.innerHTML = text;
            return textarea.value;
        },

        /**
         * Capture current collapse state (which sections are expanded)
         * @return {Object} Object mapping section index to expanded state
         */
        captureCollapseState: function() {
            var self = this;
            var expandedSections = {};
            $('.section-item').each(function() {
                var sectionIdx = $(this).data('section-idx');
                var collapseTarget = $('#section-' + self.jobid + '-' + sectionIdx);
                expandedSections[sectionIdx] = collapseTarget.hasClass('show');
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
                    var collapseTarget = $('#section-' + self.jobid + '-' + sectionIdx);
                    var toggleButton = $('[data-target="#section-' + self.jobid + '-' + sectionIdx + '"]');
                    var icon = toggleButton.find('i');

                    collapseTarget.addClass('show');
                    icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                    toggleButton.attr('aria-expanded', 'true').removeClass('collapsed');
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
                var target = $($(this).data('target'));
                var icon = $(this).find('i');

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
        },

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

            // Save on Enter (except for hints and summaries which might be multi-line)
            if (!$element.hasClass('module-hints') && !$element.hasClass('course-summary')) {
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
            var decodedValue = this.decodeHtml(value);

            // Update structure in memory
            this.setValueByPath(this.structure, path, decodedValue);

            // Mark as modified to enable auto-save
            this.markAsModified();

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
        },

        /**
         * Set value in structure by path
         * @param {Object} obj Object to modify
         * @param {string} path Path to property
         * @param {string} value New value
         */
        setValueByPath: function(obj, path, value) {
            var parts = path.match(/([^\[\]\.]+)|(\[\d+\])/g);
            var current = obj;

            for (var i = 0; i < parts.length - 1; i++) {
                var key = parts[i].replace(/[\[\]]/g, '');
                current = current[key];
            }

            var finalKey = parts[parts.length - 1].replace(/[\[\]]/g, '');
            current[finalKey] = value;
        },

        /**
         * Set up action button handlers
         */
        setupActionHandlers: function() {
            var self = this;

            // Edit button (same as clicking the text)
            $('.btn-edit-item').off('click').on('click', function(e) {
                e.stopPropagation();
                var $item = $(this).closest('.section-item, .module-item');
                var $title = $item.find('.section-title, .module-title').first();
                $title.click();
            });

            // Copy button
            $('.btn-copy-item').off('click').on('click', function(e) {
                e.stopPropagation();
                self.duplicateItem($(this));
            });

            // Delete button
            $('.btn-delete-item').off('click').on('click', function(e) {
                e.stopPropagation();
                self.deleteItem($(this));
            });

            // Add section button
            $('.btn-add-section').off('click').on('click', function(e) {
                e.stopPropagation();
                var sectionIndex = parseInt($(this).data('section-index'));
                self.addSection(sectionIndex);
            });

            // Add module/activity button
            $('.btn-add-module').off('click').on('click', function(e) {
                e.stopPropagation();
                var sectionIndex = parseInt($(this).data('section-index'));
                var moduleIndex = parseInt($(this).data('module-index'));
                self.addModule(sectionIndex, moduleIndex);
            });
        },

        /**
         * Set up module type select: toggle dropdown, option select, click outside to close
         */
        setupModuleTypeSelectHandlers: function() {
            var self = this;

            /** Close all open module-type dropdowns. */
            function closeAllDropdowns() {
                $('.module-type-select-dropdown').addClass('d-none').attr('aria-hidden', 'true');
                $('.module-type-select-toggle').attr('aria-expanded', 'false');
            }

            /**
             * Open the dropdown for a given wrapper
             * @param {jQuery} $wrapper The wrapper element containing the dropdown
             */
            function openDropdown($wrapper) {
                var $dropdown = $wrapper.find('.module-type-select-dropdown');
                var $toggle = $wrapper.find('.module-type-select-toggle');
                var isOpen = !$dropdown.hasClass('d-none');

                // Close all dropdowns first
                closeAllDropdowns();

                // If this one wasn't open, open it now
                if (!isOpen) {
                    $dropdown.removeClass('d-none').attr('aria-hidden', 'false');
                    $toggle.attr('aria-expanded', 'true');
                }
            }

            // Use event delegation for dynamically added elements
            $(document).off('click', '.module-type-select-toggle').on('click', '.module-type-select-toggle', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var $wrapper = $(this).closest('.module-type-select-wrapper');
                openDropdown($wrapper);
            });

            // Also open dropdown when clicking module-type div
            $(document).off('click', '.module-type').on('click', '.module-type', function(e) {
                e.stopPropagation();
                var $moduleItem = $(this).closest('.module-item');
                var $wrapper = $moduleItem.find('.module-type-select-wrapper');
                if ($wrapper.length) {
                    openDropdown($wrapper);
                }
            });

            // Use event delegation for dynamically added elements
            $(document).off('click', '.module-type-option').on('click', '.module-type-option', function(e) {
                e.stopPropagation();
                var value = $(this).data('value');
                var $wrapper = $(this).closest('.module-type-select-wrapper');
                var sectionIdx = parseInt($wrapper.data('section-index'), 10);
                var moduleIdx = parseInt($wrapper.data('module-index'), 10);

                // Find the option (use for loop for compatibility)
                var opt = null;
                var i;
                for (i = 0; i < MODULE_TYPE_OPTIONS.length; i++) {
                    if (MODULE_TYPE_OPTIONS[i].value === value) {
                        opt = MODULE_TYPE_OPTIONS[i];
                        break;
                    }
                }

                if (!opt) {
                    return;
                }

                // Update structure
                self.structure.sections[sectionIdx].modules[moduleIdx].type = value;

                // Update UI: icon and type text (exclude chevron)
                $wrapper.find('.module-type-select-toggle i').not('.module-type-select-chevron')
                    .removeClass().addClass('fa ' + opt.icon + ' fa-2x');
                var $moduleType = $wrapper.closest('.module-item').find('.module-type');
                if ($moduleType.length) {
                    $moduleType.text(value.toUpperCase());
                }

                closeAllDropdowns();
                self.markAsModified();
            });

            $(document).off('click.module-type-select').on('click.module-type-select', function(e) {
                if (!$(e.target).closest('.module-type-select-wrapper').length) {
                    closeAllDropdowns();
                }
            });

            // Highlight toggle and module-type when hovering over toggle
            $(document).off('mouseenter mouseleave', '.module-type-select-toggle')
                .on('mouseenter mouseleave', '.module-type-select-toggle', function(e) {
                    var $moduleItem = $(this).closest('.module-item');
                    var $moduleType = $moduleItem.find('.module-type');
                    if (e.type === 'mouseenter') {
                        $(this).addClass('highlighted');
                        $moduleType.addClass('highlighted');
                    } else {
                        // Only remove highlight if module-type is not being hovered
                        if (!$moduleType.is(':hover')) {
                            $(this).removeClass('highlighted');
                            $moduleType.removeClass('highlighted');
                        }
                    }
                });

            // Also highlight toggle when hovering over module-type (if not already highlighted)
            $(document).off('mouseenter mouseleave', '.module-type').on('mouseenter mouseleave', '.module-type', function(e) {
                var $moduleItem = $(this).closest('.module-item');
                var $toggle = $moduleItem.find('.module-type-select-toggle');
                if (e.type === 'mouseenter') {
                    $toggle.addClass('highlighted');
                    $(this).addClass('highlighted');
                } else {
                    // Only remove highlight if toggle is not being hovered
                    if (!$toggle.is(':hover')) {
                        $toggle.removeClass('highlighted');
                        $(this).removeClass('highlighted');
                    }
                }
            });
        },

        /**
         * Add a new section
         * @param {number} index Index where to insert the section
         */
        addSection: function(index) {
            var self = this;
            // Capture collapse state before re-rendering
            var expandedSections = this.captureCollapseState();

            // Load language strings for defaults
            Str.get_strings([
                {key: 'editor_new_section_title', component: 'block_dixeo_coursegen'},
                {key: 'editor_new_section_summary', component: 'block_dixeo_coursegen'}
            ]).done(function(strings) {
                var newSection = {
                    title: strings[0],
                    summary: strings[1],
                    modules: []
                };

                // Insert at the specified index
                self.structure.sections.splice(index, 0, newSection);

                // Mark as modified
                self.markAsModified();

                // Store expanded state to restore after render
                self.pendingCollapseState = expandedSections;

                self.renderStructure();
            });
        },

        /**
         * Add a new module/activity
         * @param {number} sectionIndex Index of the section
         * @param {number} moduleIndex Index where to insert the module
         */
        addModule: function(sectionIndex, moduleIndex) {
            var self = this;
            // Capture collapse state before re-rendering
            var expandedSections = this.captureCollapseState();

            // Ensure section has modules array
            if (!this.structure.sections[sectionIndex].modules) {
                this.structure.sections[sectionIndex].modules = [];
            }

            // Load language strings for defaults
            Str.get_strings([
                {key: 'editor_new_module_type', component: 'block_dixeo_coursegen'},
                {key: 'editor_new_module_title', component: 'block_dixeo_coursegen'},
                {key: 'editor_new_module_hints', component: 'block_dixeo_coursegen'}
            ]).done(function(strings) {
                var newModule = {
                    type: strings[0],
                    title: strings[1],
                    hints: strings[2]
                };

                // Insert at the specified index
                self.structure.sections[sectionIndex].modules.splice(moduleIndex, 0, newModule);

                // Mark as modified
                self.markAsModified();

                // Store expanded state to restore after render (and ensure section is expanded)
                expandedSections[sectionIndex] = true;
                self.pendingCollapseState = expandedSections;

                self.renderStructure();
            });
        },

        /**
         * Duplicate section or module
         * @param {jQuery} $button Button that was clicked
         */
        duplicateItem: function($button) {
            // Capture collapse state before re-rendering
            var expandedSections = this.captureCollapseState();

            var $sectionItem = $button.closest('.section-item');
            var $moduleItem = $button.closest('.module-item');

            var self = this;
            // Load language string for copy suffix
            Str.get_string('editor_copy_suffix', 'block_dixeo_coursegen').done(function(copySuffix) {
                if ($moduleItem.length > 0) {
                    // Duplicate module
                    var sectionIdx = $sectionItem.data('section-idx');
                    var moduleIdx = $moduleItem.data('module-idx');
                    var module = JSON.parse(JSON.stringify(self.structure.sections[sectionIdx].modules[moduleIdx]));
                    module.title = module.title + copySuffix;
                    self.structure.sections[sectionIdx].modules.splice(moduleIdx + 1, 0, module);
                } else if ($sectionItem.length > 0) {
                    // Duplicate section
                    var sectionIdx = $sectionItem.data('section-idx');
                    var section = JSON.parse(JSON.stringify(self.structure.sections[sectionIdx]));
                    section.title = section.title + copySuffix;
                    self.structure.sections.splice(sectionIdx + 1, 0, section);
                }

                // Mark as having unsaved changes
                self.markAsModified();

                // Store expanded state to restore after render
                self.pendingCollapseState = expandedSections;

                self.renderStructure();
            });
        },

        /**
         * Delete section or module
         * @param {jQuery} $button Button that was clicked
         */
        deleteItem: function($button) {
            var self = this;
            var $sectionItem = $button.closest('.section-item');
            var $moduleItem = $button.closest('.module-item');

            var messageKey = $moduleItem.length > 0 ? 'editor_delete_module_confirm' : 'editor_delete_section_confirm';
            var titleKey = 'editor_confirm_delete';

            Str.get_strings([
                {key: titleKey, component: 'block_dixeo_coursegen'},
                {key: messageKey, component: 'block_dixeo_coursegen'},
                {key: 'delete', component: 'core'},
                {key: 'cancel', component: 'core'}
            ]).done(function(strings) {
                Notification.confirm(
                    strings[0],
                    strings[1],
                    strings[2],
                    strings[3],
                        function() {
                            // Capture collapse state before re-rendering
                            var expandedSections = self.captureCollapseState();

                            if ($moduleItem.length > 0) {
                                // Delete module
                                var sectionIdx = $sectionItem.data('section-idx');
                                var moduleIdx = $moduleItem.data('module-idx');
                                self.structure.sections[sectionIdx].modules.splice(moduleIdx, 1);
                            } else if ($sectionItem.length > 0) {
                                // Delete section
                                var sectionIdx = $sectionItem.data('section-idx');
                                self.structure.sections.splice(sectionIdx, 1);
                            }

                            // Mark as having unsaved changes
                            self.markAsModified();

                            // Store expanded state to restore after render
                            self.pendingCollapseState = expandedSections;

                            self.renderStructure();
                        }
                    );
                });
        },

        /**
         * Mark structure as modified and enable auto-save
         */
        markAsModified: function() {
            this.hasUnsavedChanges = true;
            // Only enable auto-save and reset countdown on first change
            if (!this.autoSaveEnabled) {
                this.autoSaveEnabled = true;
                this.countdown = 60;
                $('#countdown-timer').text(this.countdown).removeClass('badge-secondary').addClass('badge-primary').show();
                $('#autosave-label').show();
            }
        },

        /**
         * Start auto-save timer
         */
        startAutoSave: function() {
            var self = this;

            // Hide countdown timer initially (will be shown when auto-save becomes active)
            $('#countdown-timer').hide();
            $('#autosave-label').hide();

            // Countdown timer
            this.countdownInterval = setInterval(function() {
                if (self.autoSaveEnabled && self.hasUnsavedChanges) {
                    self.countdown--;
                    $('#countdown-timer').text(self.countdown);

                    if (self.countdown <= 0) {
                        self.saveStructure();
                    }
                }
            }, 1000);

            // Manual save button
            $('#btn-save-now').on('click', function() {
                if (self.hasUnsavedChanges) {
                    self.saveStructure();
                }
            });

            // Undo button
            $('#btn-undo').on('click', function() {
                self.undo();
            });

            // Redo button
            $('#btn-redo').on('click', function() {
                self.redo();
            });

            // Reload button
            $('#btn-reload').on('click', function() {
                Str.get_string('editor_reload_confirm', 'block_dixeo_coursegen').done(function(confirmMsg) {
                    if (confirm(confirmMsg)) {
                        self.hasUnsavedChanges = false;
                        self.autoSaveEnabled = false;
                        self.countdown = 60;
                        // Hide the auto-save indicator
                        $('#countdown-timer').hide();
                        $('#autosave-label').hide();
                        // Reload latest version
                        self.loadStructure(-1);
                    }
                });
            });
        },

        /**
         * Clear modified flag (after save)
         */
        clearModified: function() {
            this.hasUnsavedChanges = false;
            this.autoSaveEnabled = false;
            this.countdown = 60;
            // Hide the auto-save indicator
            $('#countdown-timer').hide();
            $('#autosave-label').hide();
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
                $('.drag-over').removeClass('drag-over');
            });

            $('.section-item').on('dragover', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                $(this).addClass('drag-over');
            });

            $('.section-item').on('dragleave', function() {
                $(this).removeClass('drag-over');
            });

            $('.section-item').on('drop', function(e) {
                e.preventDefault();
                var type = e.originalEvent.dataTransfer.getData('type');
                var fromIndex = parseInt(e.originalEvent.dataTransfer.getData('index'));
                var toIndex = $(this).data('section-idx');

                if (type === 'section' && fromIndex !== toIndex) {
                    // Capture collapse state before re-rendering
                    var expandedSections = self.captureCollapseState();

                    // Reorder sections
                    var section = self.structure.sections.splice(fromIndex, 1)[0];
                    self.structure.sections.splice(toIndex, 0, section);
                    self.markAsModified();

                    // Store expanded state to restore after render
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
                $('.drag-over').removeClass('drag-over');
            });

            $('.module-item').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                $(this).addClass('drag-over');
            });

            $('.module-item').on('dragleave', function() {
                $(this).removeClass('drag-over');
            });

            $('.module-item').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var type = e.originalEvent.dataTransfer.getData('type');

                if (type === 'module') {
                    // Capture collapse state before re-rendering
                    var expandedSections = self.captureCollapseState();

                    var fromSectionIdx = parseInt(e.originalEvent.dataTransfer.getData('sectionIndex'));
                    var fromModuleIdx = parseInt(e.originalEvent.dataTransfer.getData('moduleIndex'));
                    var toSectionIdx = $(this).closest('.section-item').data('section-idx');
                    var toModuleIdx = $(this).data('module-idx');

                    // Move module
                    var module = self.structure.sections[fromSectionIdx].modules.splice(fromModuleIdx, 1)[0];
                    self.structure.sections[toSectionIdx].modules.splice(toModuleIdx, 0, module);
                    self.markAsModified();

                    // Store expanded state to restore after render
                    self.pendingCollapseState = expandedSections;

                    self.renderStructure();
                }
            });

            // Allow dropping modules into empty sections or at the end
            $('.modules-list').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.originalEvent.dataTransfer.dropEffect = 'move';
            });

            $('.modules-list').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var type = e.originalEvent.dataTransfer.getData('type');

                if (type === 'module') {
                    // Capture collapse state before re-rendering
                    var expandedSections = self.captureCollapseState();

                    var fromSectionIdx = parseInt(e.originalEvent.dataTransfer.getData('sectionIndex'));
                    var fromModuleIdx = parseInt(e.originalEvent.dataTransfer.getData('moduleIndex'));
                    var toSectionIdx = $(this).closest('.section-item').data('section-idx');

                    // Move module to end of section
                    var module = self.structure.sections[fromSectionIdx].modules.splice(fromModuleIdx, 1)[0];
                    self.structure.sections[toSectionIdx].modules.push(module);
                    self.markAsModified();

                    // Store expanded state to restore after render
                    self.pendingCollapseState = expandedSections;

                    self.renderStructure();
                }
            });
        },

        /**
         * Set up event handlers
         */
        setupEventHandlers: function() {
            var self = this;

            // Cleanup on page unload
            $(window).on('beforeunload', function() {
                if (self.hasUnsavedChanges) {
                    // Note: beforeunload message is browser-controlled, but we set it anyway
                    return self.unsavedChangesMessage || 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            // Load unsaved changes message
            Str.get_string('editor_unsaved_changes', 'block_dixeo_coursegen').done(function(str) {
                self.unsavedChangesMessage = str;
            });

        },

        /**
         * Update undo/redo button states
         */
        updateUndoRedoButtons: function() {
            var canUndo = this.historyIndex > 0;
            var canRedo = this.historyIndex < this.historyTotal - 1;

            $('#btn-undo').prop('disabled', !canUndo);
            $('#btn-redo').prop('disabled', !canRedo);
        },

        /**
         * Undo - load previous version in history
         */
        undo: function() {
            if (this.historyIndex <= 0) {
                return; // Already at oldest
            }

            // Check for unsaved changes
            if (this.hasUnsavedChanges) {
                var self = this;
                Str.get_strings([
                    {key: 'editor_reload_confirm', component: 'block_dixeo_coursegen'},
                    {key: 'cancel', component: 'core'},
                    {key: 'continue', component: 'core'}
                ]).done(function(strings) {
                    Notification.confirm(
                        strings[0],
                        '',
                        strings[2],
                        strings[1],
                        function() {
                            // User confirmed, discard changes and undo
                            self.hasUnsavedChanges = false;
                            self.autoSaveEnabled = false;
                            self.countdown = 60;
                            $('#countdown-timer').hide();
                            $('#autosave-label').hide();
                            self.showLoading();
                            self.loadStructure(self.historyIndex - 1);
                        }
                    );
                });
            } else {
                // No unsaved changes, undo immediately
                this.showLoading();
                this.loadStructure(this.historyIndex - 1);
            }
        },

        /**
         * Redo - load next version in history
         */
        redo: function() {
            if (this.historyIndex >= this.historyTotal - 1) {
                return; // Already at latest
            }

            // Check for unsaved changes
            if (this.hasUnsavedChanges) {
                var self = this;
                Str.get_strings([
                    {key: 'editor_reload_confirm', component: 'block_dixeo_coursegen'},
                    {key: 'cancel', component: 'core'},
                    {key: 'continue', component: 'core'}
                ]).done(function(strings) {
                    Notification.confirm(
                        strings[0],
                        '',
                        strings[2],
                        strings[1],
                        function() {
                            // User confirmed, discard changes and redo
                            self.hasUnsavedChanges = false;
                            self.autoSaveEnabled = false;
                            self.countdown = 60;
                            $('#countdown-timer').hide();
                            $('#autosave-label').hide();
                            self.showLoading();
                            self.loadStructure(self.historyIndex + 1);
                        }
                    );
                });
            } else {
                // No unsaved changes, redo immediately
                this.showLoading();
                this.loadStructure(this.historyIndex + 1);
            }
        },

        /**
         * Show saving indicator
         */
        showSavingIndicator: function() {
            // Remove any existing indicators first
            $('.saving-indicator').remove();

            Str.get_string('editor_saving', 'block_dixeo_coursegen').done(function(str) {
                var $indicator = $('<div class="saving-indicator"><i class="fa fa-spinner fa-spin"></i> ' + str + '</div>');
                $('body').append($indicator);

                setTimeout(function() {
                    $indicator.remove();
                }, 3000);
            });
        },

        /**
         * Show saved indicator
         */
        showSavedIndicator: function() {
            // Remove any existing indicators first
            $('.saving-indicator').remove();

            Str.get_string('editor_saved', 'block_dixeo_coursegen').done(function(str) {
                var $indicator = $('<div class="saving-indicator"><i class="fa fa-check"></i> ' + str + '</div>');
                $('body').append($indicator);

                setTimeout(function() {
                    $indicator.fadeOut(function() {
                        $(this).remove();
                    });
                }, 2000);
            });
        },

        /**
         * Show warning when saving from an old version
         * @param {string} version New version number
         */
        showDivergentWarning: function(version) {
            Str.get_strings([
                {key: 'editor_divergent_save', component: 'block_dixeo_coursegen'},
                {key: 'editor_divergent_message', component: 'block_dixeo_coursegen', param: version},
                {key: 'editor_ok', component: 'block_dixeo_coursegen'}
            ]).done(function(strings) {
                Notification.alert(
                    strings[0],
                    strings[1],
                    strings[2]
                );
            });
        }
    };

    return Editor;
});
