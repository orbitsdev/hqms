# Development Workflow & Collaboration Guide
## Hospital Queue Management System

---

## How We'll Work Together

### Our Approach
This project follows a **documentation-first, structured development** methodology:

1. **Foundation First** - Establish clear requirements and specifications before coding
2. **Incremental Development** - Build and test in manageable chunks
3. **Review & Iterate** - Regular check-ins and adjustments
4. **Quality Focus** - Proper testing and validation at each stage

---

## Key Strategy Decisions

### ✅ SMS Notifications (Primary)
- **SMS System Implemented** - Using Semaphore as SMS provider
- SMS notifications for: appointment approval, queue alerts, reminders
- Firebase Cloud Messaging is optional for a future mobile app (not used in web portal)
- Reliable delivery without requiring app installation

### ✅ Responsive Web Portal for Patients (Instead of Native Mobile App)
- **No Flutter app** - Using responsive Livewire/Flux web portal instead
- Mobile-first responsive design (works on desktop, tablet, mobile)
- Accessible via any browser - no app installation required
- Uses **free Flux components only**
- Faster development, easier maintenance, single codebase

### ✅ Appointments vs Medical Records (Separated)
- `appointments` handle booking, queue, and visit status (pending → approved → checked_in → in_progress → completed)
- `medical_records` are created only when vitals are recorded (no pre-visit records)
- Medical record status uses enum: `in_progress`, `for_billing`, `for_admission`, `completed`

### Why This Approach?
| Native App | Responsive Web |
|------------|----------------|
| Requires app download | Works instantly in browser |
| App store approval needed | No approval process |
| Separate codebase (Flutter) | Single Laravel codebase |
| Push notifications (FCM optional) | SMS notifications (already working) |
| 4 weeks development | 1-2 weeks development |

---

## Doctor Schedule Management

### How It Works
Doctors set their **regular weekly schedule** (one-time setup), then mark **exceptions** as needed (leave, half-day, extra clinic day).

### 1. Setup Regular Schedule (Admin/Nurse - One-time)
```
┌─────────────────────────────────────────────────┐
│ Doctor Schedule Setup                           │
├─────────────────────────────────────────────────┤
│ Doctor: [Dr. Maria Santos ▼]                    │
│ Consultation Type: [Obstetrics (OB) ▼]          │
│                                                 │
│ Working Days:                                   │
│ ☐ Sun  ☑ Mon  ☐ Tue  ☑ Wed  ☐ Thu  ☑ Fri  ☐ Sat│
│                                                 │
│                            [Save Schedule]      │
└─────────────────────────────────────────────────┘
```
**Result:** Dr. Santos works Mon/Wed/Fri for OB consultations.

### 2. Mark Exception (Doctor/Nurse - As Needed)

**View Calendar:**
```
┌─────────────────────────────────────────────────┐
│ My Schedule - Dr. Santos (OB)      January 2026 │
├─────────────────────────────────────────────────┤
│  Sun   Mon   Tue   Wed   Thu   Fri   Sat        │
│                     1     2    [3]    4         │
│   5    [6]    7    [8]    9   [10]   11         │
│  12   [13]   14   [15]   16   [17]   18         │
│  19   [20]   21   [22]   23   [24]   25         │
│                                                 │
│ [  ] = Working days (from regular schedule)     │
│ Click any date to mark leave/exception          │
└─────────────────────────────────────────────────┘
```

**Mark Single Date Unavailable:**
```
┌─────────────────────────────────────────────────┐
│ Friday, January 24, 2026                        │
├─────────────────────────────────────────────────┤
│ ○ Available (normal working day)                │
│ ● Unavailable (leave/day off)                   │
│ ○ Partial Day: [08:00] to [12:00]               │
│                                                 │
│ Reason: [Personal emergency___________]         │
│                                                 │
│              [Cancel]    [Save]                 │
└─────────────────────────────────────────────────┘
```

**Mark Date Range (Multiple Days):**
```
┌─────────────────────────────────────────────────┐
│ Mark Leave                                      │
├─────────────────────────────────────────────────┤
│ ○ Single Date                                   │
│ ● Date Range: [Jan 24, 2026] to [Jan 27, 2026]  │
│                                                 │
│ Reason: [Family vacation______________]         │
│                                                 │
│              [Cancel]    [Save]                 │
└─────────────────────────────────────────────────┘
```
**Result:** System creates exception entries for Jan 24, 25, 27 (skips 26 since not a working day).

### 3. Patient Sees Availability
```
Book OB Appointment - January 2026

        22    23    [24]   [25]    26    [27]
        Wed   Thu    Fri    Sat    Sun    Mon
         ✓     -      ✗      -      -      ✗

✓ = Available    ✗ = No doctor    - = Not a working day
```

