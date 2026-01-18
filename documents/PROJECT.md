# Hospital Queue Management System
## Guardiano Maternity and Children Clinic and Hospital

**Location:** Bonifacio Extension Población, Tacurong City

---

## Project Overview

A comprehensive digital healthcare management system to replace manual paper-based processes with an integrated mobile and web platform for patients, nurses, doctors, and cashiers.

### Current Situation
- **Manual queue system** causing long waiting times
- **Paper-based records** making it difficult to track patient history
- **No visibility** into doctor availability
- **Inefficient workflow** from registration to discharge
- **Records stored in drawers/cabinets** - hard to locate past visits

---

## Problems Identified

### Patient Problems
1. Long waiting lines and overcrowded waiting areas
2. No visibility into doctor availability (schedule, leave, travel)
3. Cannot leave while waiting (must stay even if queue number is far)
4. Cannot plan their time effectively around appointments
5. No access to past medical records/history when needed

### Nurse Problems
1. Difficulty finding past records manually for patient context
2. Dealing with irritated patients due to long waits
3. Messy, inefficient paper-based recording system
4. Hard to search records from previous months/years
5. No advance information about patient condition before check-up

### Doctor Problems
1. Manual recording - cannot easily access past records
2. No advance information about next patient's condition
3. Difficult to access previous diagnoses, prescriptions, and treatment plans
4. No preparation time before seeing each patient

---

## Solution Overview

### Mobile App (Patient/Parent)
1. **View doctor availability** - Check specific dates/times when doctors are available
2. **Schedule appointments** - Submit checkup schedule with personal info and initial symptoms
3. **Appointment reminders** - Notifications for upcoming scheduled visits
4. **Medical history access** - View past records and diagnoses anytime

### Web Portal (Nurse)
1. **Schedule management** - View patient appointment requests with personal info and symptoms
2. **Queue generation** - Approve schedules and automatically generate queue (online + walk-in combined)
3. **Patient records** - Update personal information and record vital signs
4. **Patient routing** - Forward patient to appropriate doctor
5. **Record management** - Search, update, print, and download past records

### Web Portal (Doctor)
1. **Patient preview** - See next and upcoming patients with initial symptoms
2. **Medical documentation** - Record diagnosis, prescriptions, and treatment plans
3. **Billing input** - Enter fees, discounts, and available hospital drugs
4. **Admission forwarding** - Forward to admission if needed with timestamp

### Web Portal (Cashier)
1. **Transaction processing** - Handle patient billing
2. **Discharge management** - Process patient discharge

### Reporting System
1. **Comprehensive reports** - Access all necessary hospital operational reports

---

## System Flow

### Complete Patient Journey (Real-World Scenarios)

