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

## Development Process

### Phase 0: Planning & Architecture (✅ COMPLETED)
- ✅ Project definition documented
- ✅ Technology stack confirmed (Laravel 12 + Livewire + Flutter)
- ✅ Complete database schema designed (16 tables + Spatie)
- ✅ System flow with real-world scenarios
- ✅ User workflows documented
- ✅ Edge cases identified

**Next:** API specifications, Reverb events, UI wireframes

---

### Phase 1: Backend Foundation (Estimated: 2-3 weeks)

**Week 1: Database & Authentication**
```
Day 1-2: Project Setup
- Initialize Laravel 12 project
- Configure database (MySQL/PostgreSQL)
- Install packages: Livewire, Sanctum, Spatie Permission, Reverb
- Setup Redis for caching/queues
- Configure environment variables

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
- Setup Sanctum for API
- Setup Breeze/Jetstream for web
- Implement Spatie permissions
- Create policies for each model
- Test authentication flows:
  • Patient registration (mobile)
  • Staff login (web)
  • Token generation (API)
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

Day 4-5: Model Relationships & Accessors
- Define all relationships (hasMany, belongsTo, belongsToMany)
- Create accessors (formatted_number, effective_chief_complaints)
- Create scopes (today, pending, waiting, etc.)
- Test relationships with Tinker

Day 6-7: Business Logic (Services/Actions)
- AppointmentService (create, approve, decline)
- QueueService (generate, call, skip, complete)
- BillingService (calculate totals, apply discount)
- NotificationService (send to patients)
- Test business logic with unit tests
```

**Week 3: API Endpoints (For Flutter)**
```
Day 1-2: Authentication Endpoints
POST   /api/register
POST   /api/login
POST   /api/logout
POST   /api/verify-otp
GET    /api/user
PUT    /api/user/profile

Day 3-4: Appointment Endpoints
GET    /api/consultation-types (with availability)
GET    /api/doctors/availability
POST   /api/appointments (create appointment)
GET    /api/appointments/my (user's appointments)
PUT    /api/appointments/{id}/cancel
GET    /api/appointments/{id}

Day 5: Medical Records Endpoints
GET    /api/medical-records/my (user's history)
GET    /api/medical-records/{id}
GET    /api/prescriptions/my

Day 6-7: Queue & Notifications
GET    /api/queues/my (user's active queue)
GET    /api/notifications
PUT    /api/notifications/{id}/read

Testing:
- Test with Postman/Insomnia
- Document API with Scribe/OpenAPI
- Ensure Sanctum authentication works
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
  • Send notification to patient
- Decline appointment:
  • State reason (Flux form)
  • Suggest alternative date (date picker)
  • Send notification to patient
- Filters: By date, by type, by status

Day 6-7: Walk-in Registration (Livewire Component)
- Search existing patients (Flux search)
- Create new patient account:
  • Personal info form
  • Medical history (optional)
  • Generate temp password
- Create walk-in appointment:
  • Select consultation type
  • Fill chief complaints
  • Auto-approve
  • Auto-generate queue
- Print queue ticket (blade template + print.css)

Testing Scenario:
1. Nurse approves online appointment → Queue generated
2. Nurse creates walk-in → Queue generated
3. Both appear in same queue (correct order)
4. Verify notifications sent
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
  • Send notification to mobile app
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
- Review initial chief complaints (from app)
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
  • Notification settings
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

### Phase 6: Mobile App (Flutter) (Estimated: 4 weeks)

**Week 1: Setup & Authentication**
```
Day 1-2: Project Setup
- Initialize Flutter project
- Add packages:
  • dio (HTTP client)
  • provider/riverpod (state management)
  • flutter_secure_storage (token storage)
  • firebase_messaging (push notifications)
  • intl (date formatting)
- Setup folder structure:
  /lib
    /models
    /services
    /providers
    /screens
    /widgets
    /utils

Day 3-5: Authentication Screens
- Splash screen
- Login screen:
  • Phone/email input
  • Password input
  • "Login" button
  • "Register" link
- Registration screen:
  • Personal info
  • Phone number
  • Email
  • Password
  • Emergency contact
  • Medical history (optional)
- OTP verification (if SMS-based)
- Implement Sanctum token handling:
  • Store token securely
  • Attach to all API requests
  • Refresh token if expired

