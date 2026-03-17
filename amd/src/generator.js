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
 * AMD module for course designer block.
 *
 * @module     block_dixeo_designer/generator
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax',
    'jquery',
    'core/templates',
    'core/notification',
    'core/str',
    'core/config'
], function(Ajax, $, Template, Notification, Str, Config) {
    const generatorForm = document.getElementById('edai_course_designer_form');
    const promptContainer = generatorForm.querySelector('.prompt-container');
    const promptForm = generatorForm.querySelector('#prompt-form');
    const generationContainer = generatorForm.querySelector('.generation-container');
    const courseDescription = generatorForm.querySelector('#course_description');
    const templateSelect = generatorForm.querySelector('#templateid');
    const generateCourse = generatorForm.querySelector('#generate_course');
    const generateStructure = generatorForm.querySelector('#generate_course_structure');
    const tempCourseFiles = generatorForm.querySelector('#temp_course_files');
    const filesContainer = generatorForm.querySelector('#file_names');

    return {
        init: function() {
            this.progress = 0;
            this.adjustDescriptionHeight();
            this.handleDragAndDrop();
            this.bindDeleteHandlers();

            courseDescription.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey && generateStructure) {
                    event.preventDefault();
                    generateStructure.click();
                }
            });

            if (generateCourse) {
                generateCourse.addEventListener('click', (event) => this.generateCourse(event, false));
            }
            if (generateStructure) {
                generateStructure.addEventListener('click', (event) => this.generateCourse(event, true));
            }

            const cancelBtn = generationContainer.querySelector('.btn-cancel-draft');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', (event) => this.cancelDraft(event));
            }

            const toggleBtn = document.querySelector('.dixeo-designer-block-toggle');
            const blockContainer = document.querySelector('.block_dixeo_designer.block-container');
            if (toggleBtn && blockContainer) {
                toggleBtn.addEventListener('click', function() {
                    const isHidden = blockContainer.classList.toggle('d-none');
                    toggleBtn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                    toggleBtn.setAttribute('title', isHidden
                        ? toggleBtn.getAttribute('data-title-show')
                        : toggleBtn.getAttribute('data-title-hide'));
                    const icon = toggleBtn.querySelector('i.fa');
                    if (icon) {
                        icon.classList.remove('fa-chevron-up', 'fa-chevron-down');
                        icon.classList.add(isHidden ? 'fa-chevron-down' : 'fa-chevron-up');
                    }
                });
            }
        },
        cancelDraft: function(event) {
            event.preventDefault();
            const self = this;
            Ajax.call([{
                methodname: 'block_dixeo_designer_cancel_draft',
                args: {
                    job_id: this.getJobId(),
                    sesskey: M.cfg.sesskey
                },
            }])[0]
            .then(function() {
                self.clearPoll();
                self.resetProgress();
            })
            .catch(function(err) {
                self.clearPoll();
                self.resetProgress();
                Notification.alert('', err.message || 'Cancel failed');
            });
        },
        getJobId: function() {
            return generationContainer.dataset.job_id;
        },
        hasServerFiles: function() {
            return Boolean(filesContainer && filesContainer.querySelector('.file-item'));
        },
        generateCourse: function(event, reviewStructure) {
            event.preventDefault();

            const courseDescriptionValue = courseDescription.value.trim();
            if (courseDescriptionValue === '' && !this.hasServerFiles()) {
                this.notify('invalidinput', 'descriptionorfilesrequired');
                return;
            }

            if (this.progress === 0) {
                this.startProgress();
            }

            // reviewStructure true = design only (no course), false = create full course. skip=1 means create course.
            const createcourse = !reviewStructure;

            Ajax.call([{
                methodname: 'block_dixeo_designer_generate_course',
                args: {
                    job_id: this.getJobId(),
                    description: courseDescriptionValue,
                    templateid: (templateSelect && templateSelect.value !== '') ? templateSelect.value : null,
                    skip: reviewStructure ? 0 : 1,
                    sesskey: M.cfg.sesskey
                },
            }])[0]
            .then(() => {
                this.pollStructureStatus(createcourse);
            })
            .catch(async error => {
                this.resetProgress();
                this.clearPoll();
                const errorTitle = await Str.get_string('error_title', 'block_dixeo_designer');
                Notification.alert(errorTitle, error.message);
            });
        },
        pollIntervalId: null,
        clearPoll: function() {
            if (this.pollIntervalId) {
                clearInterval(this.pollIntervalId);
                this.pollIntervalId = null;
            }
        },
        pollStructureStatus: function(createcourse) {
            const self = this;
            const poll = function() {
                Ajax.call([{
                    methodname: 'block_dixeo_designer_get_structure_status',
                    args: {
                        job_id: self.getJobId(),
                        sesskey: M.cfg.sesskey
                    },
                }])[0]
                .then(function(data) {
                    if (data.failed) {
                        self.clearPoll();
                        self.resetProgress();
                        Notification.alert('', data.error || 'Generation failed');
                        return;
                    }
                    if (data.completed) {
                        self.clearPoll();
                        if (createcourse) {
                            Ajax.call([{
                                methodname: 'block_dixeo_designer_finalize_course',
                                args: {
                                    job_id: self.getJobId(),
                                    createcourse: true,
                                    sesskey: M.cfg.sesskey
                                },
                            }])[0]
                            .then(function(final) {
                                self.finishProgress(final.courseid, final.coursename);
                            })
                            .catch(function(err) {
                                self.resetProgress();
                                Notification.alert('', err.message || 'Finalize failed');
                            });
                        } else {
                            window.location.href = Config.wwwroot + '/blocks/dixeo_designer/designer.php?id=' + self.getJobId();
                        }
                        return;
                    }
                    if (data.progress >= 0 && data.progress <= 100) {
                        self.setProgress(data.progress);
                    }
                })
                .catch(function(err) {
                    self.clearPoll();
                    self.resetProgress();
                    Notification.alert('', err.message || 'Status check failed');
                });
            };

            poll();
            this.pollIntervalId = setInterval(poll, 3000);
        },
        adjustDescriptionHeight: function() {
            courseDescription.addEventListener('input', function() {
                this.style.height = 'auto';
                const maxHeight = parseFloat(getComputedStyle(this).lineHeight) * 9;
                this.style.overflowY = 'hidden';

                if (this.scrollHeight > maxHeight) {
                    this.style.height = maxHeight + 'px';
                    this.style.overflowY = 'scroll';
                } else {
                    this.style.height = this.scrollHeight + 'px';
                }
            });
            courseDescription.dispatchEvent(new Event('input'));
        },
        setFileNamesLoading: function(loading) {
            if (!filesContainer) {
                return;
            }
            const text = filesContainer.dataset.uploadingText || 'Uploading…';
            if (loading) {
                filesContainer.classList.remove('d-none');
                filesContainer.classList.add('file-names-loading');
                filesContainer.innerHTML = '<div class="file-names-loading-state">' +
                    '<span class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></span>' +
                    '<span class="file-names-loading-text">' + text + '</span></div>';
            } else {
                filesContainer.classList.remove('file-names-loading');
            }
        },
        transferFiles: async function(newFiles) {
            if (!newFiles || newFiles.length === 0) {
                return;
            }

            const formData = new FormData();
            formData.append('sesskey', M.cfg.sesskey);
            formData.append('jobid', this.getJobId());
            Array.from(newFiles).forEach((file) => formData.append('files[]', file));

            this.setFileNamesLoading(true);

            try {
                const response = await fetch(
                    Config.wwwroot + '/blocks/dixeo_designer/upload_submission_files.php',
                    {method: 'POST', body: formData}
                );
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Upload failed');
                }

                this.displayFileNames(data.context);
            } catch (error) {
                this.setFileNamesLoading(false);
                filesContainer.innerHTML = '';
                filesContainer.classList.add('d-none');
                Notification.alert('', error.message || 'Upload failed');
            } finally {
                tempCourseFiles.value = '';
            }
        },
        handleDragAndDrop: function() {
            let dragEnterCounter = 0;
            $('#prompt-form').bind({
                dragenter: function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    dragEnterCounter++;
                    promptContainer.classList.add('drag-over');
                },
                dragleave: function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    dragEnterCounter--;
                    if (dragEnterCounter === 0) {
                        promptContainer.classList.remove('drag-over');
                    }
                },
            });

            this.dropOnChildElements(promptForm);
            tempCourseFiles.addEventListener('change', () => this.transferFiles(tempCourseFiles.files));
        },
        dropOnChildElements: function(node) {
            node.childNodes.forEach(child => {
                if (child.nodeType !== Node.ELEMENT_NODE) {
                    return;
                }

                this.dropOnChildElements(child);

                child.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                });

                child.addEventListener('drop', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    promptContainer.classList.remove('drag-over');

                    if (event.dataTransfer.files.length > 0) {
                        this.transferFiles(event.dataTransfer.files);
                    }
                });
            });
        },
        startProgress: function() {
            if (generateCourse) {
                generateCourse.disabled = true;
            }
            if (generateStructure) {
                generateStructure.disabled = true;
            }
            promptContainer.classList.replace('d-block', 'd-none');
            generationContainer.classList.replace('d-none', 'd-block');

            let interval = setInterval(() => {
                if (this.progress >= 95) {
                    clearInterval(interval);
                }

                // Increase by a random amount every second
                let stage = parseInt(generationContainer.dataset.status);

                if (this.progress < stage * 20) {
                    this.setProgress(stage * 20);
                } else {
                    // Each increment should average about 20/45 ≈ 0.44.
                    // Use Math.random() to get a value between 0.3 and 0.6, rounded to 2 decimals.
                    let increment = +(0.3 + Math.random() * 0.3).toFixed(2);

                    if (this.progress < 40 || this.progress > 80) {
                        increment /= 2; // Slow down before 40% and after 90%.
                    }

                    this.setProgress(this.progress + increment);
                }
            }, 1000);
        },
        finishProgress: async function(courseid, coursename) {
            this.setProgress(100);
            setTimeout(() => {
                let context = {
                    courseid: courseid,
                    coursename: coursename,
                    wwwroot: Config.wwwroot
                };

                Template.render('block_dixeo_designer/success_message', context)
                .then((html) => {
                    generationContainer.parentElement.insertAdjacentHTML('beforeend', html);
                    generationContainer.classList.replace('d-block', 'd-none');
                    return;
                }).catch((error) => {
                    Notification.exception(error);
                });
            }, 3000);
        },
        resetProgress: function() {
            this.clearPoll();
            if (generateCourse) {
                generateCourse.disabled = false;
            }
            if (generateStructure) {
                generateStructure.disabled = false;
            }
            promptContainer.classList.replace('d-none', 'd-block');
            generationContainer.classList.replace('d-block', 'd-none');

            let successContainer = generatorForm.querySelector('#success_message_container');
            if (successContainer) {
                successContainer.remove();
            }

            this.setProgress(0);
        },
        setProgress: function(progress) {
            this.progress = progress;

            let progressBar = generatorForm.querySelector('.s-progress--bar');
            if (progressBar) {
                progressBar.style.width = `${progress}%`;
                if (progress >= 100) {
                    progressBar.classList.add('done');
                } else {
                    progressBar.classList.remove('done');
                }
            }
        },
        displayFileNames: function(context) {
            if (filesContainer) {
                // Dispose any Bootstrap tooltips on current content to prevent stuck tooltips after DOM replace.
                $(filesContainer).find('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip('dispose');
                Template.render('block_dixeo_designer/filenames', context).then((html) => {
                    filesContainer.classList.remove('file-names-loading');
                    filesContainer.innerHTML = html;
                    if (context.hasFiles) {
                        filesContainer.classList.remove('d-none');
                    } else {
                        filesContainer.classList.add('d-none');
                    }
                    this.bindDeleteHandlers();
                }).catch((error) => {
                    Notification.exception(error);
                });
            }
        },
        bindDeleteHandlers: function() {
            if (!filesContainer) {
                return;
            }

            filesContainer.querySelectorAll('.delete-icon').forEach((deleteIcon) => {
                deleteIcon.addEventListener('click', async() => {
                    try {
                        const response = await fetch(
                            Config.wwwroot + '/blocks/dixeo_designer/delete_submission_file.php',
                            {
                                method: 'POST',
                                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                                body: new URLSearchParams({
                                    sesskey: M.cfg.sesskey,
                                    jobid: this.getJobId(),
                                    fileid: deleteIcon.dataset.fileId
                                })
                            }
                        );
                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Delete failed');
                        }

                        this.displayFileNames(data.context);
                    } catch (error) {
                        Notification.exception(error);
                    }
                });
            });
        },
        notify: async function() {
            let strings = [];
            let component = 'block_dixeo_designer';

            for (let i = 0; i < arguments.length; i++) {
                if (Array.isArray(arguments[i])) {
                    strings.push({
                        key: arguments[i][0],
                        component: component,
                        param: arguments[i][1]
                    });
                } else if (i === 1 && arguments[i]) {
                    Notification.alert('', arguments[i]);
                    return;
                } else {
                    strings.push({
                        key: arguments[i],
                        component: component
                    });
                }
            }

            Str.get_strings(strings)
            .done((s) => {
                if (s.length > 1) {
                    Notification.alert(s[0], s[1]);
                } else {
                    Notification.alert('', s[0]);
                }
            })
            .fail(Notification.exception);
        }
    };
});