#### **Scenario 1: Online Booking - Pregnant Mother**
```
Maria (28 years old, pregnant) needs OB checkup

MOBILE APP (Home):
1. Maria opens mobile app
2. Selects "Book Appointment"
3. Chooses: OB consultation
4. Sees: "OB doctors available Jan 25, 8:00 AM - 5:00 PM"
5. Fills out:
   - Preferred date: Jan 25, 2026
   - Chief complaints: "Regular prenatal checkup, 7 months pregnant"
6. Submits appointment request
7. Status: PENDING (waiting nurse approval)

NURSE WEB PORTAL (Hospital):
8. Nurse logs in, sees pending appointments
9. Reviews Maria's request
10. Checks: Date available? ✓ Not too many bookings? ✓
11. Approves appointment
12. System auto-generates: Queue O-5 (OB queue number 5)
13. System calculates: Estimated time 10:00 AM
14. Notification sent to Maria

MARIA'S PHONE:
15. Push notification: "Appointment approved! Queue: O-5, Jan 25 at ~10:00 AM"
16. Reminders sent:
    - Jan 24 (1 day before): "Tomorrow is your appointment"
    - Jan 25 at 9:00 AM (1 hour before): "Your appointment is in 1 hour"

JAN 25 - APPOINTMENT DAY:
17. Maria arrives at hospital at 9:45 AM
18. Goes to nurse station
19. Nurse: "Hi Maria! Queue O-5?" 
20. Maria: "Yes!"
21. Nurse clicks "Check In" → Status: CHECKED_IN

NURSE INTERVIEW:
22. Nurse reviews info already in system (from app)
23. Nurse asks additional questions, updates chief complaints:
    - Original: "Regular prenatal checkup, 7 months pregnant"
    - Updated: "Regular prenatal checkup, 7 months pregnant, experiencing back pain"
24. Nurse inputs vital signs:
    - BP: 120/80
    - Temp: 36.8°C
    - Weight: 65 kg
    - FHT (Fetal Heart Tone): 145 bpm
    - Fundal Height: 28 cm
25. Nurse clicks "Forward to Doctor"
26. Queue status: SERVING

WAITING AREA DISPLAY (Monitor):
[Shows in real-time via Reverb]
┌─────────────────────────────────┐
│      OB QUEUE DISPLAY          │
├─────────────────────────────────┤
│   🔊 NOW SERVING: O-5          │
├─────────────────────────────────┤
│   NEXT IN LINE:                │
│   • O-6  (~10:30 AM)           │
│   • O-7  (~11:00 AM)           │
└─────────────────────────────────┘

MARIA'S PHONE:
- Notification: "Queue O-5 is now being served. Please proceed to consultation room."

DOCTOR EXAMINATION:
27. Doctor sees Maria's complete info on screen:
    - Personal details, medical history
    - Chief complaints (both initial and updated)
    - Vital signs already recorded
28. Doctor examines Maria
29. Doctor inputs:
    - Diagnosis: "Intrauterine pregnancy, 28 weeks AOG, back pain due to pregnancy"
    - Plan: "Continue prenatal vitamins, back exercises"
    - Prescriptions: (clicks add medication)
      • Ferrous Sulfate 300mg - 1x daily - 30 days
      • Calcium 500mg - 2x daily - 30 days
30. Doctor sees ultrasound needed
31. Doctor adds to billing:
    - Professional Fee: ₱1,000
    - OB Ultrasound (TVS-OB): ₱1,500
32. Doctor: "It's Sunday, so there's ₱500 emergency fee"
33. System auto-adds: Emergency Fee (Sunday): ₱500
34. Doctor applies family discount (Maria is staff's cousin):
    - Discount type: Family
    - Discount amount: ₱300
    - Approved by: Dr. Santos
35. Doctor clicks "Forward to Billing"

CASHIER:
36. Cashier sees billing for Maria (O-5):
    ┌──────────────────────────────┐
    │ Professional Fee      ₱1,000 │
    │ TVS-OB Ultrasound    ₱1,500 │
    │ Emergency Fee (Sun)    ₱500 │
    │ ─────────────────────────── │
    │ Subtotal            ₱3,000 │
    │ Discount (Family)    -₱300 │
    │ ─────────────────────────── │
    │ TOTAL               ₱2,700 │
    └──────────────────────────────┘
37. Maria pays: ₱2,700 (Cash)
38. Cashier clicks "Mark as Paid"
39. Receipt printed
40. Nurse marks queue as COMPLETED
41. Done! Maria leaves with prescriptions

MOBILE APP (Later):
42. Maria logs into app
43. Views her medical record:
    - Date: Jan 25, 2026
    - Diagnosis: Intrauterine pregnancy, 28 weeks AOG
    - Prescriptions: Ferrous Sulfate, Calcium
    - Next visit: Feb 10, 2026 (she can book now)
```