### Schedule Types Summary
| Type | Purpose | Example |
|------|---------|---------|
| `regular` | Weekly recurring schedule | Dr. Santos works Mon, Wed, Fri |
| `exception` (unavailable) | Day off/leave | Dr. Santos on leave Jan 24-27 |
| `exception` (partial) | Half-day | Dr. Santos morning only Jan 28 |
| `exception` (available) | Extra clinic day | Dr. Santos adds Saturday clinic |

---

## Development Process

### Local Development (Laravel Herd)
- Use Laravel Herd for local access: `https://hqms.test/`
- No need to run `php artisan serve` when using Herd
- If the page does not load, confirm Herd is running and the domain resolves

### Reverb / Queue Pre-Checks (Before Testing)
- If the feature relies on Reverb or queues, start them before testing to avoid HTTP errors
- Recommended: run `composer dev` (server + queue + Vite) when doing realtime work
- Alternatively run separately:
  - `php artisan reverb:start`
  - `php artisan queue:listen --tries=1`

### Phase 0: Planning & Architecture (✅ COMPLETED)
- ✅ Project definition documented
- ✅ Technology stack confirmed (Laravel 12 + Livewire + Responsive Web)
- ✅ Complete database schema designed (16 tables + Spatie)
- ✅ System flow with real-world scenarios
- ✅ User workflows documented
- ✅ Edge cases identified
- ✅ SMS system implemented (Semaphore)

**Next:** UI wireframes, Reverb events, responsive layouts

---

### Phase 1: Backend Foundation (Estimated: 2-3 weeks)

**Week 1: Database & Authentication**
```
Day 1-2: Project Setup
- Initialize Laravel 12 project
- Configure database (MySQL)
- Install packages: Livewire, Sanctum, Spatie Permission, Reverb
- Setup Redis for caching/queues
- Configure environment variables
- Verify SMS system working

Day 3-4: Database Implementation
- Create ALL migrations (16 tables)
- Run migrations in correct order
- Create seeders:
  • ConsultationTypeSeeder (OB, PEDIA, GENERAL)
  • RoleSeeder (patient, nurse, doctor, cashier, admin)
  • PermissionSeeder (all permissions)
  • ServiceSeeder (real hospital pricing)
  • SystemSettingSeeder (default settings)
  • UserSeeder (test users for each role)
  • HospitalDrugSeeder (common medications)
- Seed database with test data

Day 5-7: Authentication & Authorization
- Setup Fortify for web authentication
- Setup Sanctum for API (internal use)
- Implement Spatie permissions
- Create policies for each model
- Test authentication flows:
  • Patient registration (web)
  • Staff login (web)
  • Permission checks
```

**Week 2: Core Models & Relationships**
```
Day 1-3: Create Eloquent Models
- User (with all relationships)
- ConsultationType
- DoctorSchedule
- Appointment
- Queue
- MedicalRecord
- Prescription
- Service
- BillingTransaction
- BillingItem
- HospitalDrug
- Admission
- SystemSetting
- QueueDisplay
- SmsLog

Day 4-5: Model Relationships & Accessors
- Define all relationships (hasMany, belongsTo, belongsToMany)
- Create accessors (formatted_number, effective_chief_complaints)
- Create scopes (today, pending, waiting, etc.)
- Test relationships with Tinker

Day 6-7: Business Logic (Services/Actions)
- AppointmentService (create, approve, decline)
- QueueService (generate, call, skip, complete)
- BillingService (calculate totals, apply discount)
- NotificationService (SMS notifications)
- Test business logic with unit tests
```

**Week 3: Core Services & SMS Integration**
```
Day 1-2: SMS Notification Integration
- Configure SMS triggers:
  • Appointment approved → SMS to patient
  • Appointment declined → SMS with reason
  • Queue called → SMS alert
  • Day-before reminder → Scheduled SMS
- Create SMS templates/messages
- Test SMS delivery

Day 3-5: Internal API (for AJAX/Livewire)
- Authentication check endpoints
- Appointment CRUD endpoints
- Queue status endpoints
- Medical records endpoints

Day 6-7: Testing & Documentation
- Test all services
- Document API endpoints
- Create Postman collection
```

---

### Phase 2: Web Portal - Nurse Module (Estimated: 3 weeks)

