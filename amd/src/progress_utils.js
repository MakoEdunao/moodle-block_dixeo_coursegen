/**
 * Shared UI helpers for progress + step highlighting.
 *
 * Keeps threshold logic consistent between generator.js and designer.js.
 */
define([], function() {
    return {
        // Phases returned by block_dixeo_designer_get_finalize_progress().
        PHASE_GENERATING_CONTENT: 'generating_content',
        PHASE_FINALIZING: 'finalizing',
        PHASE_DONE: 'done',

        // Shared remote validation constraints.
        // Remote course structure generation requires instructions >= this length.
        MIN_INSTRUCTIONS_LEN: 20,

        // sessionStorage keys shared between generator.js and designer.js.
        SESSION_RETURN_TO_KEY: 'block_dixeo_designer_return_to',
        SESSION_RETURN_TO_JOBID_KEY: 'block_dixeo_designer_return_to_jobid',

        /**
         * Map progress percentage to an active step.
         *
         * Step mapping:
         *  - 0-20%  => 1
         *  - >20-40% => 2
         *  - >=40-<80% => 3
         *  - >=80% => 4
         *
         * @param {number} progress Progress percentage (0-100)
         * @returns {number} Step number (1-4)
         */
        getActiveStepFromProgress: function(progress) {
            const p = Number(progress);
            const safeP = Number.isFinite(p) ? p : 0;

            if (safeP >= 80) {
                return 4;
            }
            if (safeP >= 40) {
                return 3;
            }
            if (safeP > 20) {
                return 2;
            }
            return 1;
        }
    };
});