#### **Scenario 2: Walk-in Patient - Child with Fever**
```
Mrs. Santos brings her 5-year-old son (fever, cough) - No appointment

AT HOSPITAL (9:00 AM):
1. Mrs. Santos arrives with sick child
2. Goes directly to nurse station
3. Nurse: "First time here?"
4. Mrs. Santos: "Yes, my son has high fever"

NURSE CREATES ACCOUNT:
5. Nurse opens "Walk-in Registration"
6. Asks for patient info, fills form:
   - Mother's name: Mrs. Santos
   - Mother's phone: 0917-XXX-XXXX
   - Child's name: Juan Santos
   - Age: 5 years old
   - Chief complaint: "High fever (39°C), cough for 2 days"
7. Nurse creates:
   - User account for Mrs. Santos (can use app later with temp password)
   - Medical record for Juan (child profile under Mrs. Santos' account)
8. Selects: PEDIA consultation
9. Clicks "Create Walk-in Appointment"
10. System auto-approves (walk-in = instant)
11. System generates: Queue P-3 (PEDIA queue number 3)
12. Estimated time: ~9:30 AM
13. Nurse gives queue number card:
    ┌──────────────────┐
    │  QUEUE TICKET    │
    │                  │
    │     P-3          │
    │                  │
    │  Jan 25, 2026    │
    │  Est: 9:30 AM    │
    └──────────────────┘

IMMEDIATE VITAL SIGNS:
14. Since walk-in, nurse takes vitals immediately:
    - Temp: 39.2°C (high!)
    - Weight: 18 kg
    - Height: 110 cm
    - Cardiac Rate: 110 bpm
15. Nurse marks as URGENT (high fever)
16. Queue priority changed: URGENT
17. Doctor notified: "Urgent patient P-3"

WAITING (Very Short):
18. PEDIA monitor shows:
    ┌─────────────────────────────────┐
    │   🔊 NOW SERVING: P-2          │
    │   🚨 URGENT: P-3 (next)        │
    └─────────────────────────────────┘
19. Within 5 minutes, P-2 done
20. Nurse calls: "P-3! Juan Santos!"
21. Mrs. Santos brings Juan to doctor

DOCTOR:
22. Doctor sees: Urgent, high fever, 5 years old
23. Examines Juan
24. Diagnosis: "Acute Upper Respiratory Tract Infection"
25. Prescribes:
    - Paracetamol 250mg - every 4-6 hours - 5 days
    - Amoxicillin 250mg - 3x daily - 7 days
26. Both medicines available in hospital pharmacy
27. Doctor adds to billing:
    - Professional Fee: ₱500
    - Amoxicillin 250mg x 21 tabs: ₱210
    - Paracetamol 250mg x 20 tabs: ₱100
28. No discount needed
29. Forward to billing

CASHIER & DISCHARGE:
30. Total: ₱810
31. Mrs. Santos pays
32. Pharmacy gives medicines
33. Juan discharged with instructions
34. Total time: 30 minutes from arrival!

LATER (SMS):
35. Mrs. Santos receives SMS:
    "Your account at Guardiano Hospital is ready!
     Login: 0917-XXX-XXXX
     Temp Password: XXXX
     Download the app to book future appointments!"
```

#### **Scenario 3: No-Show Patient (Queue Management)**
```
QUEUE SITUATION (Jan 25, 10:00 AM):
Current OB Queue:
- O-4: SERVING (currently with doctor)
- O-5: WAITING (not arrived yet)
- O-6: WAITING (checked in, waiting)
- O-7: WAITING (checked in, waiting)

PROBLEM:
1. O-4 finishes consultation
2. System should call O-5 next
3. But O-5 hasn't checked in (no-show so far)

NURSE SOLUTION:
4. Nurse sees O-5 not present
5. Nurse clicks "Skip" button on O-5
6. System marks O-5 as "SKIPPED"
7. Nurse manually calls O-6 instead
8. O-6 proceeds to doctor
9. System updates O-6 status: SERVING

DISPLAY UPDATES (Real-time):
┌─────────────────────────────────┐
│   🔊 NOW SERVING: O-6          │
│   SKIPPED: O-5 (not present)   │
│   NEXT: O-7                    │
└─────────────────────────────────┘

IF O-5 ARRIVES LATE:
10. O-5 (Maria) arrives at 10:30 AM (late!)
11. Nurse: "Sorry you were skipped. Let me add you back"
12. Nurse clicks "Re-activate" O-5
13. O-5 added back to end of queue
14. New position: O-10 (last in line)
15. Maria waits her new turn

This flexibility allows nurse to manage real-world scenarios!
```

#### **Scenario 4: Emergency After Hours**
```
SITUATION: Saturday, 8:00 PM (after regular hours)

1. Patient arrives with pregnancy emergency
2. Walk-in registration (same as Scenario 2)
3. System detects:
   - Time: 8:00 PM (after 5:00 PM)
   - Day: Saturday
4. Nurse adds services to billing
5. System AUTO-ADDS:
   - Emergency Fee: ₱500
   - Reason: "After 5pm + Saturday"
6. Display shows on bill:
   ┌──────────────────────────────┐
   │ Professional Fee      ₱1,000 │
   │ Emergency Fee (Sat+PM) ₱500 │
   │ Ultrasound           ₱1,500 │
   │ ─────────────────────────── │
   │ TOTAL               ₱3,000 │
   └──────────────────────────────┘
```

