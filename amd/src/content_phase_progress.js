// This file is part of Moodle - http://moodle.org/
//
// @module     block_dixeo_designer/content_phase_progress
// @copyright  2026 Dixeo
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * Step 3 (generating content): bar band 40–80% split into `total` equal segments.
 * Within the current segment: move through the first 80% of that segment over 10s, then hold
 * until the module completes. If the server reports the next module before 10s, jump to the
 * end of the completed segment (100% of that slice) and start the next segment.
 *
 * Pure orchestration state; callers provide setProgress(percent, force).
 *
 * @module block_dixeo_designer/content_phase_progress
 */
define([], function() {
    'use strict';

    /** Width of step 3 on the overall 0–100 bar. */
    const CONTENT_BAND_WIDTH = 40;

    /** Step 3 starts at 40% on the overall bar. */
    const CONTENT_BAND_START = 40;

    /** Fraction of each module segment filled by the timed animation (rest on completion). */
    const SEGMENT_ANIM_FRACTION = 0.8;

    const ANIM_MS = 10000;

    /**
     * @param {number} current 1-based module or section index
     * @param {number} total
     * @returns {{ segStart: number, segLen: number }}
     */
    function segmentMetrics(current, total) {
        const segStart = CONTENT_BAND_START + (CONTENT_BAND_WIDTH * (current - 1)) / total;
        const segEnd = CONTENT_BAND_START + (CONTENT_BAND_WIDTH * current) / total;
        return {segStart: segStart, segLen: segEnd - segStart};
    }

    /**
     * Bar percent within the current segment for the timed ease (0–80% of segment).
     *
     * @param {number} current
     * @param {number} total
     * @param {number} segmentStartMs
     * @param {number} nowMs
     * @returns {number}
     */
    function percentWithinSegment(current, total, segmentStartMs, nowMs) {
        const {segStart, segLen} = segmentMetrics(current, total);
        const elapsed = nowMs - segmentStartMs;
        const t = Math.min(1, Math.max(0, elapsed / ANIM_MS));
        return segStart + SEGMENT_ANIM_FRACTION * segLen * t;
    }

    /**
     * Bar percent at the end of completed modules (end of segment `completedIndex`).
     *
     * @param {number} completedIndex 1-based index of the module that just finished
     * @param {number} total
     * @returns {number}
     */
    function percentAfterModuleCompleted(completedIndex, total) {
        return CONTENT_BAND_START + (CONTENT_BAND_WIDTH * completedIndex) / total;
    }

    /**
     * @param {object} data finalize_progress row
     * @returns {{ total: number, current: number }|null}
     */
    function parseIndexAndTotal(data) {
        if (Number(data.module_total) > 0) {
            const total = Number(data.module_total) || 0;
            const current = Math.min(total, Math.max(1, Number(data.module_index) || 0));
            return {total: total, current: current};
        }
        if (Number(data.section_total) > 0) {
            const total = Number(data.section_total) || 0;
            const current = Math.min(total, Math.max(1, Number(data.section_index) || 0));
            return {total: total, current: current};
        }
        return null;
    }

    /**
     * @returns {object}
     */
    function createAnimator() {
        let rafId = null;
        let lastPollIndex = 0;
        let total = 0;
        let currentIndex = 0;
        let segmentStartMs = 0;

        /**
         * Stop the requestAnimationFrame loop for the current segment.
         */
        function cancelRaf() {
            if (rafId !== null) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
        }

        /**
         * @param {function(number, boolean): void} setProgress
         */
        function startRafLoop(setProgress) {
            cancelRaf();
            const loop = function() {
                const pct = percentWithinSegment(currentIndex, total, segmentStartMs, Date.now());
                setProgress(pct, true);
                if (Date.now() - segmentStartMs < ANIM_MS) {
                    rafId = requestAnimationFrame(loop);
                }
            };
            rafId = requestAnimationFrame(loop);
        }

        return {
            /**
             * Stop animation and clear state (finalize ended, reset, or left content phase).
             */
            reset: function() {
                cancelRaf();
                lastPollIndex = 0;
                total = 0;
                currentIndex = 0;
                segmentStartMs = 0;
            },

            /**
             * @param {object} data get_finalize_progress payload
             * @param {function(number, boolean): void} setProgress
             * @returns {boolean} true if totals were present and handled
             */
            onGeneratingContentPoll: function(data, setProgress) {
                const parsed = parseIndexAndTotal(data);
                if (!parsed) {
                    this.reset();
                    return false;
                }

                const t = parsed.total;
                const c = parsed.current;

                if (lastPollIndex > 0 && c > lastPollIndex) {
                    cancelRaf();
                    setProgress(percentAfterModuleCompleted(lastPollIndex, t), true);
                    total = t;
                    currentIndex = c;
                    segmentStartMs = Date.now();
                    lastPollIndex = c;
                    startRafLoop(setProgress);
                    return true;
                }

                if (lastPollIndex === 0) {
                    total = t;
                    currentIndex = c;
                    segmentStartMs = Date.now();
                    lastPollIndex = c;
                    startRafLoop(setProgress);
                    return true;
                }

                if (c === lastPollIndex) {
                    return true;
                }

                return true;
            }
        };
    }

    return {
        createAnimator: createAnimator,
        segmentMetrics: segmentMetrics,
        percentWithinSegment: percentWithinSegment,
        percentAfterModuleCompleted: percentAfterModuleCompleted,
        parseIndexAndTotal: parseIndexAndTotal,
        ANIM_MS: ANIM_MS,
        SEGMENT_ANIM_FRACTION: SEGMENT_ANIM_FRACTION
    };
});
