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
    'core/config',
    'block_dixeo_designer/progress_utils'
], function(Ajax, $, Template, Notification, Str, Config, ProgressUtils) {
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

            // Regenerate fast-path UX:
            // When editing an existing job, disable the Regenerate button until the
            // prompt/template/files actually change.
            this.initRegenerateChangeTracking();

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
        regenChangeTrackingEnabled: false,
        regenInitialSignature: null,
        initRegenerateChangeTracking: function() {
            if (!generateStructure) {
                return;
            }

            const existingJobAttr = generateStructure.dataset.existingJob;
            const isExistingJob = existingJobAttr === 'true' || existingJobAttr === '1';
            if (!isExistingJob) {
                return;
            }

            this.regenChangeTrackingEnabled = true;
            this.regenInitialSignature = this.getSubmissionSignature();

            // Disable until changes are detected.
            this.syncRegenerateButtonState();

            // Prompt changes enable the button.
            if (courseDescription) {
                courseDescription.addEventListener('input', () => {
                    this.syncRegenerateButtonState();
                });
            }

            // Template changes enable the button.
            if (templateSelect) {
                templateSelect.addEventListener('change', () => {
                    this.syncRegenerateButtonState();
                });
            }

            // File list changes enable the button (upload, delete, re-render).
            if (filesContainer) {
                const observer = new MutationObserver(() => {
                    this.syncRegenerateButtonState();
                });
                observer.observe(filesContainer, {
                    subtree: true,
                    childList: true,
                    attributes: true,
                    attributeFilter: ['class']
                });
                // Store observer to disconnect later if needed.
                this.regenFilesMutationObserver = observer;
            }
        },
        getSubmissionSignature: function() {
            const promptVal = courseDescription ? courseDescription.value.trim() : '';
            const templateVal = templateSelect ? (templateSelect.value || '') : '';

            let filePart = '';
            if (filesContainer && !filesContainer.classList.contains('d-none')) {
                const items = Array.from(filesContainer.querySelectorAll('.file-item'));
                const fileIds = items.map((el) => el.dataset.fileId || '').filter(Boolean).sort();
                // Also include the displayed text to detect unusual id-less cases.
                const fileText = items.map((el) => el.textContent.trim()).sort();
                filePart = JSON.stringify({fileIds: fileIds, fileText: fileText});
            }

            // Signature must be deterministic.
            return JSON.stringify({
                prompt: promptVal,
                template: templateVal,
                files: filePart
            });
        },
        syncRegenerateButtonState: function() {
            if (!this.regenChangeTrackingEnabled || !generateStructure) {
                return;
            }
            const currentSig = this.getSubmissionSignature();
            const changed = currentSig !== this.regenInitialSignature;
            generateStructure.disabled = !changed;
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
                self.clearAllProgressPolls();
                self.resetProgress();
            })
            .catch(function(err) {
                self.clearAllProgressPolls();
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

            // Remember where the user initiated generation so "Generate new course"
            // can redirect back correctly after completion.
            try {
                sessionStorage.setItem(
                    ProgressUtils.SESSION_RETURN_TO_KEY,
                    window.location.href
                );
                // Also store the job so the designer page can decide
                // whether the redirect value is still relevant.
                sessionStorage.setItem(
                    ProgressUtils.SESSION_RETURN_TO_JOBID_KEY,
                    String(this.getJobId())
                );
            } catch (e) {
                // Ignore storage failures.
            }

            const courseDescriptionValue = courseDescription.value.trim();
            if (courseDescriptionValue === '' && !this.hasServerFiles()) {
                this.notify('invalidinput', 'descriptionorfilesrequired');
                return;
            }

            // Remote API requires instructions >= 20 characters.
            // If the user provided a non-empty description, block early in the client.
            const minInstructionLen = ProgressUtils.MIN_INSTRUCTIONS_LEN;
            if (courseDescriptionValue !== '' && courseDescriptionValue.length < minInstructionLen) {
                Str.get_string('designer_instructions_too_short', 'block_dixeo_designer', {min: minInstructionLen})
                    .then(function(msg) {
                        Notification.alert('', msg);
                    });
                return;
            }

            if (this.progress === 0) {
                this.startProgress();
            }

            // On designer.php, regeneration runs while the editor/footer stay visible.
            // Lock the editor/footer with a backdrop so users can't click around.
            if (reviewStructure) {
                this.lockDesignerUI();
            }

            // reviewStructure true = design only (no course), false = create full course. skip=1 means create course.
            const createcourse = !reviewStructure;

            const isLocalUploading = Boolean(
                filesContainer && filesContainer.classList.contains('file-names-loading')
            );
            if (!isLocalUploading) {
                const localFileCount = filesContainer
                    ? filesContainer.querySelectorAll('.file-item').length
                    : 0;
                if (localFileCount > 0) {
                    // Show the "current file" index starting at 1.
                    this.setStepLabel(1, 'Processing files (1/' + localFileCount + ')');
                    const self = this;
                    Str.get_string('step_uploading_files_count', 'block_dixeo_designer', {
                        current: 1,
                        total: localFileCount
                    }).then(function(stepStr) {
                        self.setStepLabel(1, stepStr);
                    });
                } else {
                    // If there are no files, the first step should show we are processing only the prompt.
                    const self = this;
                    Str.get_string('step_processing_prompt', 'block_dixeo_designer').then(function(label) {
                        self.setStepLabel(1, label);
                    });
                }
            }

            // 0–20%: Processing files.
            // The local file upload stage runs independently (step 1 shows x/y progress),
            // and once that stage is considered done and the backend call returns,
            // the overall progress bar advances into the 20% step-2 band.
            // Start at 0; step 1 will be driven by file-sync polling.
            this.setProgress(0, true);

            Ajax.call([{
                methodname: 'block_dixeo_designer_start_generation',
                args: {
                    job_id: this.getJobId(),
                    description: courseDescriptionValue,
                    templateid: (templateSelect && templateSelect.value !== '') ? templateSelect.value : null,
                    sesskey: M.cfg.sesskey
                },
            }])[0]
            .then((startResp) => {
                // Regenerate no-op fast-path:
                // If backend determined prompt/template/files are identical and the
                // latest structure is already saved, reload the designer immediately
                // without polling file sync or submitting remote generation.
                if (reviewStructure && startResp && startResp.noop) {
                    this.unlockDesignerUI();
                    window.location.href = Config.wwwroot + '/blocks/dixeo_designer/designer.php?id=' + this.getJobId();
                    return;
                }
                // Step 1 (0–20%) is driven by the remote file sync polling.
                // Step 2 starts only after the file sync becomes synchronized/none.
                this.setProgress(0, true);
                this.startStep2Progress(createcourse);
            })
            .catch(async error => {
                this.resetProgress();
                this.clearAllProgressPolls();
                const errorTitle = await Str.get_string('error_title', 'block_dixeo_designer');
                Notification.alert(errorTitle, error.message);
            });
        },
        filesyncPollIntervalId: null,
        structurePollIntervalId: null,
        step2FakeIntervalId: null,
        step2StartMs: null,
        designerUiLockEl: null,
        designerUiLockUpdateHandler: null,
        clearAllProgressPolls: function() {
            if (this.filesyncPollIntervalId) {
                clearInterval(this.filesyncPollIntervalId);
                this.filesyncPollIntervalId = null;
            }
            if (this.structurePollIntervalId) {
                clearInterval(this.structurePollIntervalId);
                this.structurePollIntervalId = null;
            }
            if (this.step2FakeIntervalId) {
                clearInterval(this.step2FakeIntervalId);
                this.step2FakeIntervalId = null;
            }
            this.step2StartMs = null;
        },
        startStep2Progress: function(createcourse) {
            const self = this;

            /**
             * Starts the slow fake progress timer for step 2 (20% -> 37%).
             * This keeps the UI moving while the backend prepares the
             * remote structure generation.
             */
            function startStep2Fake() {
                // Step 2 fake progress: 20% -> 37% over 90 seconds.
                // We keep it slow so the bar feels alive while the remote
                // structure job is being prepared.
                self.step2StartMs = Date.now();
                self.step2FakeIntervalId = setInterval(function() {
                    if (self.step2StartMs === null) {
                        return;
                    }
                    const elapsed = Date.now() - self.step2StartMs;
                    const t = Math.min(1, elapsed / 90000);
                    const fake = 20 + 17 * t;
                    if (self.progress < fake) {
                        self.setProgress(fake);
                    }
                }, 500);
            }

            let submitted = false;

            const pollFileSync = function() {
                Ajax.call([{
                    methodname: 'block_dixeo_designer_get_filesync_status',
                    args: {
                        job_id: self.getJobId(),
                        sesskey: M.cfg.sesskey
                    },
                }])[0]
                .then(function(data) {
                    if (data && data.errormessage) {
                        self.clearAllProgressPolls();
                        self.resetProgress();
                        Notification.alert('', data.errormessage);
                        return;
                    }

                    const pct = Number.isFinite(data.progresspercent) ? data.progresspercent : null;
                    const total = Number.isFinite(data.filestotal) ? data.filestotal : null;
                    const done = Number.isFinite(data.filescompleted) ? data.filescompleted : null;

                    // Step 1 progress band: 0% -> 20% based on file-sync percent.
                    if (pct !== null) {
                        const mapped = (Math.max(0, Math.min(100, pct)) / 100) * 20;
                        if (self.progress < mapped) {
                            self.setProgress(mapped);
                        }
                    }

                    // Step 1 label:
                    // - while syncing, show "current file" index as (filescompleted + 1)
                    //   so the UI starts at 1/total.
                    // - otherwise show "Processing prompt" when there are no files.
                    if (total !== null && total > 0 && done !== null) {
                        let currentIndex = done;
                        if (data.status === 'syncing' && done < total) {
                            currentIndex = done + 1;
                        }
                        if (currentIndex < 1) {
                            currentIndex = 1;
                        }

                        Str.get_string('step_uploading_files_count', 'block_dixeo_designer', {
                            current: currentIndex,
                            total: total
                        }).then(function(stepStr) {
                            self.setStepLabel(1, stepStr);
                        });
                    } else {
                        Str.get_string('step_processing_prompt', 'block_dixeo_designer').then(function(label) {
                            self.setStepLabel(1, label);
                        });
                    }

                    if (!submitted && (data.status === 'synchronized' || data.status === 'none')) {
                        submitted = true;
                        // Jump into the step-2 band immediately (step 2 is 20-40%).
                        self.setProgress(21, true);
                        startStep2Fake();
                        self.submitStructureAndPoll(createcourse);
                    }
                })
                .catch(function() {
                    // If file sync polling fails, keep fake progress running and continue.
                });
            };

            pollFileSync();
            this.filesyncPollIntervalId = setInterval(pollFileSync, 2000);
        },
        submitStructureAndPoll: function(createcourse) {
            const self = this;

            if (this.filesyncPollIntervalId) {
                clearInterval(this.filesyncPollIntervalId);
                this.filesyncPollIntervalId = null;
            }

            Str.get_string('step_generating_structure', 'block_dixeo_designer').then(function(str) {
                self.setStepLabel(2, str);
            });

            Ajax.call([{
                methodname: 'block_dixeo_designer_submit_structure_job',
                args: {
                    job_id: self.getJobId(),
                    sesskey: M.cfg.sesskey
                },
            }])[0]
            .then(function() {
                self.pollStructureCompletion(createcourse);
            })
            .catch(function(err) {
                self.clearAllProgressPolls();
                self.resetProgress();
                Notification.alert('', err.message || 'Could not start structure generation');
            });
        },
        pollStructureCompletion: function(createcourse) {
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
                        self.clearAllProgressPolls();
                        self.resetProgress();
                        Notification.alert('', data.error || 'Generation failed');
                        return;
                    }

                    if (!data.completed) {
                        return;
                    }

                    self.clearAllProgressPolls();
                    self.setProgress(40);
                    const delayMs = createcourse ? 500 : 1000;
                    setTimeout(function() {
                        if (createcourse) {
                            Ajax.call([{
                                methodname: 'block_dixeo_designer_finalize_course',
                                args: {
                                    job_id: self.getJobId(),
                                    createcourse: true,
                                    sesskey: M.cfg.sesskey
                                },
                            }])[0].catch(function(err) {
                                self.resetProgress();
                                Notification.alert('', err.message || 'Finalize failed');
                            });
                            self.pollFinalizeProgress();
                        } else {
                            var structureJson = (typeof data.result === 'string')
                                ? data.result
                                : JSON.stringify(data.result || {});
                            Ajax.call([{
                                methodname: 'block_dixeo_designer_save_structure',
                                args: {
                                    job_id: self.getJobId(),
                                    structure: structureJson
                                },
                            }])[0]
                            .then(function() {
                                window.location.href = Config.wwwroot + '/blocks/dixeo_designer/designer.php?id=' + self.getJobId();
                            })
                            .catch(function(err) {
                                self.resetProgress();
                                Notification.alert('', err.message || 'Could not save structure');
                            });
                        }
                    }, delayMs);
                })
                .catch(function(err) {
                    self.clearAllProgressPolls();
                    self.resetProgress();
                    Notification.alert('', err.message || 'Status check failed');
                });
            };

            poll();
            this.structurePollIntervalId = setInterval(poll, 3000);
        },
        finalizePollIntervalId: null,
        clearFinalizePoll: function() {
            if (this.finalizePollIntervalId) {
                clearInterval(this.finalizePollIntervalId);
                this.finalizePollIntervalId = null;
            }
        },
        pollFinalizeProgress: function() {
            const self = this;
            let pollInFlight = false;
            const poll = function() {
                if (pollInFlight) {
                    return;
                }
                pollInFlight = true;
                Ajax.call([{
                    methodname: 'block_dixeo_designer_get_finalize_progress',
                    args: {
                        job_id: self.getJobId(),
                        sesskey: M.cfg.sesskey
                    },
                }])[0]
                .then(function(data) {
                    if (data.phase === ProgressUtils.PHASE_GENERATING_CONTENT && data.section_total > 0) {
                        const total = Number(data.section_total) || 0;
                        const sectionIndex = Number(data.section_index) || 0;
                        // Label should show the currently in-progress section (1-based).
                        const current = Math.min(total, Math.max(1, sectionIndex));
                        // Progress bar should reflect completed sections only.
                        const completed = Math.max(0, current - 1);
                        const pct = 40 + 40 * (completed / total);
                        self.setProgress(pct);
                        Str.get_string('step_generating_content_count', 'block_dixeo_designer', {
                            current: current,
                            total: total
                        }).then(function(str) {
                            self.setStepLabel(3, str);
                        });
                    } else if (data.phase === ProgressUtils.PHASE_FINALIZING) {
                        self.setProgress(80);
                    } else if (data.phase === ProgressUtils.PHASE_DONE && data.courseid) {
                        self.clearFinalizePoll();
                        self.setProgress(100);
                        self.finishProgress(data.courseid, data.coursename);
                    }
                })
                .catch(function() {})
                .then(function() {
                    pollInFlight = false;
                });
            };
            poll();
            this.finalizePollIntervalId = setInterval(poll, 2000);
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
        setFileNamesLoading: function(loading, options) {
            if (!filesContainer) {
                return;
            }
            options = options || {};
            const stepText = options.stepText || 'Uploading files (0/1)';
            const mbLine = options.mbLine || '';
            const progressPct = options.progressPct;
            if (loading) {
                filesContainer.classList.remove('d-none');
                filesContainer.classList.add('file-names-loading');
                let html = '<div class="file-names-loading-state">' +
                    '<div class="file-names-loading-row">' +
                    '<span class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></span>' +
                    '<span class="file-names-loading-text">' + stepText + '</span></div>';
                if (mbLine) {
                    html += '<div class="file-names-loading-row">' +
                        '<span class="file-names-loading-mb text-muted small">' + mbLine + '</span></div>';
                }
                if (progressPct !== undefined && progressPct >= 0) {
                    const pctRound = Math.round(progressPct);
                    const pctStyle = Math.min(100, progressPct) + '%';
                    html += '<div class="file-names-loading-row">' +
                        '<div class="file-upload-progress" role="progressbar" aria-valuemin="0" ' +
                        'aria-valuemax="100" aria-valuenow="' + pctRound + '">' +
                        '<div class="file-upload-progress-bar" style="width: ' + pctStyle + ';"></div></div></div>';
                }
                html += '</div>';
                filesContainer.innerHTML = html;
            } else {
                filesContainer.classList.remove('file-names-loading');
            }
        },
        updateFileUploadProgress: function(stepText, mbLine, progressPct) {
            if (!filesContainer || !filesContainer.classList.contains('file-names-loading')) {
                return;
            }
            const textEl = filesContainer.querySelector('.file-names-loading-text');
            if (textEl) {
                textEl.textContent = stepText;
            }
            let mbEl = filesContainer.querySelector('.file-names-loading-mb');
            if (mbLine !== undefined) {
                if (!mbEl) {
                    const state = filesContainer.querySelector('.file-names-loading-state');
                    if (state) {
                        const mbRow = document.createElement('div');
                        mbRow.className = 'file-names-loading-row';
                        mbEl = document.createElement('span');
                        mbEl.className = 'file-names-loading-mb text-muted small';
                        mbEl.textContent = mbLine;
                        mbRow.appendChild(mbEl);
                        state.appendChild(mbRow);
                    }
                } else {
                    mbEl.textContent = mbLine;
                }
            }
            let barWrap = filesContainer.querySelector('.file-upload-progress');
            if (progressPct !== undefined && progressPct >= 0) {
                if (!barWrap) {
                    const state = filesContainer.querySelector('.file-names-loading-state');
                    if (state) {
                        const row = document.createElement('div');
                        row.className = 'file-names-loading-row';
                        barWrap = document.createElement('div');
                        barWrap.className = 'file-upload-progress';
                        barWrap.setAttribute('role', 'progressbar');
                        barWrap.setAttribute('aria-valuemin', '0');
                        barWrap.setAttribute('aria-valuemax', '100');
                        barWrap.innerHTML = '<div class="file-upload-progress-bar"></div>';
                        row.appendChild(barWrap);
                        state.appendChild(row);
                    }
                }
                barWrap.setAttribute('aria-valuenow', Math.round(progressPct));
                const bar = barWrap.querySelector('.file-upload-progress-bar');
                if (bar) {
                    bar.style.width = Math.min(100, progressPct) + '%';
                }
            }
        },
        transferFiles: async function(newFiles) {
            if (!newFiles || newFiles.length === 0) {
                return;
            }

            const files = Array.from(newFiles);
            const totalFiles = files.length;
            const totalBytes = files.reduce((sum, f) => sum + (f.size || 0), 0);
            const totalMB = (totalBytes / (1024 * 1024)).toFixed(2);
            const self = this;

            const formatMB = function(bytes) {
                return (bytes / (1024 * 1024)).toFixed(2);
            };

            // Show upload progress immediately so it is visible (do not wait for lang string).
            self.setFileNamesLoading(true, {
                stepText: 'Processing files (1/' + totalFiles + ')',
                mbLine: '0 MB / ' + totalMB + ' MB',
                progressPct: 0
            });
            self.setStepLabel(1, 'Processing files (1/' + totalFiles + ')');
            Str.get_string('step_uploading_files_count', 'block_dixeo_designer', {
                current: 1,
                total: totalFiles
            }).then(function(stepStr) {
                self.setStepLabel(1, stepStr);
            });

            let bytesUploaded = 0;
            let lastContext = null;

            /**
             * Upload a single file via XHR and return the response context (or null).
             * @param {File} file The file to upload
             * @param {number} fileNum 1-based file index for progress text
             * @param {number} bytesSoFar Bytes already uploaded (for progress)
             * @param {number} totalBytesVal Total bytes to upload
             * @param {number} totalFilesVal Total number of files
             * @param {string} totalMBVal Total size in MB string for display
             * @returns {Promise<object|null>} Resolves with file context from response or null
             */
            function doUploadOneFile(file, fileNum, bytesSoFar, totalBytesVal, totalFilesVal, totalMBVal) {
                return new Promise(function(resolve, reject) {
                    const formData = new FormData();
                    formData.append('sesskey', M.cfg.sesskey);
                    formData.append('jobid', self.getJobId());
                    formData.append('files[]', file);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', Config.wwwroot + '/blocks/dixeo_designer/upload_submission_files.php');

                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const totalSoFar = bytesSoFar + e.loaded;
                            const pct = totalBytesVal > 0 ? (totalSoFar / totalBytesVal) * 100 : 0;
                            const uploadedMB = formatMB(totalSoFar);
                            Str.get_string('step_uploading_files_count', 'block_dixeo_designer', {
                                current: fileNum,
                                total: totalFilesVal
                            }).then(function(stepStr) {
                                self.setStepLabel(1, stepStr);
                                self.updateFileUploadProgress(stepStr, uploadedMB + ' MB / ' + totalMBVal + ' MB', pct);
                            });
                        }
                    });

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                if (data.success && data.context) {
                                    resolve(data.context);
                                } else {
                                    resolve(null);
                                }
                            } catch (err) {
                                reject(new Error('Upload failed'));
                            }
                        } else {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                reject(new Error(data.message || 'Upload failed'));
                            } catch (err) {
                                reject(new Error('Upload failed'));
                            }
                        }
                    };
                    xhr.onerror = function() {
                        reject(new Error('Upload failed'));
                    };
                    xhr.send(formData);
                });
            }

            try {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileNum = i + 1;
                    const context = await doUploadOneFile(file, fileNum, bytesUploaded, totalBytes, totalFiles, totalMB);
                    if (context) {
                        lastContext = context;
                    }
                    bytesUploaded += file.size || 0;
                }

                self.setFileNamesLoading(false);
                if (lastContext) {
                    self.displayFileNames(lastContext);
                }
            } catch (error) {
                self.setFileNamesLoading(false);
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
            this.setProgress(0, true);
            this.setActiveStep(1);
        },
        lockDesignerUI: function() {
            if (this.designerUiLockEl) {
                return;
            }

            const wrapper = document.querySelector('.dixeo-designer-block-wrapper');
            // Use the inner block bottom (not the wrapper toggle) so the overlay
            // starts below the designer UI (progress/debug content remains visible).
            const blockContainer = document.querySelector(
                '.dixeo-designer-block-wrapper .block_dixeo_designer.block-container'
            );
            // Only lock when the fixed editor/footer exist (designer.php).
            const editorFooter = document.querySelector('#page-blocks-dixeo_designer-designer .editor-toolbar-footer');
            if (!wrapper || !editorFooter) {
                return;
            }

            const el = document.createElement('div');
            el.className = 'dixeo-designer-ui-lock-backdrop';
            el.setAttribute('aria-hidden', 'true');

            // Position the overlay so it starts below the block (so progress UI remains visible).
            const rectAnchor = blockContainer || wrapper;
            const initialTop = rectAnchor.getBoundingClientRect().bottom;
            el.style.top = initialTop + 'px';

            document.body.appendChild(el);
            this.designerUiLockEl = el;

            let ticking = false;
            const self = this;

            const updateTop = function() {
                if (!self.designerUiLockEl) {
                    return;
                }
                const rect = rectAnchor.getBoundingClientRect();
                self.designerUiLockEl.style.top = rect.bottom + 'px';
            };

            this.designerUiLockUpdateHandler = function() {
                if (ticking) {
                    return;
                }
                ticking = true;
                requestAnimationFrame(function() {
                    ticking = false;
                    updateTop();
                });
            };

            window.addEventListener('resize', this.designerUiLockUpdateHandler);
            window.addEventListener('scroll', this.designerUiLockUpdateHandler, true);

            // Ensure the correct top is set even if layout changes immediately.
            updateTop();
        },
        unlockDesignerUI: function() {
            if (!this.designerUiLockEl) {
                return;
            }

            if (this.designerUiLockUpdateHandler) {
                window.removeEventListener('resize', this.designerUiLockUpdateHandler);
                window.removeEventListener('scroll', this.designerUiLockUpdateHandler, true);
            }

            this.designerUiLockEl.remove();
            this.designerUiLockEl = null;
            this.designerUiLockUpdateHandler = null;
        },
        finishProgress: async function(courseid, coursename) {
            this.setProgress(100);
            setTimeout(() => {
                let context = {
                    courseid: courseid,
                    coursename: coursename,
                    wwwroot: Config.wwwroot
                };

                // "Generate new course" redirect:
                // Prefer the original generation page stored in sessionStorage.
                // If it was the designer page, go to a fresh designer.php (no id).
                const freshDesignerUrl = Config.wwwroot + '/blocks/dixeo_designer/designer.php';
                let returnTo = null;
                try {
                returnTo = sessionStorage.getItem(ProgressUtils.SESSION_RETURN_TO_KEY);
                } catch (e) {
                    returnTo = null;
                }
                const currentIsDesignerPage = window.location.pathname.indexOf('/blocks/dixeo_designer/designer.php') !== -1;
                const returnToIsDesigner = returnTo && returnTo.indexOf('/blocks/dixeo_designer/designer.php') !== -1;

                if (returnTo) {
                    context.generate_another_url = returnToIsDesigner ? freshDesignerUrl : returnTo;
                } else {
                    context.generate_another_url = currentIsDesignerPage ? freshDesignerUrl : (Config.wwwroot + '/my/');
                }

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
            this.unlockDesignerUI();
            this.clearAllProgressPolls();
            this.clearFinalizePoll();
            if (generateCourse) {
                generateCourse.disabled = false;
            }
            // Keep Regenerate disabled unless prompt/template/files changed.
            this.syncRegenerateButtonState();
            promptContainer.classList.replace('d-none', 'd-block');
            generationContainer.classList.replace('d-block', 'd-none');

            let successContainer = generatorForm.querySelector('#success_message_container');
            if (successContainer) {
                successContainer.remove();
            }

            this.setProgress(0, true);
        },
        setProgress: function(progress) {
            const force = arguments.length > 1 ? Boolean(arguments[1]) : false;
            const nextProgress = Math.min(100, Math.max(0, progress));

            // Prevent progress from moving backwards during polling/animation.
            // Resets can explicitly force the value down to 0.
            if (!force && nextProgress < this.progress) {
                return;
            }

            this.progress = nextProgress;

            const container = generationContainer || document.querySelector('.designer-finalize-progress');
            if (!container) {
                return;
            }
            const progressBar = container.querySelector('.s-progress--bar');
            if (progressBar) {
                progressBar.style.width = `${this.progress}%`;
                progressBar.setAttribute('aria-valuenow', this.progress);
                if (this.progress >= 100) {
                    progressBar.classList.add('done');
                } else {
                    progressBar.classList.remove('done');
                }
            }

            // Tie highlighting to current progress percentage.
            this.updateActiveStepFromProgress();
        },
        updateActiveStepFromProgress: function() {
            const step = ProgressUtils.getActiveStepFromProgress(this.progress);
            this.setActiveStep(step);
        },
        setActiveStep: function(step) {
            const container = generationContainer || document.querySelector('.designer-finalize-progress');
            if (!container) {
                return;
            }
            container.querySelectorAll('.generation-step').forEach(function(el) {
                el.classList.remove('active');
                if (parseInt(el.getAttribute('data-step'), 10) === step) {
                    el.classList.add('active');
                }
            });
        },
        setStepLabel: function(step, text) {
            const container = generationContainer || document.querySelector('.designer-finalize-progress');
            if (!container) {
                return;
            }
            const el = container.querySelector('.generation-step[data-step="' + step + '"]');
            if (el) {
                el.textContent = text || '';
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
