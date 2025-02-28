define([
    'jquery',
    'core/ajax',
], function($, Ajax) {
    const generatorForm         = document.getElementById('edai_course_generator_form');
    const promptContainer       = generatorForm.querySelector('.prompt-container');
    const promptForm            = generatorForm.querySelector('#prompt-form');
    const generationContainer   = generatorForm.querySelector('.generation-container');
    const courseDescription     = generatorForm.querySelector('#course_description');
    const generateCourse        = generatorForm.querySelector('#generate_course');
    const tempCourseFiles       = generatorForm.querySelector('#temp_course_files');
    const courseFiles           = generatorForm.querySelector('#course_files');
    const filesContainer        = generatorForm.querySelector('#file_names');
    const spinner               = generatorForm.querySelector('#progress-spinner');
    const loader                = generatorForm.querySelector('#loader');
    const successContainer      = generatorForm.querySelector('#success_message_container');

    return {
        init: function() {
            this.progress = 0;

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

            // Prevent default action and start progress.
            generateCourse.addEventListener('click', (event) => {
                event.preventDefault();
                loader.classList.replace('d-none', 'd-block');
                if (this.progress === 0) {
                    // Begin progress animation.
                    this.startProgress(loader);
                    spinner.classList.add('spinner-border');
                    generationContainer.classList.replace('d-none', 'd-block');
                } else if (this.progress < 100) {
                    // Finish progress.
                    this.setProgress(loader, 100);
                    successContainer.classList.replace('d-none', 'd-block');
                    spinner.classList.remove('spinner-border');
                } else {
                    // Reset progress.
                    this.setProgress(loader, 0);
                    successContainer.classList.replace('d-block', 'd-none');
                    spinner.classList.remove('spinner-border');
                    generationContainer.classList.replace('d-block', 'd-none');
                }
            });

            // Drag over prompt input.
            dragEnterCounter = 0;
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
        transferFiles: function(newFiles) {
            // Combine existing files with new files.
            let existingFiles = Array.from(courseFiles.files);
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
        startProgress: function(loader) {
            let interval = setInterval(() => {
                if (this.progress >= 90) {
                    clearInterval(interval);
                }

                // Increase by a random amount every second
                let increment = Math.floor(Math.random() * 5);
                this.setProgress(loader, this.progress + increment);
            }, 1000);
        },
        setProgress: function(loader, progress) {
            let progressBar = loader.querySelector('.s-progress--bar');
            progressBar.style.width = `${progress}%`;
            if (progress >= 100) {
                progressBar.classList.add('done');
            } else {
                progressBar.classList.remove('done');
            }
            this.progress = progress;
        },
        displayFileNames: function() {
            filesContainer.innerHTML = '';
            for (let i = 0; i < courseFiles.files.length; i++) {
                const file = courseFiles.files[i];
                const fileItem = document.createElement('div');
                fileItem.textContent = file.name;
                fileItem.style.fontWeight = 'bold';
                filesContainer.appendChild(fileItem);
            }
        }
    };
});