#### **Scenario 5: Patient Needs Admission**
```
CONTINUATION OF SCENARIO 1 (Maria):

DOCTOR DECIDES ADMISSION NEEDED:
1. During examination, doctor finds complications
2. Doctor: "Maria, I recommend admission for monitoring"
3. Doctor clicks "Forward to Admission" (instead of billing)
4. System creates admission record:
   - Admission Number: ADM-2026-001
   - Reason: "Pregnancy complications requiring monitoring"
   - Room: To be assigned
5. Nurse escorts Maria to admission area
6. Admission team takes over
7. Billing for admission: SEPARATE SYSTEM (existing)

IN OUR SYSTEM:
- Medical record: COMPLETED
- Queue: COMPLETED
- Admission: RECORDED (for reports only)
- No billing transaction (admission handles own billing)

FOR REPORTS:
- Admin can see: "5 patients admitted today"
- Track admission rates
- Track which doctors admit most patients
```

---

## Detailed User Workflows

### Patient (Mobile App) Complete Flow
```
1. REGISTRATION
   - Download app
   - Enter phone number
   - Receive OTP
   - Set password
   - Fill profile (name, address, emergency contact)
   - Optional: Add medical history (blood type, allergies)

2. BOOK APPOINTMENT
   - Select consultation type (OB/PEDIA/GENERAL)
   - View doctor availability (no names, just dates)
   - Choose date
   - Fill chief complaints
   - Submit request
   - Wait for approval notification

3. RECEIVE NOTIFICATIONS
   - Appointment approved (with queue number)
   - OR Appointment declined (with reason + suggested date)
   - 1 day before reminder
   - 1 hour before reminder
   - "Queue nearby" (2-3 patients away)
   - "Your turn now!"

4. DAY OF APPOINTMENT
   - Arrive at hospital
   - Check in with nurse
   - Wait for queue number to be called
   - Interview with nurse
   - Vital signs recorded
   - Consultation with doctor
   - Proceed to billing
   - Pay and get receipt

5. VIEW MEDICAL HISTORY
   - Open app anytime
   - View all past visits
   - See diagnoses
   - See prescriptions
   - Download/share records if needed

6. CANCEL APPOINTMENT
   - Open "My Appointments"
   - Select appointment
   - Click "Cancel"
   - Confirm cancellation
   - Receive confirmation
```

### Nurse (Web Portal) Complete Flow
```
1. LOGIN
   - Enter email/password
   - Dashboard shows:
     • Pending appointments (need approval)
     • Today's queue status (all types)
     • Checked-in patients
     • Waiting for vital signs

2. APPROVE APPOINTMENTS
   - View pending appointments
   - Check date capacity
   - Approve or Decline
   - If decline: State reason, suggest new date
   - System auto-generates queue number

3. WALK-IN REGISTRATION
   - Patient arrives
   - Create/find patient account
   - Fill personal info
   - Fill chief complaints
   - Create walk-in appointment (auto-approved)
   - Queue auto-generated
   - Print queue ticket

4. CHECK-IN PATIENTS
   - Patient with online booking arrives
   - Search by name or queue number
   - Click "Check In"
   - Status updated

5. MANAGE QUEUE
   - View current queue (all types separately)
   - Call next patient (click "Call")
   - Skip no-show patients
   - Mark urgent patients
   - Real-time updates to displays

6. INTERVIEW & VITAL SIGNS
   - Review initial complaints from app
   - Ask follow-up questions
   - Update chief complaints if needed
   - Input vital signs:
     • Basic (all): Temp, BP, CR, RR
     • OB: FHT, Fundal Height, LMP
     • PEDIA/General: Weight, Height, Circumferences
   - Click "Forward to Doctor"

7. POST-CONSULTATION
   - Doctor finishes
   - Nurse marks queue as completed
   - OR forwards to billing
   - OR forwards to admission

8. SEARCH PATIENT RECORDS
   - Search by name, phone, or queue number
   - View complete medical history
   - Update information if needed
   - Print records if needed
```

