define([
    'jquery',
    'core/templates',
    'core/notification',
    'core/str'
], function($, Template, Notification, Str) {
    const generatorForm         = document.getElementById('edai_course_generator_form');
    const promptContainer       = generatorForm.querySelector('.prompt-container');
    const promptForm            = generatorForm.querySelector('#prompt-form');
    const carousel              = generatorForm.querySelector('#carousel-controls');
    const generationContainer   = generatorForm.querySelector('.generation-container');
    const courseDescription     = generatorForm.querySelector('#course_description');
    const generateCourse        = generatorForm.querySelector('#generate_course');
    const tempCourseFiles       = generatorForm.querySelector('#temp_course_files');
    const courseFiles           = generatorForm.querySelector('#course_files');
    const filesContainer        = generatorForm.querySelector('#file_names');
    const spinner               = generatorForm.querySelector('#progress-spinner');

    return {
        init: function() {
            this.progress = 0;

            this.adjustDescriptionHeight();
            this.handleDragAndDrop();

            // Add event listener to generate course button.
            generateCourse.addEventListener('click', (event) => {
                event.preventDefault();

                // Check if the course description is filled or files are uploaded.
                if (courseDescription.value.trim() === '' && courseFiles.files.length === 0) {
                    this.notify('invalidinput', 'descriptionorfilesrequired');
                    return;
                }

                if (this.progress === 0) {
                    this.startProgress();
                }

                // Real implementation. To be tested.
                // Currently this block is 100% independent. Should it require other plugins? 
                /*
                const formdata = new FormData();
                formdata.append('description', courseDescription.value);
                for (let i = 0; i < courseFiles.files.length; i++) {
                    formdata.append('course_files[]', courseFiles.files[i]);
                }

                fetch(`${M.cfg.wwwroot}/local/edai_course_generator_client/ajax/generate_course.php`, {
                    method: 'POST',
                    body: formdata
                })
                .then(response => {
                    return response.json().then(data => {
                        if (!response.ok) {
                            throw new Error(
                                response.status === 402 ? data.error : 'Oops, error during course creation. Please try again.'
                            );
                        }
                        return data;
                    });
                })
                .then(courseid => {
                    this.finishProgress(courseid);
                })
                .catch(error => {
                    Notification.alert('', error.message);
                });
                */
                
                // For non-functional demo only. Complete after X seconds.
                setTimeout(() => {
                    let courseid = 2;
                    this.finishProgress(courseid);
                }, 5000);
            });
        },
        adjustDescriptionHeight: function() {
            // Adjust course description height.
            const initialheight = courseDescription.clientHeight;
            courseDescription.addEventListener('input', function() {
                const maxlines = 9;
                const lineheight = parseFloat(getComputedStyle(courseDescription).lineHeight);
                const lines = courseDescription.value.split('\n').length;

                let newheight;
                if (lines * lineheight < initialheight) {
                    newheight = initialheight;
                } else if (lines <= maxlines) {
                    newheight = lines * lineheight;
                    courseDescription.style.overflowY = 'hidden';
                } else {
                    newheight = maxlines * lineheight;
                    courseDescription.style.overflowY = 'scroll';
                }

                // Additional padding.
                newheight += newheight > initialheight ? 14 : 0;
                courseDescription.style.height = newheight + 'px';
            });
        },
        clearAllFiles: function() {
            let dataTransfer = new DataTransfer();
            courseFiles.files = dataTransfer.files;
            this.displayFileNames();
        },
        transferFiles: function(newFiles) {
            // Validate files.
            const maxfilesize = 20 * 1024 * 1024;  // 20 MB
            const maxtotalsize = 50 * 1024 * 1024; // 50 MB
            const allowedtypes = [
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/pdf',
                'text/plain'
            ];

            let totalSize = 0;
            let existingFiles = Array.from(courseFiles.files);

            // Include the size of files already added to courseFiles.
            for (let file of existingFiles) {
                totalSize += file.size;
            }

            for (let file of newFiles) {
                // Check file type.
                if (!allowedtypes.includes(file.type)) {
                    this.notify('uploaderror', ['filetypeinvalid', file.name]);
                    return;
                }
                // Check file size.
                totalSize += file.size;
                if (file.size > maxfilesize) {
                    this.notify('uploaderror', ['filetoolarge', file.name]);
                    return;
                }
                // Check total size.
                if (totalSize > maxtotalsize) {
                    this.notify('uploaderror', 'totaltoolarge');
                    return;
                }
            }

            // Combine existing files with new files.
            for (let file of newFiles) {
                // Check if file already exists.
                if (!existingFiles.some(existingFile => existingFile.name === file.name && existingFile.size === file.size)) {
                    existingFiles.push(file);
                }
            }

            // Add all files to DataTransfer
            let dataTransfer = new DataTransfer();
            for (let file of existingFiles) {
                dataTransfer.items.add(file);
            }

            courseFiles.files = dataTransfer.files;
            this.displayFileNames();
        },
        handleDragAndDrop: function() {
            // Drag over and leave over prompt form.
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

            // Apply drop listeners to all child elements of prompt form.
            this.dropOnChildElements(promptForm);
            // Move files from temp to course files.
            tempCourseFiles.addEventListener('change', () => {
                let newFiles = Array.from(tempCourseFiles.files);
                this.transferFiles(newFiles);
            });
        },
        dropOnChildElements: function(node) {
            node.childNodes.forEach(child => {
                this.dropOnChildElements(child);

                child.addEventListener("dragover", (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                });

                child.addEventListener("drop", (event) => {
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
            generateCourse.disabled = true;
            spinner.classList.add('spinner-border');
            promptContainer.classList.replace('d-block', 'd-none');
            generationContainer.classList.replace('d-none', 'd-block');

            let interval = setInterval(() => {
                if (this.progress >= 90) {
                    clearInterval(interval);
                }

                // Increase by a random amount every second
                let increment = Math.floor(Math.random() * 5);
                this.setProgress(this.progress + increment);
            }, 1000);
        },
        finishProgress: async function(courseid) {
            spinner.classList.remove('spinner-border');
            
            Template.render('block_course_generator/success_message', {courseid: courseid}).then((html) => {
                generationContainer.innerHTML += html;

                let resetLink = generatorForm.querySelector('.reset-prompt');
                if (resetLink) {
                    resetLink.addEventListener('click', () => {
                        this.resetProgress();
                    });
                }
            }).catch((error) => {
                console.error("Error rendering template: ", error);
            });

            this.setProgress(100);
        },
        resetProgress: function() {
            generateCourse.disabled = false;
            spinner.classList.remove('spinner-border');
            promptContainer.classList.replace('d-none', 'd-block');
            generationContainer.classList.replace('d-block', 'd-none');

            courseDescription.value = '';

            let successContainer = generatorForm.querySelector('#success_message_container');
            if (successContainer) {
                successContainer.remove();
            }

            this.clearAllFiles();

            this.setProgress(0);
        },
        setProgress: function(progress) {
            this.progress = progress;

            let progressBar = generatorForm.querySelector('.s-progress--bar');
            progressBar.style.width = `${progress}%`;
            if (progress >= 100) {
                progressBar.classList.add('done');
            } else {
                progressBar.classList.remove('done');
            }
        },
        displayFileNames: function() {
            let contextFiles = [];
            let totalSize = 0;
            for (let i = 0; i < courseFiles.files.length; i++) {
                const file = courseFiles.files[i];
                totalSize += file.size;
                contextFiles.push({
                    name: file.name,
                    size: this.formatFilesize(file.size),
                });
            };
            let hasFiles = contextFiles.length > 0;
            let context = {
                hasFiles: hasFiles,
                totalSize: this.formatFilesize(totalSize),
                files: contextFiles
            };

            if (hasFiles) {
                carousel.classList.add('d-none');
            } else {
                carousel.classList.remove('d-none');
            }

            Template.render('block_course_generator/filenames', context).then((html) => {
                filesContainer.innerHTML = html;

                let deleteIcons = filesContainer.querySelectorAll('.delete-icon');
                deleteIcons.forEach((deleteIcon, index) => {
                    let that = this;
                    let toDelete = courseFiles.files[index].name;

                    deleteIcon.addEventListener('click', function() {
                        // Remove file from display.
                        let toolTipId = deleteIcon.getAttribute('aria-describedby');
                        document.getElementById(toolTipId).remove();

                        // Remove file from course files.
                        let dataTransfer = new DataTransfer();
                        for (let i = 0; i < courseFiles.files.length; i++) {
                            if (courseFiles.files[i].name !== toDelete) {
                                dataTransfer.items.add(courseFiles.files[i]);
                            }
                        }
                    
                        courseFiles.files = dataTransfer.files;
                        that.displayFileNames();
                    });
                });

            }).catch((error) => {
                console.error("Error rendering template: ", error);
            });
        },
        formatFilesize: (size) => {
            const units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
            let unitIndex = 0;
            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }
            return `${size.toFixed(1)} ${units[unitIndex]}`;
        },
        notify: async function() {
            let strings = [];
            let component = 'block_course_generator';

            for (let i = 0; i < arguments.length; i++) {
                if (Array.isArray(arguments[i])) {
                    strings.push({
                        key: arguments[i][0],
                        component: component,
                        param: arguments[i][1]
                    });
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