**Week 1: Dashboard & Appointment Management**
```
Day 1-2: Nurse Dashboard (Livewire)
- Display stats:
  • Pending appointments count
  • Today's queue count (per type)
  • Checked-in patients
  • Current serving patients
- Real-time updates via Reverb

Day 3-5: Appointment Approval (Livewire Component)
- List pending appointments (Flux table)
- View appointment details (Flux modal)
- Approve appointment:
  • Auto-generate queue number
  • Calculate estimated time
  • Send SMS notification to patient
- Decline appointment:
  • State reason (Flux form)
  • Suggest alternative date (date picker)
  • Send SMS notification to patient
- Filters: By date, by type, by status

Day 6-7: Walk-in Registration (Livewire Component)
- Search existing patients (Flux search)
- Create new patient account:
  • Personal info form
  • Medical history (optional)
  • Phone number (for SMS)
  • Generate temp password
- Create walk-in appointment:
  • Select consultation type
  • Fill chief complaints
  • Auto-approve
  • Auto-generate queue
  • Send SMS with queue number
- Print queue ticket (blade template + print.css)

Testing Scenario:
1. Nurse approves online appointment → Queue generated → SMS sent
2. Nurse creates walk-in → Queue generated → SMS sent
3. Both appear in same queue (correct order)
4. Verify SMS notifications received
```

**Week 2: Queue Management**
```
Day 1-3: Queue Dashboard (Livewire Component)
- Separate views per type (OB, PEDIA, GENERAL)
- Display current queue (Flux table):
  • Queue number
  • Patient name
  • Status
  • Estimated time
  • Actions (Call, Skip, View)
- Real-time updates via Reverb
- Filter: waiting, called, serving, skipped

Day 4-5: Queue Actions (Livewire)
- Call next patient:
  • Update status to "called"
  • Broadcast to displays
  • Send SMS to patient
  • Update estimated times for remaining
- Skip patient (no-show):
  • Update status to "skipped"
  • Call next in line
  • Allow re-activation later
- Mark urgent:
  • Change priority
  • Move to front of queue
- Complete queue:
  • Update status to "completed"

Day 6-7: Check-in System (Livewire Component)
- Search patient by:
  • Name
  • Phone number
  • Queue number
  • Appointment ID
- Display appointment details
- Click "Check In" button
- Update appointment status
- Patient appears in "Ready for Vitals" list

Real-World Testing:
1. Create 10 test appointments (mix of OB, PEDIA, GENERAL)
2. Check in some, leave some unchecked
3. Call queue numbers in order
4. Skip a no-show
5. Mark one urgent (moves to front)
6. Verify displays update in real-time
7. Verify SMS sent when queue called
```

**Week 3: Vital Signs & Patient Interview**
```
Day 1-3: Vital Signs Input (Livewire Component)
- Patient list (checked-in, waiting for vitals)
- Click patient → Open vitals form (Flux modal)
- Dynamic form based on consultation type:
  • All types: Temp, BP, CR, RR
  • OB: + FHT, Fundal Height, LMP
  • PEDIA/GENERAL: + Weight, Height, Circumferences
- Review initial chief complaints (from booking)
- Update chief complaints (nurse interview)
- Save vital signs
- Automatically create medical record
- Click "Forward to Doctor"

Day 4-5: Patient Search & Records (Livewire Component)
- Global patient search (Flux search)
- Search by: Name, Phone, Queue, Appointment
- View patient profile:
  • Personal info
  • Medical history
  • All past visits (timeline)
- View medical record details:
  • Vital signs
  • Diagnosis
  • Prescriptions
  • Billing
- Print medical record (PDF export)

Day 6-7: Testing & Refinement
- Test complete nurse workflow:
  1. Approve appointment
  2. Patient checks in
  3. Input vital signs
  4. Forward to doctor
- Test walk-in workflow:
  1. Register walk-in
  2. Take vital signs immediately
  3. Forward to doctor
- Verify data accuracy
- Test real-time updates
- Verify all SMS notifications
```

---

### Phase 3: Web Portal - Doctor Module (Estimated: 2 weeks)

**Week 1: Doctor Dashboard & Queue**
```
Day 1-2: Doctor Dashboard (Livewire)
- Display stats:
  • Patients waiting (by type)
  • Patients seen today
  • Next patient preview
- Filter by consultation type
- View schedule for the day

Day 3-5: Patient Queue View (Livewire Component)
- List patients ready for doctor (Flux table):
  • Queue number
  • Patient name
  • Chief complaints (preview)
  • Vital signs (summary)
  • Urgent flag (if any)
- Click patient → Open consultation view
- Real-time updates when nurse forwards patient

Day 6-7: Patient Information View
- Complete patient profile:
  • Demographics
  • Medical history (blood type, allergies, chronic conditions)
  • Chief complaints (both initial and updated)
  • Vital signs (just recorded)
- Past visits history (sidebar/timeline):
  • Previous diagnoses
  • Previous prescriptions
  • Last visit date
- All info visible before/during consultation
```

