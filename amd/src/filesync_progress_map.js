// This file is part of Moodle - http://moodle.org/
//
// @module     block_dixeo_designer/filesync_progress_map
// @copyright  2026 Dixeo
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * Maps file-sync / pre-structure progress (0–20% bar band before submit_structure_job).
 * Moodle prepare → HTTP upload → indexing; driven by get_filesync_status payloads.
 * Pure functions only — no DOM or Moodle globals.
 *
 * Other generation phases can use sibling modules (e.g. content-phase mapping) with the same pattern.
 *
 * @module block_dixeo_designer/filesync_progress_map
 */
define([], function() {
    'use strict';

    const MOODLE_CAP = 5;
    const UPLOAD_SPAN = 10;
    const INDEXING_SPAN = 5;
    /** Top of the file-sync band (20%) before structure submit. */
    const FILESYNC_BAND_END = MOODLE_CAP + UPLOAD_SPAN + INDEXING_SPAN;
    const CAP_BEFORE_STRUCTURE_SUBMIT = 19.95;
    const UPLOAD_BAND_END = MOODLE_CAP + UPLOAD_SPAN;

    /**
     * @param {object} row AJAX row
     * @param {string} key
     * @returns {number|null}
     */
    function num(row, key) {
        const v = row[key];
        return Number.isFinite(v) ? v : null;
    }

    /**
     * @param {number} r
     * @returns {number}
     */
    function clamp01(r) {
        return Math.min(1, Math.max(0, r));
    }

    /**
     * @param {number} p
     * @returns {number}
     */
    function clampPct(p) {
        return Math.min(100, Math.max(0, p));
    }

    /**
     * @param {object} data
     * @returns {boolean}
     */
    function isMoodlePrepareActive(data) {
        return data.moodleprepareactive === true || data.moodleprepareactive === 1;
    }

    /**
     * @param {object} data
     * @param {number|null} uploadTotal
     * @param {number|null} uploadNow
     * @param {number|null} fileTotal
     * @param {boolean} noneReady
     * @returns {boolean}
     */
    function remoteSyncStarted(data, uploadTotal, uploadNow, fileTotal, noneReady) {
        if (uploadTotal > 0 && uploadNow !== null) {
            return true;
        }
        if (fileTotal !== null && fileTotal > 0) {
            return true;
        }
        const st = data.status;
        if (st === 'syncing' || st === 'synchronized') {
            return true;
        }
        return Boolean(noneReady);
    }

    /**
     * @param {number|null} pct
     * @param {boolean} remoteStarted
     * @param {string} status
     * @param {boolean} noneReady
     * @returns {number|null}
     */
    function normalizePctForMap(pct, remoteStarted, status, noneReady) {
        let p = pct;
        if (remoteStarted && p === null && (status === 'synchronized' || noneReady)) {
            p = 100;
        }
        if (noneReady && (p === null || p === 0)) {
            p = 100;
        }
        return p;
    }

    /**
     * @param {object} data get_filesync_status payload
     * @param {{ hasSubmissionFiles: boolean, structureSubmitDone: boolean }} opts
     * @returns {number}
     */
    function computeTarget(data, opts) {
        const hasFiles = opts.hasSubmissionFiles === true || opts.hasSubmissionFiles === 1;
        const structureDone = Boolean(opts.structureSubmitDone);
        const cap = structureDone ? 100 : CAP_BEFORE_STRUCTURE_SUBMIT;

        const pct = num(data, 'progresspercent');
        const fileTotal = num(data, 'filestotal');
        const fileDone = num(data, 'filescompleted');
        const uploadTotal = num(data, 'uploadbytestotal');
        const uploadNow = num(data, 'uploadbytes');
        const status = data.status;
        const lastsyncNum = Number(data.lastsynccompleted);
        const noneReady = status === 'none' && Number.isFinite(lastsyncNum) && lastsyncNum > 0;

        const remoteStarted = remoteSyncStarted(data, uploadTotal, uploadNow, fileTotal, noneReady);

        if (!hasFiles && !remoteStarted) {
            return 0;
        }

        const pctForMap = normalizePctForMap(pct, remoteStarted, status, noneReady);

        if (status === 'synchronized' || noneReady) {
            return Math.min(cap, FILESYNC_BAND_END);
        }
        if (status === 'preparing' || isMoodlePrepareActive(data)) {
            return Math.min(cap, MOODLE_CAP);
        }

        if (status === 'none' && !noneReady) {
            const bytesStarted = uploadTotal > 0 && uploadNow !== null && uploadNow > 0;
            if (!bytesStarted) {
                return Math.min(cap, MOODLE_CAP);
            }
            if (uploadNow < uploadTotal) {
                return Math.min(cap, MOODLE_CAP + UPLOAD_SPAN * clamp01(uploadNow / uploadTotal));
            }
            return Math.min(cap, UPLOAD_BAND_END);
        }

        if (status === 'syncing') {
            if (uploadTotal > 0 && uploadNow !== null && uploadNow < uploadTotal) {
                return Math.min(cap, MOODLE_CAP + UPLOAD_SPAN * clamp01(uploadNow / uploadTotal));
            }

            const uploadBytesDone = uploadTotal <= 0
                || (uploadNow !== null && uploadNow >= uploadTotal);

            if (uploadBytesDone) {
                if (fileTotal > 0 && fileDone !== null) {
                    if (fileDone < fileTotal) {
                        return Math.min(cap, UPLOAD_BAND_END + INDEXING_SPAN * clamp01(fileDone / fileTotal));
                    }
                    if (pctForMap !== null) {
                        const slicePct = pctForMap >= 99 ? 100 : clampPct(pctForMap);
                        return Math.min(cap, UPLOAD_BAND_END + INDEXING_SPAN * (slicePct / 100));
                    }
                }
                return Math.min(cap, UPLOAD_BAND_END);
            }

            if (fileTotal > 0 && fileDone !== null) {
                return Math.min(cap, MOODLE_CAP + UPLOAD_SPAN * clamp01(fileDone / fileTotal));
            }
            return Math.min(cap, MOODLE_CAP);
        }

        return Math.min(cap, MOODLE_CAP);
    }

    /**
     * @param {object} data
     * @param {boolean} hasSubmissionFiles
     * @returns {{ key: string, params?: object }}
     */
    function resolveLabel(data, hasSubmissionFiles) {
        if (!hasSubmissionFiles) {
            return {key: 'step_processing_prompt'};
        }
        if (isMoodlePrepareActive(data)) {
            return {key: 'step_preparing_files'};
        }

        const fileTotal = num(data, 'filestotal');
        const fileDone = num(data, 'filescompleted');
        if (fileTotal !== null && fileTotal > 0 && fileDone !== null) {
            let currentIndex = fileDone;
            if (data.status === 'syncing' && fileDone < fileTotal) {
                currentIndex = fileDone + 1;
            }
            if (currentIndex < 1) {
                currentIndex = 1;
            }
            return {
                key: 'step_uploading_files_count',
                params: {current: currentIndex, total: fileTotal},
            };
        }

        const uploadTotal = num(data, 'uploadbytestotal');
        const uploadNow = num(data, 'uploadbytes');
        if (uploadTotal > 0 && uploadNow !== null && uploadNow > 0
            && (data.status === 'syncing' || data.status === 'none')) {
            return {
                key: 'step_uploading_files_count',
                params: {current: 1, total: 1},
            };
        }

        if (data.status === 'syncing' && uploadTotal > 0 && uploadNow !== null
            && uploadNow >= uploadTotal
            && (fileTotal === null || fileTotal <= 0 || fileDone === null || fileDone < fileTotal)) {
            return {key: 'step_preparing_files'};
        }

        return {key: 'step_preparing_files'};
    }

    return {
        CAP_BEFORE_STRUCTURE_SUBMIT: CAP_BEFORE_STRUCTURE_SUBMIT,
        computeTarget: computeTarget,
        resolveLabel: resolveLabel,
    };
});
