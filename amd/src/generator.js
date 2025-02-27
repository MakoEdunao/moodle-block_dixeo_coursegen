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
            let generateCourse = generatorForm.querySelector('#generate_course');
            let loader = generatorForm.querySelector('#loader');
            let successContainer = generatorForm.querySelector('#success_message_container');

            // Prevent default action and start progress.
            generateCourse.addEventListener('click', (event) => {
                event.preventDefault();
                loader.classList.replace('d-none', 'd-block');
                if (this.progress === 0) {
                    // Begin progress animation.
                    this.startProgress(loader);
                } else if (this.progress < 100) {
                    // Finish progress.
                    this.setProgress(loader, 100);
                    successContainer.classList.replace('d-none', 'd-block');
                } else {
                    // Reset progress.
                    this.setProgress(loader, 0);
                    successContainer.classList.replace('d-block', 'd-none');
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