Day 6-7: API Service Layer (Dio)
- Create ApiService class
- Implement all endpoints:
  • Authentication (login, register, logout)
  • Appointments (list, create, cancel)
  • Medical records (list, view)
  • Notifications (list, read)
  • Queues (view active)
- Error handling (network errors, API errors)
- Token refresh interceptor
- Test with backend API
```

**Week 2: Home & Appointments**
```
Day 1-2: Home Screen
- Welcome message with user's name
- Quick actions:
  • Book Appointment
  • View My Appointments
  • View Medical Records
  • Current Queue Status (if active)
- Upcoming appointments (card list)
- Recent notifications

Day 3-5: Book Appointment Screen
- Select consultation type:
  • OB
  • PEDIA
  • GENERAL
- View availability:
  • "OB doctors available on Jan 25, 8:00 AM - 5:00 PM"
  • Show available dates (calendar)
  • Show capacity indicator (20/50 booked)
- Select date (date picker)
- Fill chief complaints (textarea)
- Submit button
- Confirmation dialog
- Show pending status

Day 6-7: My Appointments Screen
- List all appointments (past & upcoming)
- Filter: All, Pending, Approved, Completed
- Each appointment card shows:
  • Date, time
  • Consultation type
  • Status badge
  • Queue number (if approved)
  • Actions (Cancel if pending/approved)
- Pull-to-refresh
- Tap appointment → View details:
  • Chief complaints
  • Status history
  • Cancel button (with confirmation)

Testing:
1. Register new patient
2. Book OB appointment
3. View in "Pending" status
4. (Backend: Nurse approves)
5. Refresh app → See "Approved" with queue number
6. Cancel appointment
7. Verify cancelled
```

**Week 3: Queue Status & Medical Records**
```
Day 1-3: Queue Status Screen
- Show current active queue:
  ┌──────────────────────────────────┐
  │  Your Appointment                │
  │  Date: Jan 25, 2026              │
  │  Type: OB                        │
  │                                  │
  │  🎫 Queue Number: O-5            │
  │                                  │
  │  ⏰ Estimated Time               │
  │     10:00 AM - 10:30 AM          │
  │                                  │
  │  📊 Current Status               │
  │     Serving: O-3                 │
  │     Ahead of you: 2 patients     │
  │                                  │
  │  ⏳ Updates in real-time         │
  └──────────────────────────────────┘
- Real-time updates via WebSocket/Polling
- Notification when nearby (2-3 away)
- Notification when your turn
- Animated progress indicator

Day 4-5: Medical Records Screen
- List all past visits (timeline view):
  • Date
  • Consultation type
  • Doctor name (if available)
  • Diagnosis (preview)
- Tap visit → View full medical record:
  • Personal info
  • Chief complaints
  • Vital signs
  • Diagnosis
  • Prescriptions (with details)
  • Plan/notes
- Download/share record (PDF)
- Search/filter records

Day 6-7: Profile & Settings
- View/edit profile:
  • Personal info
  • Address
  • Emergency contact
  • Medical history (blood type, allergies, chronic conditions)
- Change password
- Notification settings (enable/disable)
- App settings (theme, language)
- Logout

Testing:
1. Book appointment for today
2. (Backend: Approve, generate queue)
3. Open Queue Status screen
4. Verify real-time updates
5. (Backend: Call previous queues)
6. Verify "ahead of you" count decreases
7. Receive "queue nearby" notification
8. View medical records from past visits
```

**Week 4: Notifications & Polish**
```
Day 1-2: Push Notifications (FCM)
- Setup Firebase Cloud Messaging
- Request notification permissions
- Handle notification types:
  • Appointment approved
  • Appointment declined
  • 1 day before reminder
  • 1 hour before reminder
  • Queue nearby
  • Queue called (your turn)
- Tap notification → Navigate to relevant screen
- Show notification badge count

Day 3-4: UI Polish & UX
- Consistent styling (Material Design)
- Loading indicators (shimmer effect)
- Empty states ("No appointments yet")
- Error states (network error, retry)
- Success/error snackbars
- Form validation
- Smooth transitions
- Pull-to-refresh on lists

Day 5-6: Testing & Bug Fixes
- End-to-end testing:
  • Complete user journey (register → book → notify → cancel)
  • Real-time updates work correctly
  • Notifications received on time
  • Medical records display correctly
