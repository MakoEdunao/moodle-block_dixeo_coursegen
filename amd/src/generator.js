define([
    'jquery',
    'core/ajax',
], function($, Ajax) {
    return {
        init: function() {
            this.progress = 0;

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.addInviteListener.bind(this));
            } else {
                this.addInviteListener();
            }
        },
        addInviteListener: function() {
            let generatorForm = document.getElementById('edai_course_generator_form');
            let promptContainer = generatorForm.querySelector('.prompt-container');
            let generationContainer = generatorForm.querySelector('.generation-container');
            let generateCourse = generatorForm.querySelector('#generate_course');
            let spinner = generatorForm.querySelector('#progress-spinner');
            let loader = generatorForm.querySelector('#loader');
            let successContainer = generatorForm.querySelector('#success_message_container');

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
    };
});
