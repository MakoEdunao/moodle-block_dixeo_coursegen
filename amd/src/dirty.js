// amd/src/admindirty.js
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
                        return false;
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
                    }
                }
            });
        }
    };
});