### Doctor (Web Portal) Complete Flow
```
1. LOGIN
   - Dashboard shows:
     • Current queue (patients waiting)
     • Next patient details
     • Today's schedule
     • Pending diagnoses

2. VIEW QUEUE
   - See all patients in queue
   - See who's next
   - View patient info before calling
   - Review chief complaints
   - Review vital signs

3. CONSULTATION
   - Patient enters room
   - View complete patient history:
     • Personal info, medical history
     • Chief complaints (initial + updated)
     • Vital signs (just recorded by nurse)
     • Past visits and diagnoses
   - Examine patient
   - Input diagnosis:
     • Pertinent HPI/PE
     • Diagnosis text
     • Treatment plan
     • Procedures done (if any)

4. PRESCRIBE MEDICATIONS
   - Click "Add Prescription"
   - Select from hospital drugs OR type custom
   - Enter dosage, frequency, duration
   - Add instructions
   - Can add multiple medications
   - Free-text prescription notes area

5. BILLING PREPARATION
   - Add services to bill:
     • Professional fee
     • Ultrasounds/procedures
     • Hospital drugs
   - System auto-calculates emergency fees
   - Apply discount if needed:
     • Select discount type (family/senior/PWD)
     • Enter discount amount
     • Enter reason
     • Click "Apply Discount" (recorded as approved by doctor)

6. DECISION: OUTPATIENT OR ADMISSION
   - Outpatient: Click "Forward to Billing"
   - Admission: Click "Forward to Admission"
     • Fill admission form
     • Reason for admission
     • Room preference
     • Admission team notified

7. VIEW REPORTS
   - Daily patient count
   - Patients seen today
   - Upcoming patients
   - Admission statistics
```

### Cashier (Web Portal) Complete Flow
```
1. LOGIN
   - Dashboard shows:
     • Pending bills
     • Today's transactions
     • Total revenue

2. PROCESS BILLING
   - Patient arrives from doctor
   - View billing details:
     • Patient name, queue number
     • Itemized charges
     • Subtotal
     • Emergency fees (if any)
     • Discount (if approved by doctor)
     • Total amount
   - Verify items with patient
   - Select payment method (cash/card/gcash)
   - Enter amount paid
   - System calculates change/balance
   - Click "Process Payment"
   - Print receipt

3. HANDLE PARTIAL PAYMENTS
   - If patient can't pay full amount
   - Record partial payment
   - Status: PARTIAL
   - Balance tracked
   - Follow-up payment later

4. DAILY REPORTS
   - View total transactions
   - View total revenue
   - Payment method breakdown
   - Discounts given
   - Export reports
```

---

## Real-World Edge Cases Handled

### 1. **Multiple Patients, Same Name**
```
Problem: 3 patients named "Maria Santos"
Solution: 
- System shows: Maria Santos (0917-XXX-1234)
- Display full contact info
- Show date of birth
- Show last visit date
- Nurse can easily identify correct patient
```

### 2. **Queue Numbers Reset Daily**
```
Scenario: Yesterday's O-5, today's O-5
Solution:
- Queue numbers tied to date
- Database: queue_number + queue_date + type (unique)
- Yesterday's queue archived
- Fresh numbers start today
```

### 3. **Doctor Unavailable (Leave/Emergency)**
```
Problem: Dr. Santos on leave, has appointments
Solution:
- Admin/Nurse marks doctor as unavailable
- System blocks new appointments for that doctor
- Existing appointments: Nurse reassigns to another OB doctor
- Patients notified of doctor change
```

### 4. **Multiple Consultation Types for One Patient**
```
Scenario: Mother needs OB checkup, brings child for PEDIA
Solution:
- Create 2 separate appointments
- One account (mother)
- Two queues: O-5 (mother) and P-3 (child)
- Can be same day or different days
- System allows multiple appointments per user
```

### 5. **Late Arrival After Queue Passed**
```
Scenario: Queue O-5, patient arrives when O-8 is being served
Solution:
- Nurse sees O-5 marked "skipped"
- Options:
  A. Re-activate and add to end (O-12)
  B. Mark as "no-show" (appointment cancelled)
- Nurse decides based on situation
- Flexible system for real-world scenarios
```

### 6. **System Down / Internet Issues**
```
Fallback:
- Print queue tickets still work (manual fallback)
- Nurse has printed schedule list
- Manual queue management
- Data syncs when system back online
- Critical: Medical records never lost (database backup)
```

---

## Data Structure

### Form Type System
The hospital uses **ONE unified form** with **THREE types**:
- **OB** (Obstetrics)
- **PEDIA** (Pediatrics)
- **GENERAL** (General Medicine)

*Note: Some fields are filled or left blank depending on the type selected*

### Patient Record Fields

#### Basic Information
- **Visit Type:** NEW / OLD / REVISIT
- **Service Type:** Checkup / Admission
- **Date:** Visit date
- **Type:** OB / PEDIA / GENERAL

#### Personal Information
- Last Name
- First Name
- Middle Name
- **Status:** CHILD / SINGLE / MARRIED / WIDOW
- **Complete Address:**
  - Province
  - Municipality
  - Barangay/Purok
  - Street
