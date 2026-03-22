# AMD modules (`block_dixeo_designer`)

## Designer page (`designer.js`)

The main designer module composes the **structure editor** with two mixins (merged via `jQuery.extend`):

| Module | Role |
|--------|------|
| `designer` | Load/render structure, module-type helpers, actions, templates, footer wiring. |
| `designer_collapse` | Section expand/collapse state and click handlers. |
| `designer_editing` | Inline editable fields (`TextUtil` for decode). |
| `designer_undo` | In-memory history, `setValueByPath`, undo/redo. |
| `designer_finalize` | Create course: scroll/block expand, save+finalize WS, finalize poll, generation bar, UI lock, success template. |
| `designer_dragdrop` | Section/module drag-and-drop reordering. |

## Progress / generation phases

Pure **percent and label resolution** for a single phase should live in a dedicated `*_progress_map.js` module (no DOM, no `core/ajax`), so orchestration stays in `progress.js`.

| Module | Role |
|--------|------|
| `filesync_progress_map` | 0–20% bar band before `submit_structure_job` (Moodle prepare → upload → indexing), from `get_filesync_status`. |
| `content_phase_progress` | Step 3 (40–80%): per-module segment animation (80% of segment over 10s, then hold; snap on completion). |
