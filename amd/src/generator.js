define([
    'jquery',
    'core/ajax',
], function($, Ajax) {
    const generatorForm         = document.getElementById('edai_course_generator_form');
    const promptContainer       = generatorForm.querySelector('.prompt-container');
    const generationContainer   = generatorForm.querySelector('.generation-container');
    const courseDescription     = generatorForm.querySelector('#course_description');
    const generateCourse        = generatorForm.querySelector('#generate_course');
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

            // User added files to the file input.
            courseFiles.addEventListener('change', () => this.displayFileNames());

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
            $('.prompt-container .form-group').bind({
                dragenter: function(event) {
                    dragEnterCounter++;
                    promptContainer.classList.add('drag-over');
                    event.preventDefault();
                },
                dragleave: function() {
                    dragEnterCounter--;
                    if (dragEnterCounter === 0) { 
                        promptContainer.classList.remove('drag-over');
                    }
                }
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
