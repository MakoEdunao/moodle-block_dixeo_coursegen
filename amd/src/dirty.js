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
 * AMD module for settings UI.
 *
 * @module     block_dixeo_coursegen/dirty
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        init: function() {
            const form = document.getElementById('adminsettings');
            if (!form) {
                return;
            }

            // Snapshot initial state.
            const initial = new Map();
            $(form).find('input, select, textarea').each(function() {
                const el = this;
                if (!el.name) {
                    return;
                }
                const val = (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value;
                initial.set(el.name, val);
            });

            /**
             * Checks if any input, select, or textarea element within the specified form
             * has been modified from its initial value.
             * @returns {boolean} True if the form is dirty (has unsaved changes), false otherwise.
             */
            function isDirty() {
                let dirty = false;
                $(form).find('input, select, textarea').each(function() {
                    const el = this;
                    if (!el.name || !initial.has(el.name)) {
                        return;
                    }
                    const now = (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value;
                    if (now != initial.get(el.name)) {
                        dirty = true;
                        return;
                    }
                });
                return dirty;
            }

            const register = form.querySelector('button.dixeo-register-button');
            if (!register) {
                return;
            }

            const needsRegistration = form.querySelector('.dixeo-register-instructions .needs-registration');
            if (!needsRegistration) {
                return;
            }

            const needsSaving = form.querySelector('.dixeo-register-instructions .needs-saving');
            if (!needsSaving) {
                return;
            }

            $(form).on('input change', () => {
                register.disabled = isDirty();
                if (isDirty()) {
                    needsRegistration.classList.add('hidden');
                    needsSaving.classList.remove('hidden');
                } else {
                    needsRegistration.classList.remove('hidden');
                    needsSaving.classList.add('hidden');
                }
            });

            register.addEventListener('click', (e) => {
                e.preventDefault();
                if (isDirty()) {
                    return false;
                } else {
                    const href = register.dataset.url;
                    if (href) {
                        window.location.assign(href);
                        return true;
                    }
                    return false;
                }
            });
        }
    };
});