- Test on both Android and iOS
- Test on different screen sizes
- Fix bugs

Day 7: Performance & Optimization
- Optimize API calls (caching)
- Optimize images (compress, lazy load)
- Optimize build size
- Test performance (no lag, smooth scrolling)
- Memory leak check
- Battery usage check

Release Testing Scenarios:
1. Patient journey (online booking):
   - Register → Book → Wait for approval → Receive notification → View queue status → Arrive at hospital
2. Patient journey (repeat visit):
   - Login → View past records → Book new appointment → Cancel → Rebook different date
3. Stress test:
   - 50 concurrent users booking appointments
   - Real-time updates with 100+ queue changes
4. Network scenarios:
   - Slow internet
   - No internet (offline behavior)
   - Server down (error handling)
```

---

### Phase 7: Integration & Testing (Estimated: 2 weeks)

**Week 1: Full System Integration**
```
Day 1-2: End-to-End Flow Testing
- Online patient flow:
  1. Mobile: Register patient
  2. Mobile: Book OB appointment for tomorrow
  3. Web (Nurse): See pending appointment
  4. Web (Nurse): Approve appointment
  5. Mobile: Receive approval notification with queue number
  6. Mobile: Receive 1-day-before reminder
  7. Next day: Mobile receive 1-hour-before reminder
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
  18. Mobile: View completed medical record
  19. Mobile: View prescriptions

- Walk-in patient flow:
  1. Patient arrives (no app, no account)
  2. Web (Nurse): Create walk-in registration
  3. Web (Nurse): Generate queue immediately
  4. Web (Nurse): Input vital signs
  5. Web (Nurse): Forward to doctor
  6. Same as online from step 12 onwards
  7. Patient given SMS with login credentials
  8. Patient downloads app later
  9. Patient logs in and sees medical record

Day 3-4: Queue Display Integration
- Setup 3 physical displays (or simulators)
- Test real-time updates:
  1. Nurse calls O-5
  2. OB display updates immediately
  3. Sound plays
  4. Mobile app notifies patient O-5
  5. Next patient O-6 sees updated status
- Test multiple rapid changes:
  • Call 5 patients in quick succession
  • Skip 2 patients
  • Mark 1 urgent
  • Verify all displays stay in sync

Day 5: Reverb/WebSocket Testing
- Test real-time features:
  • Queue updates
  • Appointment approvals
  • Notifications
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
  • Token expiration
  • Token refresh
  • Invalid token handling
- Test authorization:
  • Patient can't access nurse portal
  • Nurse can't approve without permission
  • Doctor can only see assigned patients
- Test input validation:
  • SQL injection attempts
  • XSS attempts
  • CSRF protection
- Test API rate limiting
- Test file upload security (if any)
```

**Week 2: Bug Fixes & Performance**
```
Day 1-3: Bug Fixing
- Fix all critical bugs found in testing
- Fix UI/UX issues
- Fix data inconsistencies
- Fix real-time update issues
- Test fixes

Day 4-5: Performance Optimization
- Database query optimization:
  • Add missing indexes
  • Optimize N+1 queries (eager loading)
  • Cache frequently accessed data
- API response time optimization:
  • Reduce payload size
  • Implement pagination
  • Add API caching
- Frontend performance:
  • Optimize Livewire components
  • Reduce unnecessary re-renders
  • Lazy load heavy components
- Mobile app performance:
  • Optimize API calls
  • Implement local caching
  • Reduce unnecessary rebuilds

Day 6-7: Load Testing
- Simulate hospital workload:
  • 200 patients registered
  • 50 active appointments
  • 30 patients in queue
  • 10 concurrent nurse actions
  • 5 concurrent doctor actions
  • 2 concurrent cashier actions
  • 100+ mobile app users
- Monitor:
  • Server CPU/memory
  • Database performance
  • API response times
  • Reverb connection stability
  • Mobile app performance
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
  • PHP 8.3+
  • MySQL/PostgreSQL
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
  • Run seeders (production data: consultation types, services, settings)
  • Configure Reverb (production mode)
  • Setup queue workers (supervisor)
  • Configure Laravel scheduler (cron)
- Deploy mobile app:
  • Build APK (Android)
  • Build IPA (iOS)
  • Upload to Google Play Store (internal testing)
  • Upload to Apple App Store (TestFlight)