- Contact Number
- Occupation
- Chief Complaints (Initial symptoms)

#### Vital Signs
- **Temp** (Temperature)
- **BP** (Blood Pressure) - mmHg
- **CR** (Cardiac Rate) - bpm
- **RR** (Respiratory Rate) - cpm
- **Time Examined by Physician** (AM/PM)
- **Procedure Done**
- **Professional Fee**
- **Amount**
- **Time Received in Billing**
- **Time Ended in Billing**

#### OB-Specific Fields (Conditional)
- **FHT** (Fetal Heart Tone) - bpm
- **FH** (Fundal Height) - cm
- **HC** (Head Circumference)
- **LMP** (Last Menstrual Period)

#### PEDIA/GENERAL Fields (Conditional)
- **Weight** - kg
- **Height** - cm
- **Head Circumference** - cm
- **Chest Circumference** - cm

#### Medical Documentation
- **Diagnosis**
- **Plan** (Treatment plan)
- **Pertinent HPI/PE** (History of Present Illness / Physical Examination)

#### Billing Information
- **Professional Fee**
- **Ultrasounds** (OB Gyne / General Ultrasound)
- **Other Fees/Medications**
- Guard in Charge notation

---

## Technology Stack

### Backend & Web Portal
- **Framework:** Laravel 12 (latest)
- **Frontend:** Livewire (full-stack framework)
- **UI Components:** Flux (free components - tables, modals, dialogs, forms)
- **Real-time:** Laravel Reverb (WebSocket server for notifications, queue updates)
- **Authentication (Web):** Laravel Breeze/Jetstream (Livewire starter kit)
- **Authentication (API):** Laravel Sanctum (token-based API authentication)
- **Authorization:** Spatie Laravel Permission (roles & permissions management)
- **Database:** MySQL/PostgreSQL (Laravel standard)

### Mobile App
- **Framework:** Flutter
- **HTTP Client:** Dio (API integration with Laravel Sanctum)
- **State Management:** Provider/Riverpod (TBD based on team preference)
- **Local Storage:** Hive/SharedPreferences
- **Push Notifications:** Firebase Cloud Messaging (FCM)

### Key Laravel Packages
- `laravel/sanctum` - API authentication for Flutter app
- `spatie/laravel-permission` - Role-based access control
- `livewire/livewire` - Full-stack reactive components
- `livewire/flux` - Free UI components library
- Laravel Reverb - Real-time broadcasting (notifications, queue updates)

### Infrastructure
- **Cache/Queue:** Redis
- **Session Storage:** Redis
- **File Storage:** Local/S3 (for future document uploads)
- **Real-time Server:** Laravel Reverb (WebSocket)

---

## Team Roles

### Development Team Structure
- **Backend/Full-stack Developer(s)** - Laravel + Livewire development
- **Mobile Developer(s)** - Flutter development
- **Database Designer** - Schema design and optimization
- **UI/UX Designer** - Interface design (working with Flux components)
- **Project Manager/Lead** - Coordination and planning
- **QA/Testing** - Testing across web and mobile

*Note: Roles to be specifically assigned to team members*

---

## Project Goals

### Primary Objectives
1. **Reduce waiting times** by 60%+ through appointment scheduling
2. **Digitize all records** for instant access
3. **Improve patient experience** with visibility and control
4. **Increase staff efficiency** through streamlined workflows
5. **Maintain data accuracy** and medical history continuity

### Success Metrics
- Average wait time reduction
- Patient satisfaction scores
- Record retrieval time (from minutes to seconds)
- Staff workflow efficiency improvement
- System uptime and reliability

---

## Next Steps

### Current Focus: Planning & Scoping (No Code Yet)
**Goal:** Define complete project scope, goals, and workflow BEFORE development

1. ✅ Technology stack confirmed (Laravel + Livewire + Flutter)
2. ⏳ Define complete system architecture
3. ⏳ Design database schema (roles, permissions, relationships)
4. ⏳ Map out all user workflows in detail
5. ⏳ Create API endpoint specifications for Flutter app
6. ⏳ Define real-time notification requirements (Reverb events)
7. ⏳ Plan Spatie roles & permissions structure
8. ⏳ Set development phases and milestones

**Development starts AFTER complete planning is approved**

---

*Document Version: 1.0*  
*Last Updated: January 18, 2026*