**Week 2: Diagnosis & Prescriptions**
```
Day 1-3: Diagnosis Input (Livewire Component)
- Textarea for each field (Flux forms):
  • Pertinent HPI/PE
  • Diagnosis
  • Treatment Plan
  • Procedures Done
- Free-text prescription notes area
- Save diagnosis

Day 4-5: Prescription Management (Livewire Component)
- Add prescription button → Modal opens
- Search hospital drugs (autocomplete)
- OR type custom medication
- Fill fields:
  • Medication name
  • Dosage (e.g., 500mg)
  • Frequency (e.g., 3x daily)
  • Duration (e.g., 7 days)
  • Instructions (e.g., take after meals)
  • Quantity
- Add multiple prescriptions
- List shows all prescribed medications
- Edit/Remove prescription
- Save all prescriptions

Day 6-7: Billing & Discharge Decision
- Add services to bill:
  • Professional fee (manual input)
  • Select ultrasound/procedures (dropdown from services)
  • Select hospital drugs (auto-added from prescriptions)
- System auto-calculates:
  • Emergency fee (if after 5pm, Sunday, holiday)
  • Subtotal
- Apply discount (if needed):
  • Select discount type (family, senior, PWD, employee)
  • Enter discount amount or percentage
  • Enter reason
  • Click "Apply" (recorded as doctor-approved)
- Final decision:
  • Button: "Forward to Billing" (outpatient)
  • Button: "Forward to Admission" (needs admission)
  • Update medical record status: `for_billing` or `for_admission`

Testing Scenario:
1. Doctor sees next patient (Maria, O-5)
2. Reviews her info, vitals, complaints
3. Examines (manually in real life)
4. Inputs diagnosis
5. Adds 2 prescriptions
6. Adds ultrasound to bill
7. Applies family discount
8. Forwards to billing
9. Next patient (Juan, P-3) automatically appears
```

---

### Phase 4: Web Portal - Cashier & Admin (Estimated: 2 weeks)

**Week 1: Cashier Module**
```
Day 1-3: Billing Dashboard (Livewire)
- List pending bills (Flux table):
  • Queue number
  • Patient name
  • Total amount
  • Discount (if any)
  • Actions (View, Process)
- Click patient → Open billing details

Day 4-5: Payment Processing (Livewire Component)
- Display itemized bill (Flux table):
  • Professional fee
  • Services (ultrasounds, procedures)
  • Drugs
  • Emergency fee (if applicable)
  • Subtotal
  • Discount (show who approved)
  • Total
- Select payment method (cash, card, gcash, etc.)
- Enter amount paid
- Calculate change/balance
- Click "Process Payment"
- Generate receipt (PDF/print)
- Update payment status

Day 6-7: Partial Payments & Reports
- Handle partial payments:
  • Record partial amount
  • Track balance
  • Allow additional payments later
- Daily summary:
  • Total transactions
  • Total revenue
  • Payment methods breakdown
  • Discounts given
- Export reports (Excel/PDF)

Real-World Testing:
1. Process 10 different bills:
   - Cash payments
   - Card payments
   - With discounts
   - With emergency fees
   - Partial payment
2. Generate daily report
3. Verify totals correct
```

**Week 2: Admin Module & Reports**
```
Day 1-2: User Management (Livewire)
- List all users (Flux table)
- Filter by role (patient, nurse, doctor, cashier, admin)
- Create new staff account:
  • Email, password
  • Personal info
  • Assign role (Spatie)
  • Assign consultation types (for doctors)
- Edit user
- Deactivate user (soft delete)

Day 3-4: System Settings (Livewire)
- List all settings (grouped by category)
- Edit settings:
  • Operating hours per type
  • Average duration
  • Emergency fee amount
  • Max appointments per day
  • SMS notification settings
- Save settings
- Apply immediately (cache clear)

Day 5: Queue Display Management (Livewire)
- List queue displays
- Add new display:
  • Display name
  • Consultation type
  • Location
  • Generate access token
- View display URL (with token)
- Edit display settings (JSON)
- Deactivate display

Day 6-7: Reports Dashboard (Livewire)
- Daily reports:
  • Patient count by type
  • Queue statistics (avg wait time, avg service time)
  • No-show count
  • Revenue summary
  • SMS sent count
- Monthly/Yearly reports:
  • Total patients served
  • Revenue trends (charts using Chart.js)
  • Doctor workload (patients per doctor)
  • Peak hours analysis
  • Appointment vs walk-in ratio
- Export all reports (Excel, PDF)

Testing:
1. Create test users for each role
2. Modify system settings
3. Generate reports with test data
4. Verify calculations correct
```

---

### Phase 5: Queue Display System (Estimated: 1 week)

