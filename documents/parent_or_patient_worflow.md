# Parent Or Patient Workflow
## Patient Portal Focus (Responsive Web)

---

## Purpose
Define a clear, separate workflow for account owners (parents or guardians) and patients so the patient portal behavior is aligned with `documents/WORKFLOW.md` and `documents/DATABASE.md`.

---

## Actors
- Account owner (parent/guardian or the patient themself)
- Patient (child/dependent or the account owner)
- Nurse (approval, check-in, vitals, interview)
- Doctor (diagnosis, plan, prescriptions)
- Cashier (billing)

---

## Data Boundaries (Aligned With Database)
- `users` + `personal_information` store the account owner's profile.
- `appointments` are created by the account owner (online or walk-in).
- `medical_records` store the patient details per visit (self-contained, may be different from account owner).
- `queues` attach after approval and check-in.

---

## Backend Data Creation Rules
- No database writes while the stepper is in progress (only Livewire state).
- Create `appointments` only on final submit.
- Create `medical_records` when nurse records vital signs.
- If appointment is cancelled or declined, no medical record is created.

---

## Appointment Status Flow (Reference)
`pending` -> `approved` -> `checked_in` -> `in_progress` -> `completed`

Alternate: `declined`, `cancelled`, `no_show`.

---

## Workflow A: Account Owner Booking For Self
1. Login -> open Book Appointment.
2. Select consultation type.
3. Select appointment date.
4. Patient details -> choose "Myself".
5. Enter chief complaints -> review -> submit.
6. Appointment created as `pending`.
7. SMS confirmation sent.
8. After approval, queue is assigned and shown in portal.
9. Visit day -> check-in -> vitals -> doctor -> billing -> completion.
10. Medical record appears in history after completion.

---

## Workflow B: Account Owner Booking For Dependent
1. Login -> open Book Appointment.
2. Select consultation type.
3. Select appointment date.
4. Patient details -> choose "Someone else (child/dependent)".
5. Enter dependent name, birth date, gender.
6. Enter chief complaints -> review -> submit.
7. Appointment created as `pending`.
8. SMS confirmation sent to account owner.
9. After approval, queue is assigned and shown in portal.
10. Visit day -> check-in -> nurse interview -> vitals -> doctor -> billing -> completion.
11. Medical record for the dependent appears in history after completion.

---

## Patient Portal Pages (Reference)
- Dashboard: upcoming appointment + quick actions.
- Book Appointment: stepper with 4 steps.
- My Appointments: list, filter, cancel.
- Queue Status: active queue and live updates.
- Medical Records: completed visits only.
- Profile: account owner information.

---

## Notifications (SMS)
- Appointment booked (pending).
- Appointment approved or declined.
- Day-before reminder.
- Queue near and queue called.

---

## Open Decisions (For Discussion)
- None for this workflow right now.
