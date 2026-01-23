# Patient Module Workflow (Current Context)

- Fresh DB seeded with updated users/personal info schema and patient components stubbed to static views.
- Patient routes will start with profile completion; other patient features (appointments, queue, records) temporarily removed from routing until rebuilt.
- Login flow should redirect patients with incomplete personal info to the profile form; otherwise send them to the patient dashboard.
- Next steps: implement personal-info completion guard/middleware, then reintroduce dashboard/booking/queue/records with live data.