**Week 1: Public Display Pages**
```
Day 1-3: Display Page (Livewire Component)
- Route: /display?token={access_token}
- Verify token (from queue_displays table)
- Get consultation type from display settings
- Large, readable layout:
  • Hospital name/logo
  • Consultation type header (OB, PEDIA, GENERAL)
  • Current serving (huge text):
    "NOW SERVING: O-5"
  • Next in line (large text):
    "NEXT: O-6, O-7, O-8"
  • Estimated times (optional)
- Auto-refresh via Reverb (no page reload)
- Sound notification when new patient called
- Fullscreen mode
- Customizable theme (from display_settings JSON)

Day 4-5: Real-time Updates (Reverb Events)
- Listen to QueueUpdated event
- When nurse calls queue:
  • Display updates immediately
  • Show new "NOW SERVING"
  • Update "NEXT IN LINE"
  • Play sound notification
- Smooth animations (CSS transitions)
- Fallback: Polling every 5 seconds if Reverb fails

Day 6-7: Display Settings & Testing
- Admin can configure per display:
  • Font size (small, medium, large, extra-large)
  • Theme (light, dark, high-contrast)
  • Show estimated times (yes/no)
  • Show patient count (yes/no)
  • Sound enabled (yes/no)
  • Volume level
- Test with actual monitors:
  • Setup 3 displays (OB, PEDIA, GENERAL)
  • Full-day simulation:
    - Nurse calls patients
    - Displays update in real-time
    - Verify accuracy
    - Check performance (no lag)

Real-World Deployment:
1. Setup TV/monitor in waiting area
2. Open browser in kiosk mode
3. Navigate to display URL
4. Fullscreen (F11)
5. Monitor online status (last_heartbeat)
6. Auto-restart if display goes offline
```

---

### Phase 6: Patient Portal - Responsive Web (Estimated: 2 weeks)

> **Note:** This replaces the original Flutter mobile app. Instead, we build a responsive web portal that works beautifully on mobile browsers.

**Week 1: Authentication & Home**
```
Day 1-2: Patient Authentication (Livewire)
- Responsive login page:
  • Phone number or email
  • Password
  • "Login" button
  • "Register" link
  • "Forgot Password" link
- Responsive registration page:
  • Personal info (name, birthdate, gender)
  • Phone number (required for SMS)
  • Email (optional)
  • Password
  • Emergency contact
  • Medical history (optional)
- Mobile-optimized forms
- Touch-friendly inputs

Day 3-4: Patient Home Dashboard (Livewire)
- Responsive layout (mobile-first):
  • Welcome message with user's name
  • Quick action cards:
    - Book Appointment
    - My Appointments
    - My Records
    - Current Queue (if active)
  • Upcoming appointment card (if any)
  • Recent notifications
- Bottom navigation (mobile) / Sidebar (desktop)
- Clean, modern UI using Flux free components

Day 5-7: Patient Profile (Livewire)
- View/edit personal info:
  • Name, birthdate, gender
  • Phone number
  • Address
  • Emergency contact
- Medical history:
  • Blood type
  • Allergies
  • Chronic conditions
  • Current medications
- Change password
- Notification preferences (SMS on/off)
- Responsive form layout

UI/UX Requirements:
- Mobile-first design
- Large touch targets (44px minimum)
- Clear typography (readable on small screens)
- Fast loading (optimized images, lazy load)
- Works offline-ish (graceful degradation)
```

**Week 2: Appointments & Queue**
```
Day 1-3: Book Appointment (Livewire Component)
- Step 1: Select consultation type
  • OB (Obstetrics)
  • PEDIA (Pediatrics)
  • GENERAL (General Medicine)
  • Card-based selection (large, touch-friendly)
- Step 2: Select date
  • Calendar view (mobile-optimized)
  • Show available dates
  • Show capacity per day (20/50 booked)
  • Disable fully booked dates
- Step 3: Fill details
  • Chief complaints (textarea)
  • Patient selection (self or dependent)
  • For child: enter child's info
- Step 4: Confirm & Submit
  • Review all details
  • Submit button
  • Show "Pending Approval" status
  • SMS confirmation sent

Day 4-5: My Appointments (Livewire Component)
- List all appointments (card-based, mobile-friendly):
  • Date and time
  • Consultation type (with color badge)
  • Status (Pending/Approved/Completed/Cancelled)
  • Queue number (if approved)
- Filter tabs: Upcoming | Past | All
- Tap appointment → View details:
  • Full appointment info
  • Queue number and estimated time
  • Cancel button (if pending/approved)
- Pull-to-refresh (if supported)
- Empty state: "No appointments yet"

Day 6-7: Queue Status (Livewire Component)
- Show current active queue (prominent display):
  ┌────────────────────────────────┐
  │  Your Queue                    │
  │  Date: Jan 25, 2026            │
  │  Type: OB                      │
  │                                │
  │  🎫 Queue Number: O-5          │
  │                                │
  │  ⏰ Estimated Time             │
  │     10:00 AM - 10:30 AM        │
  │                                │
  │  📊 Current Status             │
  │     Now Serving: O-3           │
  │     Ahead of you: 2            │
  │                                │
  │  🔔 SMS alerts enabled         │
  └────────────────────────────────┘
- Real-time updates via Reverb
- Progress indicator (visual)
- SMS notification when:
  • 2-3 patients ahead
  • Your turn (queue called)
- Fallback: Poll every 30 seconds
```

