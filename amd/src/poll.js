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
 * AMD module for course generator block.
 *
 * @module     block_dixeo_coursegen/poll
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax',
    'core/notification',
    'core/log'
], function(Ajax, Notification, Log) {
    const TERMINAL_STATES = new Set(['success', 'error']);
    const MIN_INTERVAL_MS = 2000; // Start at 2s.
    const MAX_INTERVAL_MS = 30000; // Cap at 30s.
    const TOTAL_TIMEOUT_MS = 5 * 60 * 1000; // Stop after 5 minutes as a safety.`

    /**
     * Returns the input milliseconds with a random ±20% jitter applied.
     * Useful for avoiding synchronized requests ("thundering herd" problem).
     *
     * @param {number} ms - The base time in milliseconds.
     * @returns {number} The jittered time in milliseconds.
     */
    function withJitter(ms) {
        // ±20% jitter to avoid thundering herds.
        const delta = ms * 0.2;
        return Math.round(ms + (Math.random() * 2 * delta - delta));
    }

    class Poller {
        constructor(root) {
            this.root = root;
            this.taskid = root.dataset.taskid;
            this.interval = MIN_INTERVAL_MS;
            this.running = false;
            this.startTime = Date.now();
            this.timeoutHandle = null;

            this.onVisibilityChange = this.onVisibilityChange.bind(this);
            this.cleanup = this.cleanup.bind(this);
        }

        init() {
            if (!this.taskid) {
                Log.error('block_dixeo_coursegen/poll: missing data-taskid');
                return;
            }
            this.running = true;
            document.addEventListener('visibilitychange', this.onVisibilityChange);
            window.addEventListener('beforeunload', this.cleanup);
            this.tick(); // Fire immediately.
        }

        onVisibilityChange() {
            if (document.hidden) {
                // Pause while hidden to save resources; resume when visible.
                this.clearTimer();
            } else if (this.running) {
                this.interval = MIN_INTERVAL_MS; // Reset cadence on resume.
                this.tick();
            }
        }

        scheduleNext() {
            if (!this.running) {
                return;
            }
            if (Date.now() - this.startTime > TOTAL_TIMEOUT_MS) {
                this.running = false;
                return;
            }
            this.clearTimer();
            this.timeoutHandle = window.setTimeout(() => this.tick(), withJitter(this.interval));
        }

        clearTimer() {
            if (this.timeoutHandle) {
                clearTimeout(this.timeoutHandle);
                this.timeoutHandle = null;
            }
        }

        tick() {
            if (document.hidden || !this.running) {
                return;
            }

            Ajax.call([{
                methodname: 'block_dixeo_coursegen_get_status',
                args: {
                    taskid: this.taskid,
                    sesskey: M.cfg.sesskey
                },
            }])[0].then((data) => {
                this.root.dataset.status = Number.isFinite(data.status) ? data.status : '';
                this.root.dataset.timeupdated = data.timeupdated || '';

                if (data.terminal || TERMINAL_STATES.has(data.status)) {
                    this.running = false;
                    this.clearTimer();
                    return;
                }

                // Success path: gentle backoff up to cap.
                this.interval = Math.min(this.interval * 1.5, MAX_INTERVAL_MS);
                this.scheduleNext();
                return;
            }).catch((err) => {
                // On error: show once and back off more aggressively.
                Log.warn('block_dixeo_coursegen/poll error', err);
                Notification.exception(err);
                this.interval = Math.min(this.interval * 2, MAX_INTERVAL_MS);
                this.scheduleNext();
            });
        }

        cleanup() {
            this.running = false;
            this.clearTimer();
            document.removeEventListener('visibilitychange', this.onVisibilityChange);
            window.removeEventListener('beforeunload', this.cleanup);
        }
    }

    return {
        init(rootEl) {
            const p = new Poller(rootEl);
            p.init();
            return p;
        }
    };
});