Day 5: Final Testing in Production
- Test all workflows in production environment
- Test with production data
- Test external integrations (SMS, email, FCM)
- Performance test with real hardware
- Load test production server

Day 6-7: Data Migration (if needed)
- Export existing patient data (if any)
- Transform to new format
- Import into production database
- Verify data integrity
- Test with migrated data
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
- Practice scenarios:
  • Process 10 mock patients
  • Handle no-shows
  • Handle urgent patients
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
- Practice scenarios:
  • Process 5 mock patients
  • Different consultation types
- Q&A session
- Quick reference guide (PDF)

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
- Practice scenarios
- Q&A session

Day 5: Soft Launch (Pilot Day)
- Go live with limited scope:
  • Morning session only (8 AM - 12 PM)
  • One consultation type (e.g., GENERAL)
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
  • Performance issues
- Collect feedback from staff and patients
- Prioritize improvements
- Plan next iteration
```

---

## Real-World Implementation Checklist

### Pre-Launch (Must Complete)
- ✅ All core features working
- ✅ Database properly seeded
- ✅ API fully tested
- ✅ Real-time updates working
- ✅ Mobile app tested on real devices
- ✅ Queue displays tested on actual monitors
- ✅ Security audit completed
- ✅ Backup system configured
- ✅ SSL certificate installed
- ✅ Staff trained
- ✅ User guides created
- ✅ Support plan in place

### Launch Day
- ✅ All systems online
- ✅ Staff ready and trained
- ✅ Support team on standby
- ✅ Monitoring tools active
- ✅ Backup plan ready
- ✅ Communication plan (if system down)

### Post-Launch (First Week)
- ✅ Daily monitoring
- ✅ Daily feedback collection
- ✅ Bug fix priority queue
- ✅ Performance monitoring
- ✅ User adoption metrics
- ✅ Staff satisfaction survey

### Post-Launch (First Month)
- ✅ Feature usage analytics
- ✅ System performance review
- ✅ Staff re-training (if needed)
- ✅ Patient satisfaction survey
- ✅ Plan improvements
- ✅ Prioritize next features

---

## Maintenance & Support

### Daily Tasks
- Monitor server health
- Check error logs
- Review failed jobs (queue)
- Check backup status
- Monitor Reverb connections

### Weekly Tasks
- Review system performance
- Analyze usage patterns
- Review user feedback
- Update documentation
- Plan improvements

### Monthly Tasks
- Security updates
- Dependency updates
- Database optimization
- Generate monthly reports
- Review and archive old data

### As Needed
- Add new features
- Fix bugs
- Improve performance
- Scale infrastructure
- User training refreshers

---

## Laravel Architecture Overview

### Project Structure (Standard Laravel 12)
```
/hospital-queue-system
├── /app
│   ├── /Http
│   │   ├── /Controllers/Api          # API endpoints (Sanctum)
│   │   └── /Livewire                 # Livewire components
│   ├── /Models                       # Eloquent models
│   ├── /Policies                     # Authorization policies
│   ├── /Services                     # Business logic
│   ├── /Events                       # Reverb events
│   └── /Listeners                    # Event listeners
├── /database
│   ├── /migrations                   # Database schema
│   └── /seeders                      # Test data
├── /resources
│   └── /views
│       └── /livewire                 # Livewire blade views (Flux)
└── /routes
    ├── web.php                       # Livewire routes
    └── api.php                       # Sanctum API routes
```

### Key Laravel Packages & Their Purpose

#### Laravel Sanctum (API Authentication)
- Token-based authentication for Flutter app
- Protects API endpoints
- Token generation on login
- Token revocation on logout

#### Spatie Laravel Permission (Roles & Authorization)
- Role: `nurse`, `doctor`, `cashier`, `admin`
- Permissions: `view-queue`, `approve-appointment`, `add-diagnosis`, etc.
- Middleware: `role:doctor`, `permission:view-records`
- Gates & Policies for fine-grained control

#### Livewire + Flux (Web Portal)
- Full-stack reactive components (no separate frontend build)
- Real-time updates without page refresh
- Flux components: Tables, Modals, Forms, Dialogs
- Server-side rendering (SEO-friendly)

#### Laravel Reverb (Real-time Communication)
- WebSocket server (built into Laravel)
- Queue position updates (real-time)
- New appointment notifications
- Patient status changes
- Broadcasting events: `QueueUpdated`, `AppointmentApproved`

### User Roles & Permissions (Spatie)

**To be detailed in ROLES.md**, but high-level:

```php
// Roles
- patient (mobile app users)
- nurse (web portal - schedule/queue management)
- doctor (web portal - diagnosis/prescription)
- cashier (web portal - billing)
- admin (full access)