**Week 3: Medical Records & Polish**
```
Day 1-2: My Medical Records (Livewire Component)
- List all past visits (timeline/card view):
  • Date
  • Consultation type
  • Diagnosis (preview)
- Tap visit → View full record:
  • Chief complaints
  • Vital signs
  • Diagnosis
  • Prescriptions (with details)
  • Doctor notes
- Share/Download as PDF (optional)
- Search/filter by date range

Day 3-4: Responsive Layout Polish
- Test on multiple screen sizes:
  • iPhone SE (small)
  • iPhone 14 (medium)
  • iPad (tablet)
  • Desktop (large)
- Fix any layout issues
- Optimize touch targets
- Ensure forms work on mobile keyboards
- Test landscape orientation
- Verify readable font sizes

Day 5-7: Testing & Optimization
- Full patient journey test:
  1. Register new account
  2. Complete profile
  3. Book OB appointment
  4. Wait for approval (check SMS)
  5. View approved appointment with queue
  6. Check queue status
  7. Receive SMS when called
  8. View medical record after visit
- Performance testing:
  • Page load < 3 seconds on 3G
  • Smooth scrolling
  • No layout shifts
- Cross-browser testing:
  • Chrome (Android)
  • Safari (iOS)
  • Firefox
  • Edge
```

---

### Phase 7: Integration & Testing (Estimated: 2 weeks)

**Week 1: Full System Integration**
```
Day 1-2: End-to-End Flow Testing
- Online patient flow:
  1. Web (Patient): Register on mobile browser
  2. Web (Patient): Book OB appointment for tomorrow
  3. SMS: Receive booking confirmation
  4. Web (Nurse): See pending appointment
  5. Web (Nurse): Approve appointment
  6. SMS: Receive approval with queue number
  7. SMS: Receive day-before reminder (scheduled)
  8. Patient arrives at hospital
  9. Web (Nurse): Check in patient
  10. Web (Nurse): Interview and input vital signs
  11. Web (Nurse): Forward to doctor
  12. Web (Doctor): View patient, see all info
  13. Web (Doctor): Input diagnosis, prescriptions
  14. Web (Doctor): Add services, apply discount
  15. Web (Doctor): Forward to billing
  16. Web (Cashier): Process payment
  17. Web (Nurse): Mark queue completed
  18. Web (Patient): View completed medical record

- Walk-in patient flow:
  1. Patient arrives (no account)
  2. Web (Nurse): Create walk-in registration
  3. SMS: Patient receives login credentials
  4. Web (Nurse): Generate queue immediately
  5. Web (Nurse): Input vital signs
  6. Web (Nurse): Forward to doctor
  7. Same as online from step 12 onwards
  8. Web (Patient): Logs in later, sees record

Day 3-4: Queue Display Integration
- Setup 3 physical displays (or simulators)
- Test real-time updates:
  1. Nurse calls O-5
  2. OB display updates immediately
  3. Sound plays
  4. SMS sent to patient O-5
  5. Patient portal shows updated status
- Test multiple rapid changes:
  • Call 5 patients in quick succession
  • Skip 2 patients
  • Mark 1 urgent
  • Verify all displays stay in sync

Day 5: Reverb/WebSocket Testing
- Test real-time features:
  • Queue updates
  • Appointment approvals
  • Display updates
- Load testing:
  • 50+ concurrent connections
  • Rapid queue changes
  • Verify no lag, no dropped messages
- Fallback testing:
  • Disable Reverb
  • Verify polling works
  • Re-enable Reverb
  • Verify reconnection

Day 6-7: Security Testing
- Test authentication:
  • Session expiration
  • Invalid credentials
  • Role-based access
- Test authorization:
  • Patient can't access nurse portal
  • Nurse can't approve without permission
  • Doctor can only see assigned patients
- Test input validation:
  • SQL injection attempts
  • XSS attempts
  • CSRF protection
- Test SMS security:
  • Rate limiting
  • Phone validation
```

**Week 2: Bug Fixes & Performance**
```
Day 1-3: Bug Fixing
- Fix all critical bugs found in testing
- Fix UI/UX issues (especially mobile)
- Fix data inconsistencies
- Fix real-time update issues
- Test fixes

Day 4-5: Performance Optimization
- Database query optimization:
  • Add missing indexes
  • Optimize N+1 queries (eager loading)
  • Cache frequently accessed data
- Page load optimization:
  • Minify CSS/JS
  • Optimize images
  • Lazy load components
- Mobile performance:
  • Test on slow networks (3G)
  • Reduce bundle size
  • Optimize for low-end devices

Day 6-7: Load Testing
- Simulate hospital workload:
  • 200 patients registered
  • 50 active appointments
  • 30 patients in queue
  • 10 concurrent nurse actions
  • 5 concurrent doctor actions
  • 2 concurrent cashier actions
  • 100+ patient portal users
- Monitor:
  • Server CPU/memory
  • Database performance
  • Page response times
  • Reverb connection stability
  • SMS delivery rate
- Identify bottlenecks
- Optimize critical paths
```

