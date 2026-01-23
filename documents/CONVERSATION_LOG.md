# Conversation Log – Active Queue Guard

- Date: 2026-01-23
- Topic: Preventing duplicate active queue rows per appointment in HQMS.

## Key Points
- The `is_active_queue` computed column flags in-play queue rows (`waiting`, `called`, `serving`, `skipped`).
- Unique index `(appointment_id, is_active_queue)` blocks more than one active row for the same appointment, even under double-clicks or concurrent inserts.
- Status `completed`/`cancelled` turns `is_active_queue` false, allowing requeue if a visit is reopened.
- Guard is DB-level; does not enforce call order—nurses can call any number out of turn.

## Real-World Analogies
- Like a deli counter ticket system where each appointment can hold exactly one “currently being served” ticket; finishing or voiding a ticket frees the slot for a new one.
- Prevents two live tickets for the same appointment when front-desk staff double-click, two terminals submit simultaneously, or integrations retry.

## Open Considerations
- Future logic should still enforce per-type queues (online vs walk-in) while relying on the DB guard to prevent duplicates across those channels.