// Example Permissions
- view-appointments
- approve-appointments
- manage-queue
- input-vital-signs
- view-patient-records
- add-diagnosis
- add-prescription
- process-billing
- generate-reports
```

### API Structure (Sanctum-Protected)

**To be detailed in API.md**, but high-level endpoints:

```php
POST   /api/login                    # Get Sanctum token
POST   /api/logout                   # Revoke token

GET    /api/doctors/availability     # Check doctor schedules
POST   /api/appointments             # Request appointment
GET    /api/appointments/my          # User's appointments
GET    /api/records/my               # User's medical history

// All protected with: middleware(['auth:sanctum'])
```

### Real-time Events (Reverb)

**To be detailed in EVENTS.md**, but examples:

```php
// Server broadcasts:
event(new QueueUpdated($queueNumber));
event(new AppointmentApproved($appointment));
event(new PatientCalled($patientId));

// Livewire components listen:
public function getListeners()
{
    return ['QueueUpdated' => '$refresh'];
}

// Flutter app listens via WebSocket/Pusher client
```

---

## File Organization

### Check-in Points
- **Before major decisions** - Discuss technology choices, architecture decisions
- **After completing modules** - Review, test, and get approval before moving forward
- **When blocked** - Communicate issues immediately
- **Regular updates** - Progress reports at defined intervals

### Decision-Making Process
1. **Present options** with pros/cons
2. **Discuss implications** (cost, time, complexity)
3. **Make decision together**
4. **Document decision** and rationale

### Documentation Files Organization

We'll create these planning documents before coding:

```
/project-docs
  ├── PROJECT.md           # ✅ Project definition
  ├── WORKFLOW.md          # ✅ Development process
  ├── DATABASE.md          # ⏳ Complete database schema
  ├── API.md               # ⏳ All API endpoints (Sanctum)
  ├── ROLES.md             # ⏳ Spatie roles & permissions matrix
  ├── EVENTS.md            # ⏳ Reverb real-time events
  ├── USER-FLOWS.md        # ⏳ User workflow diagrams
  └── UI-WIREFRAMES.md     # ⏳ Screen layouts (Flux components)