---

### Phase 8: Deployment & Training (Estimated: 1-2 weeks)

**Week 1: Production Deployment**
```
Day 1-2: Server Setup
- Provision production server:
  • Ubuntu 24 LTS
  • PHP 8.4+
  • MySQL
  • Redis
  • Nginx
- Install SSL certificate (Let's Encrypt)
- Configure firewall
- Setup automated backups:
  • Daily database backups
  • 7-day retention
  • Offsite storage

Day 3-4: Application Deployment
- Deploy Laravel application:
  • Clone from repository
  • Install dependencies (composer)
  • Configure .env (production settings)
  • Run migrations
  • Run seeders (production data)
  • Configure Reverb (production mode)
  • Setup queue workers (supervisor)
  • Configure Laravel scheduler (cron)
  • Verify SMS provider configured
- Setup monitoring:
  • Error tracking (Sentry/Bugsnag)
  • Uptime monitoring
  • SMS delivery monitoring

Day 5: Final Testing in Production
- Test all workflows in production
- Test with real devices (staff phones)
- Test SMS delivery
- Performance test
- Load test production server

Day 6-7: Data Migration (if needed)
- Export existing patient data (if any)
- Transform to new format
- Import into production database
- Verify data integrity
```

**Week 2: Staff Training & Go-Live**
```
Day 1-2: Nurse Training
- System overview presentation
- Hands-on training:
  • Login and dashboard
  • Approve/decline appointments
  • Walk-in registration
  • Queue management
  • Check-in patients
  • Input vital signs
  • Search patient records
- Practice scenarios
- Q&A session
- Training materials (PDF guide)

Day 3: Doctor Training
- System overview presentation
- Hands-on training:
  • Login and dashboard
  • View patient queue
  • View patient information
  • Input diagnosis
  • Add prescriptions
  • Billing and discounts
  • Forward to billing/admission
- Practice scenarios
- Q&A session

Day 4: Cashier & Admin Training
- Cashier training:
  • Process payments
  • Handle partial payments
  • Print receipts
  • Daily reports
- Admin training:
  • User management
  • System settings
  • Queue display management
  • Generate reports

Day 5: Soft Launch (Pilot Day)
- Go live with limited scope:
  • Morning session only (8 AM - 12 PM)
  • One consultation type (GENERAL)
  • 10-20 patients maximum
- Staff present for support
- Monitor system closely
- Collect feedback
- Fix critical issues immediately

Day 6: Full Go-Live
- Full launch (all consultation types)
- All operating hours
- Staff on standby for support
- Monitor system performance
- Quick response to issues
- Patient feedback collection

Day 7: Post-Launch Review
- Review metrics:
  • System stability
  • User adoption
  • Error rates
  • SMS delivery rate
- Collect feedback
- Prioritize improvements
- Plan next iteration
```

---

## Patient Portal Features Summary

### For Patients/Parents (Responsive Web)

| Feature | Description | Notification |
|---------|-------------|--------------|
| **Register** | Create account with phone number | SMS: Welcome message |
| **Login** | Access portal from any device | - |
| **Book Appointment** | Select type, date, fill complaints | SMS: Booking confirmed |
| **View Appointments** | See all appointments and status | - |
| **Cancel Appointment** | Cancel pending/approved bookings | SMS: Cancellation confirmed |
| **Queue Status** | Real-time queue position | SMS: Queue called |
| **Medical Records** | View past visit history | - |
| **Profile** | Update personal/medical info | - |

### SMS Notifications (Automated)

| Event | Message Example |
|-------|-----------------|
| Appointment Booked | "HQMS: Your OB appointment for Jan 25 is pending approval." |
| Appointment Approved | "HQMS: Approved! Queue #O-5 for Jan 25, 10:00 AM. Arrive 30 mins early." |
| Appointment Declined | "HQMS: Your appointment was declined. Reason: [reason]. Please rebook." |
| Day Before Reminder | "HQMS: Reminder - Your OB appointment is tomorrow at 10:00 AM. Queue #O-5." |
| Queue Called | "HQMS: Your turn! Queue #O-5 is now being called. Please proceed." |
| Queue Nearly Called | "HQMS: Get ready! You are 2nd in line. Queue #O-5." |

---

## Confirmed Technology Stack

### ✅ Backend & Web Portal
- **Laravel 12** with Livewire 4.0 (full-stack reactive framework)
- **Flux UI Free** components only (tables, modals, forms, inputs)
- **Laravel Reverb** (WebSocket/real-time updates)
- **Laravel Sanctum** (session-based auth for web)
- **Spatie Laravel Permission** (roles & permissions)
- **Tailwind CSS 4.0** (responsive design)

### ✅ Notifications
- **SMS via Semaphore** (primary notification method)
- Queued SMS via Laravel Jobs
- SMS logging and analytics
- Firebase Cloud Messaging optional (future mobile app)

### ✅ Patient Access
- **Responsive Web Portal** (mobile-first design)
- Works on all devices (phone, tablet, desktop)
- No app installation required
- Uses same Laravel/Livewire stack

### ✅ Database
- **MySQL** (production)
- **SQLite** (development/testing)

### ❌ Not Using (Deferred)
- ~~Flutter mobile app~~ → Responsive web instead
- ~~Firebase Cloud Messaging (web)~~ → Optional for future mobile app only
- ~~Flux Pro components~~ → Free components only

---

## Responsive Design Guidelines

### Breakpoints (Tailwind)
```css
/* Mobile first approach */
sm: 640px   /* Small tablets */
md: 768px   /* Tablets */
lg: 1024px  /* Laptops */
xl: 1280px  /* Desktops */
```

### Mobile-First Principles
1. **Design for mobile first**, then scale up
2. **Touch-friendly** - Minimum 44px touch targets
3. **Readable typography** - 16px base font minimum
4. **Thumb-friendly** - Important actions within reach
5. **Fast loading** - Optimize for 3G connections
6. **Progressive enhancement** - Core features work everywhere

### Flux Free Components Available
- `flux:input` - Text inputs
- `flux:textarea` - Multi-line text
- `flux:select` - Dropdowns
- `flux:checkbox` - Checkboxes
- `flux:radio` - Radio buttons
- `flux:button` - Buttons
- `flux:modal` - Modals/dialogs
- `flux:dropdown` - Dropdown menus
- `flux:badge` - Status badges
- `flux:separator` - Dividers
- `flux:heading` - Headings
- `flux:text` - Text blocks

---

## Project Timeline Summary

### Total Estimated Duration: 12-14 weeks (3-3.5 months)

**Phase 0: Planning & Architecture** ✅ COMPLETED
- Documentation, SMS system implemented

**Phase 1: Backend Foundation** (2-3 weeks)
- Database, authentication, services, SMS integration
- Deliverable: Working backend with SMS

**Phase 2: Web Portal - Nurse** (3 weeks)
- Appointment management, queue system, vital signs
- Deliverable: Functional nurse portal

**Phase 3: Web Portal - Doctor** (2 weeks)
- Patient queue, diagnosis, prescriptions, billing
- Deliverable: Functional doctor portal

**Phase 4: Web Portal - Cashier & Admin** (2 weeks)
- Billing, reports, system management
- Deliverable: Complete staff portal

**Phase 5: Queue Display System** (1 week)
- Public displays with real-time updates
- Deliverable: Working displays

**Phase 6: Patient Portal - Responsive Web** (2 weeks)
- Registration, booking, queue status, records
- Deliverable: Mobile-friendly patient portal

**Phase 7: Integration & Testing** (2 weeks)
- End-to-end testing, bug fixes, optimization
- Deliverable: Fully tested system

**Phase 8: Deployment & Training** (1-2 weeks)
- Production deployment, staff training, go-live
- Deliverable: Live production system

---

## Success Criteria

### Technical Success
- ✅ All core features working as specified
- ✅ Real-time updates working smoothly
- ✅ System handles 200+ patients/day
- ✅ Page load < 3 seconds on mobile
- ✅ SMS delivery rate > 95%
- ✅ 99.9% uptime
- ✅ Zero data loss
- ✅ Responsive design works on all devices

### User Success
- ✅ Patients can book appointments easily from phone
- ✅ 60%+ reduction in waiting time
- ✅ Staff find system easier than manual
- ✅ Doctors have complete patient info
- ✅ Cashiers process payments faster
- ✅ 90%+ user satisfaction score

### Business Success
- ✅ Reduced overcrowding in waiting area
- ✅ Better patient flow management
- ✅ Complete digital medical records
- ✅ Accurate billing and accounting
- ✅ Data-driven decision making (reports)

---

## Future Enhancements (Post-Launch)

### Phase 2 Features (Months 3-6)
- Email notifications (optional)
- Print queue tickets at kiosk
- Laboratory results integration
- Multiple languages (Filipino, English)
- Doctor schedule management
- Patient ratings and feedback

### Phase 3 Features (Months 6-12)
- Native mobile app (Flutter) - if needed
- Push notifications (FCM)
- Inventory management
- Inpatient management
- Pharmacy system integration
- Insurance claims processing

### Advanced Features (Year 2+)
- AI-powered queue prediction
- Telemedicine integration
- Multi-hospital support

---

*Document Version: 3.0*
*Last Updated: January 20, 2026*
*Status: Ready for Development - Responsive Web Approach*