```

---

## Confirmed Technology Stack

### ✅ Backend & Web
- **Laravel 12** with Livewire (full-stack reactive framework)
- **Flux** free components (tables, modals, dialogs, forms)
- **Laravel Reverb** (WebSocket/real-time notifications)
- **Laravel Sanctum** (API authentication for mobile)
- **Spatie Laravel Permission** (roles & permissions)

### ✅ Mobile
- **Flutter** with **Dio** (HTTP client for API calls)

### ⏳ To Be Decided
- **Database:** MySQL or PostgreSQL? (both work well with Laravel)
- **Hosting:** Cloud (AWS, DigitalOcean) or On-Premise?
- **Flutter State Management:** Provider, Riverpod, or Bloc?

---

## Code Standards & Best Practices

### Laravel-Specific Standards
- **PSR-12** coding standard
- **Laravel conventions** (naming, structure)
- **Service/Action pattern** for business logic
- **Repository pattern** (if needed for complex queries)
- **Form Requests** for validation
- **API Resources** for response formatting
- **Events & Listeners** for decoupled logic
- **Jobs & Queues** for async operations
- **Policies** with Spatie Permission for authorization

### Livewire Best Practices
- Component-based architecture
- Proper use of Flux components (tables, forms, modals)
- Real-time updates via Reverb events
- Efficient data loading (lazy loading, pagination)
- Form validation using Laravel rules

### Flutter/Dio Standards
- **BLoC/Provider/Riverpod** pattern (TBD)
- **Dio interceptors** for Sanctum token handling
- **Error handling** for API calls
- **Offline caching** (if needed)
- **Material Design** guidelines

### API Design (Laravel Sanctum)
- **RESTful** endpoints
- **Token-based** authentication (Sanctum)
- **Versioning** (v1, v2, etc.)
- **Consistent responses** (success/error format)
- **Rate limiting** (throttle middleware)
- **API documentation** (Scribe/OpenAPI)

### Database Standards
- **Migrations** for all schema changes
- **Seeders** for test data
- **Foreign key constraints**
- **Soft deletes** for sensitive data
- **Indexes** on frequently queried columns
- **Timestamps** (created_at, updated_at)

---

## Healthcare-Specific Considerations

### Data Privacy & Security
- **HIPAA compliance** considerations (if applicable)
- **Patient data encryption** at rest and in transit
- **Access control** - role-based permissions
- **Audit logs** - track who accessed what and when
- **Data backup** strategy and disaster recovery

### Reliability Requirements
- **99.9% uptime** target
- **Data integrity** - no lost records
- **Concurrent users** - handle peak clinic hours
- **Offline capability** (if needed for mobile app)

---

## Current Status & Focus

### ✅ Completed
- Project definition and problem analysis
- Feature requirements documented
- Data structure from hospital forms analyzed
- Technology stack confirmed (Laravel 12, Livewire, Flux, Reverb, Sanctum, Spatie, Flutter, Dio)

### 🎯 Current Focus: PLANNING ONLY (No Development Yet)
**Goal:** Complete architectural planning before any coding begins

### ⏳ Next Immediate Planning Steps
1. **Database Schema Design** - All tables, relationships, indexes
2. **Roles & Permissions Matrix** - Define all user roles and their capabilities using Spatie
3. **API Endpoint Specifications** - All routes for Flutter app (Sanctum-protected)
4. **Real-time Events Mapping** - What notifications/updates use Reverb
5. **User Workflow Diagrams** - Detailed flow for each user type
6. **UI/UX Wireframes** - Screen layouts using Flux components
7. **Development Milestones** - Timeline and deliverables

**🚫 NO CODE WRITTEN until complete planning is approved and documented**

---

## My Role (Claude)

### What I Can Help With
- ✅ Architecture and design recommendations
- ✅ Database schema design
- ✅ API endpoint specifications
- ✅ Code generation and examples
- ✅ Testing strategy and test cases
- ✅ Documentation and comments
- ✅ Debugging and troubleshooting
- ✅ Best practices and security guidance

### What I Need From You
- Technology stack preferences
- Team capabilities and expertise
- Access to test environments (when coding begins)
- Feedback and approval on deliverables
- Real hospital workflow clarifications (if needed)
- Priority decisions when tradeoffs exist

---

## Questions Before We Proceed with Planning

### Team & Organization
1. **Who are the team members?**
   - Laravel developers (count & experience level)?
   - Flutter developer(s)?
   - Who handles database design?
   - Project lead/manager?

2. **Team Laravel Experience**
   - Comfortable with Laravel 12 features?
   - Experience with Livewire?
   - Worked with Spatie Permission before?
   - Familiar with Laravel Reverb (new in Laravel 11+)?

### Technical Decisions
3. **Database Choice**
   - MySQL (most common with Laravel)?
   - PostgreSQL (more features, better for complex queries)?

4. **Flutter State Management**
   - Provider (simple, official)?
   - Riverpod (modern, recommended)?
   - BLoC (enterprise, more complex)?

5. **Hosting Environment**
   - Cloud (Laravel Forge, DigitalOcean, AWS)?
   - Hospital on-premise servers?
   - Hybrid approach?

### Project Scope
6. **Timeline Expectations**
   - Planning phase: 2-4 weeks?
   - Development: 3-6 months?
   - Any hard deadlines?

7. **Phased Rollout or All-at-Once?**
   - Start with Nurse module first?
   - Build all modules then deploy?
   - MVP features vs full features?

8. **Integration Requirements**
   - Any existing hospital systems?
   - SMS provider for notifications?
   - Payment gateway integration?
   - Lab/imaging system integration?

---

## Project Timeline Summary

### Total Estimated Duration: 16-20 weeks (4-5 months)

**Phase 0: Planning & Architecture** ✅ COMPLETED (2 weeks)
- PROJECT.md, WORKFLOW.md, DATABASE.md finalized
- Real-world scenarios documented
- Technology stack confirmed

**Phase 1: Backend Foundation** (2-3 weeks)
- Database, authentication, API endpoints
- Deliverable: Working API with Postman tests

**Phase 2: Web Portal - Nurse** (3 weeks)
- Appointment management, queue system, vital signs
- Deliverable: Functional nurse portal (live demo)

**Phase 3: Web Portal - Doctor** (2 weeks)
- Patient queue, diagnosis, prescriptions, billing
- Deliverable: Functional doctor portal (live demo)

**Phase 4: Web Portal - Cashier & Admin** (2 weeks)
- Billing, reports, system management
- Deliverable: Complete web portal system

**Phase 5: Queue Display System** (1 week)
- Public displays with real-time updates
- Deliverable: Working displays on monitors

**Phase 6: Mobile App (Flutter)** (4 weeks)
- Authentication, appointments, queue status, records
- Deliverable: Functional mobile app (APK/IPA)

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
- ✅ API response time < 500ms
- ✅ Mobile app smooth on mid-range devices
- ✅ 99.9% uptime
- ✅ Zero data loss
- ✅ Secure authentication and authorization

### User Success
- ✅ Patients can book appointments easily
- ✅ 80%+ online booking adoption (within 3 months)
- ✅ 60%+ reduction in waiting time
- ✅ Staff find system easier than manual
- ✅ Doctors have complete patient info
- ✅ Cashiers process payments faster
- ✅ 90%+ user satisfaction score

### Business Success
- ✅ Reduced overcrowding in waiting area
- ✅ Better patient flow management
- ✅ Improved patient satisfaction
- ✅ Complete digital medical records
- ✅ Accurate billing and accounting
- ✅ Data-driven decision making (reports)
- ✅ Hospital operational efficiency improved

---

## Risk Management

### Technical Risks

**Risk: Reverb/WebSocket Issues**
- Impact: Real-time updates fail
- Mitigation: Implement polling fallback
- Contingency: Can operate with 30-second polling

**Risk: Database Performance**
- Impact: Slow queries, system lag
- Mitigation: Proper indexing, query optimization, caching
- Contingency: Scale database server, add read replicas

**Risk: Mobile App Compatibility**
- Impact: App doesn't work on some devices
- Mitigation: Test on multiple devices, use stable Flutter version
- Contingency: Provide web-based mobile view

**Risk: Server Downtime**
- Impact: System unavailable
- Mitigation: Automated monitoring, auto-restart, load balancing
- Contingency: Manual queue system (printed tickets)

### User Adoption Risks

**Risk: Staff Resistance to Change**
- Impact: Low adoption, continued manual processes
- Mitigation: Thorough training, gradual rollout, support available
- Contingency: Additional training sessions, one-on-one coaching

**Risk: Patients Don't Download App**
- Impact: Low online booking rate
- Mitigation: QR codes at hospital, SMS reminders, staff promotion
- Contingency: Walk-in system works independently

**Risk: Internet Connectivity Issues**
- Impact: System slow or unavailable
- Mitigation: Reliable ISP, backup connection, local caching
- Contingency: Offline mode (limited functionality)

---

## Future Enhancements (Post-Launch)

### Phase 2 Features (Months 3-6)
- SMS appointment reminders (via SMS gateway)
- Email notifications
- Print queue tickets at kiosk
- Laboratory results integration
- Imaging results (X-ray, ultrasound) integration
- Multiple languages (Filipino, English)
- Doctor schedule management (doctors can set own availability)
- Patient ratings and feedback
- Telemedicine integration (video consultations)

### Phase 3 Features (Months 6-12)
- Inventory management (medicines, supplies)
- Inpatient management (full admission system)
- Pharmacy system integration
- Laboratory system integration
- Electronic medical records (EMR) full suite
- Insurance claims processing
- Financial reports and analytics (advanced)
- Referral system (to other hospitals/specialists)
- Patient portal (web version of mobile app)

### Advanced Features (Year 2+)
- AI-powered queue prediction
- Chatbot for common questions
- Automated appointment reminders (voice calls)
- Predictive analytics (patient volume forecasting)
- Mobile app for staff (doctors, nurses)
- Integration with national health systems
- Research and analytics module
- Multi-hospital support (chain management)

---

*Document Version: FINAL 2.0*  
*Last Updated: January 18, 2026*  
*Status: Ready for Development*
