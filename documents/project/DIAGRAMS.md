# System Diagrams

## Table of Contents

1. [Context Diagram](#1-context-diagram)
2. [Logical Data Flow Diagram (DFD)](#2-logical-data-flow-diagram)
3. [Physical Data Flow Diagram (DFD)](#3-physical-data-flow-diagram)
4. [Entity Relationship Diagram (ERD)](#4-entity-relationship-diagram)

---

## 1. Context Diagram

The Context Diagram shows the system as a single process and its interactions with external entities.

```
                                    ┌─────────────────┐
                                    │   SMS Gateway    │
                                    │  (Semaphore)     │
                                    └────────▲─────────┘
                                             │
                                    SMS Notifications
                                    (Queue alerts,
                                     Appointment updates)
                                             │
┌──────────────┐                  ┌──────────┴──────────┐                  ┌──────────────┐
│              │  Book Appt,      │                     │  Manage Users,   │              │
│   Patient /  │  View Queue,     │                     │  Services,       │    Admin      │
│   Parent     │  View Records,   │                     │  Drugs,          │              │
│              │  Check Status    │                     │  Discounts       │              │
│              ├─────────────────►│                     │◄─────────────────┤              │
│              │◄─────────────────┤                     ├─────────────────►│              │
│              │  Queue Position, │                     │  Reports,        │              │
│              │  Notifications,  │      CareTime       │  User Lists      │              │
│              │  Medical Records │       (HQMS)        │                  └──────────────┘
└──────────────┘                  │                     │
                                  │  Hospital Queue     │                  ┌──────────────┐
┌──────────────┐                  │  Management         │  Process Billing,│              │
│              │  Manage Appts,   │  System             │  Receive Payment │   Cashier     │
│    Nurse     │  Register Walk-  │                     │◄─────────────────┤              │
│              │  ins, Record     │                     ├─────────────────►│              │
│              │  Vitals, Manage  │                     │  Bill Details,   │              │
│              │  Queue           │                     │  Payment Receipt └──────────────┘
│              ├─────────────────►│                     │
│              │◄─────────────────┤                     │                  ┌──────────────┐
│              │  Patient Info,   │                     │  Push Notifs     │   Firebase    │
│              │  Queue Status    │                     │  (FCM Tokens)    │   Cloud       │
└──────────────┘                  │                     ├─────────────────►│   Messaging   │
                                  │                     │                  │              │
┌──────────────┐                  │                     │                  └──────────────┘
│              │  View Queue,     │                     │
│   Doctor     │  Examine Patient,│                     │                  ┌──────────────┐
│              │  Diagnose,       │                     │  Real-time       │   WebSocket   │
│              │  Prescribe       │                     │  Queue Updates   │   Server      │
│              ├─────────────────►│                     ├─────────────────►│  (Reverb)     │
│              │◄─────────────────┤                     │◄─────────────────┤              │
│              │  Patient Records,│                     │  Broadcast       └──────────────┘
│              │  Vital Signs     │                     │  Events
└──────────────┘                  └─────────┬───────────┘
                                            │
                                   ┌────────▼─────────┐
                                   │    Database       │
                                   │  (SQLite / MySQL) │
                                   └──────────────────┘
```

### External Entities

| Entity | Description |
|--------|-------------|
| **Patient / Parent** | Books appointments (self or dependents), tracks queue position, views medical records |
| **Nurse** | Manages appointments, registers walk-ins, records vital signs, manages queue |
| **Doctor** | Examines patients, records diagnosis, writes prescriptions, manages admissions |
| **Cashier** | Processes billing, receives payments, applies discounts |
| **Admin** | Manages users, services, drugs, discounts, system settings |
| **SMS Gateway (Semaphore)** | External SMS service for sending notifications |
| **Firebase Cloud Messaging** | Push notification service for mobile app |
| **WebSocket Server (Reverb)** | Real-time broadcasting for queue updates and notifications |
| **Database** | Persistent data storage (SQLite for development, MySQL for production) |

---

## 2. Logical Data Flow Diagram

The Logical DFD shows *what* the system does without concern for physical implementation.

### Level 0 (Overview)

```
┌──────────┐    Appointment     ┌─────────────────────────────────┐     Queue Status    ┌──────────┐
│ Patient / ├──────Request──────►│                                 ├────────Update───────►│  Public   │
│ Parent   │◄──Confirmation─────┤                                 │                      │  Display  │
└──────────┘                    │                                 │                      └──────────┘
                                │         0.0                     │
┌──────────┐    Walk-in Info    │                                 │     SMS / Push      ┌──────────┐
│  Nurse   ├───────────────────►│    Hospital Queue               │─────Notification────►│ Patient   │
│          │◄──Queue Number─────┤    Management System            │                      │ Mobile    │
└──────────┘                    │                                 │                      └──────────┘
                                │                                 │
┌──────────┐    Diagnosis       │                                 │
│  Doctor  ├───────────────────►│                                 │
│          │◄──Patient Info─────┤                                 │
└──────────┘                    │                                 │
                                │                                 │
┌──────────┐    Payment         │                                 │
│ Cashier  ├───────────────────►│                                 │
│          │◄──Bill Details─────┤                                 │
└──────────┘                    └────────────────┬────────────────┘
                                                 │
                                          ┌──────▼──────┐
                                          │  D1  Data   │
                                          │    Store     │
                                          └─────────────┘
```

### Level 1 (Detailed Processes)

```
                    ┌──────────────────────────────────────────────────────────────────────┐
                    │                        SYSTEM BOUNDARY                                │
                    │                                                                       │
┌──────────┐        │  ┌─────────────────┐        ┌─────────────────┐                      │
│          │ Appt   │  │  1.0             │ Appt   │  2.0             │     ┌──────────┐    │
│ Patient /├─Request──►│  Appointment     ├─Record─►│  Queue           │────►│ D2 Queue │    │
│ Parent   │        │  │  Management     │        │  Management      │◄────┤  Store   │    │
│          │◄─Status──┤│                 │        │                  │     └──────────┘    │
└──────────┘        │  │ - Book Online   │        │ - Assign Number  │                      │
                    │  │ - Select Date   │        │ - Call Patient   │    ┌──────────────┐  │
┌──────────┐        │  │ - Choose Type   │        │ - Track Status   │───►│ D6 Notif.    │  │
│          │Walk-in │  │   (New/Old/     │        │ - Broadcast      │    │   Logs       │  │
│  Nurse   ├─Register─►│    Revisit)     │        │   Updates        │    └──────────────┘  │
│          │        │  └───────┬─────────┘        └───────┬──────────┘                      │
│          │        │          │                           │                                 │
│          │        │   ┌──────▼──────┐            ┌──────▼──────┐                          │
│          │ Vitals │   │ D1 Appt.   │            │ D3 Medical  │                          │
│          ├──────────►│  Store      │            │  Records    │                          │
└──────────┘        │   └─────────────┘            └──────┬──────┘                          │
                    │                                      │                                 │
┌──────────┐        │  ┌─────────────────┐                │                                 │
│          │Examine │  │  3.0             │    Record      │    ┌─────────────────┐          │
│  Doctor  ├──────────►│  Medical         │◄───────────────┘    │  4.0             │          │
│          │        │  │  Examination    │                     │  Billing          │          │
│          │◄─History──┤│                 │──For Billing──────►│  Management      │          │
└──────────┘        │  │ - View Vitals   │                     │                  │          │
                    │  │ - Diagnose      │     ┌──────────┐    │ - Calculate Fees │          │
┌──────────┐        │  │ - Prescribe     │────►│ D4 Rx    │    │ - Apply Discount │          │
│          │Payment │  │ - Admit         │     │  Store   │    │ - Process Pay    │          │
│ Cashier  ├──────────►└─────────────────┘     └──────────┘    │                  │          │
│          │        │                                          └───────┬──────────┘          │
│          │◄─Receipt──┤                                              │                     │
└──────────┘        │                                          ┌──────▼──────┐               │
                    │                                          │ D5 Billing  │               │
┌──────────┐        │  ┌─────────────────┐                     │  Store      │               │
│          │ Manage │  │  5.0             │                     └─────────────┘               │
│  Admin   ├──────────►│  System          │                                                  │
│          │        │  │  Administration │                                                  │
│          │◄─Reports──┤│                 │                                                  │
└──────────┘        │  │ - Manage Users  │                                                  │
                    │  │ - Manage Service│                                                  │
                    │  │ - Manage Drugs  │                                                  │
                    │  │ - Manage Disc.  │                                                  │
                    │  └─────────────────┘                                                  │
                    │                                                                       │
                    └──────────────────────────────────────────────────────────────────────┘

DATA STORES:
┌──────────────┬──────────────────────────────────────────────────────────┐
│ D1           │ Appointment Store (appointments)                        │
│ D2           │ Queue Store (queues)                                    │
│ D3           │ Medical Records Store (medical_records, prescriptions)  │
│ D4           │ Prescription Store (prescriptions, hospital_drugs)      │
│ D5           │ Billing Store (billing_transactions, billing_items)     │
│ D6           │ Notification Logs (notification_logs, sms_logs)         │
└──────────────┴──────────────────────────────────────────────────────────┘
```

### Process Descriptions

| Process | Input | Output | Description |
|---------|-------|--------|-------------|
| **1.0 Appointment Management** | Appointment request, walk-in info, consultation type, visit type (new/old/revisit) | Appointment record, confirmation | Handles online booking and walk-in registration |
| **2.0 Queue Management** | Approved appointment, queue date | Queue number, real-time status, notifications | Assigns queue numbers, tracks position, broadcasts updates |
| **3.0 Medical Examination** | Patient info, vital signs, queue entry | Diagnosis, prescriptions, medical record | Doctor examines, diagnoses, and prescribes |
| **4.0 Billing Management** | Completed medical record, services, drugs | Bill, receipt, payment record | Calculates fees, applies discounts, processes payment |
| **5.0 System Administration** | Admin commands | Users, services, drugs, settings | Manages system configuration and master data |

---

## 3. Physical Data Flow Diagram

The Physical DFD shows *how* the system is implemented, including specific technologies.

```
┌──────────────────────────────────────────────────────────────────────────────────────────┐
│                              PHYSICAL SYSTEM ARCHITECTURE                                  │
│                                                                                           │
│  ┌─────────────────────────────────────────┐     ┌──────────────────────────────────────┐ │
│  │         CLIENT LAYER                     │     │          EXTERNAL SERVICES            │ │
│  │                                          │     │                                      │ │
│  │  ┌──────────────┐  ┌──────────────────┐ │     │  ┌──────────┐  ┌──────────────────┐ │ │
│  │  │ Web Browser  │  │ Flutter Mobile   │ │     │  │Semaphore │  │ Firebase Cloud   │ │ │
│  │  │ (Chrome,     │  │ App              │ │     │  │SMS API   │  │ Messaging (FCM)  │ │ │
│  │  │  Firefox,    │  │ (Android/iOS)    │ │     │  │          │  │                  │ │ │
│  │  │  Safari)     │  │                  │ │     │  └──────▲───┘  └────────▲─────────┘ │ │
│  │  └──────┬───────┘  └───────┬──────────┘ │     │         │               │           │ │
│  │         │                  │             │     └─────────┼───────────────┼───────────┘ │
│  │    HTTPS│             HTTPS│             │               │               │             │
│  │  (Livewire            (REST│             │               │               │             │
│  │   AJAX)            Sanctum)│             │               │               │             │
│  └─────────┼──────────────────┼─────────────┘               │               │             │
│            │                  │                              │               │             │
│  ┌─────────▼──────────────────▼──────────────────────────────┼───────────────┼───────────┐ │
│  │                    APPLICATION SERVER                      │               │           │ │
│  │                 (Laravel 12 + PHP 8.4)                    │               │           │ │
│  │                                                           │               │           │ │
│  │  ┌────────────────────────────────────────────────────────┼───────────────┼─────────┐ │ │
│  │  │                    MIDDLEWARE LAYER                     │               │         │ │ │
│  │  │  ┌───────────┐ ┌──────────┐ ┌────────────┐           │               │         │ │ │
│  │  │  │ Auth      │ │ Role     │ │ Personal   │           │               │         │ │ │
│  │  │  │ (Fortify  │ │ Check   │ │ Info       │           │               │         │ │ │
│  │  │  │ + Sanctum)│ │ (Spatie) │ │ Complete   │           │               │         │ │ │
│  │  │  └───────────┘ └──────────┘ └────────────┘           │               │         │ │ │
│  │  └──────────────────────────────────────────────────────────────────────────────────┘ │ │
│  │                                                           │               │           │ │
│  │  ┌────────────────────────────────────────────────────────┼───────────────┼─────────┐ │ │
│  │  │                 LIVEWIRE COMPONENTS                     │               │         │ │ │
│  │  │                                                        │               │         │ │ │
│  │  │  PATIENT MODULE        NURSE MODULE        DOCTOR MODULE               │         │ │ │
│  │  │  ┌──────────────┐     ┌──────────────┐    ┌──────────────┐            │         │ │ │
│  │  │  │BookAppointment│     │WalkInRegistr.│    │PatientQueue  │            │         │ │ │
│  │  │  │Appointments  │     │Appointments  │    │Examination   │            │         │ │ │
│  │  │  │AppointmentShow│    │AppointmentShow│   │PatientHistory│            │         │ │ │
│  │  │  │MedicalRecords│     │TodayQueue    │    │MySchedule    │            │         │ │ │
│  │  │  │ActiveQueue   │     │MedicalRecords│    │Dashboard     │            │         │ │ │
│  │  │  │Dashboard     │     │Reports       │    │Admissions    │            │         │ │ │
│  │  │  └──────────────┘     │Dashboard     │    └──────────────┘            │         │ │ │
│  │  │                       └──────────────┘                                │         │ │ │
│  │  │  CASHIER MODULE        ADMIN MODULE                                   │         │ │ │
│  │  │  ┌──────────────┐     ┌──────────────┐                               │         │ │ │
│  │  │  │BillingQueue  │     │UserManagement│                               │         │ │ │
│  │  │  │ProcessBilling│     │ServiceMgmt   │                               │         │ │ │
│  │  │  │PaymentHistory│     │DrugMgmt      │                               │         │ │ │
│  │  │  │TransactionDtl│     │DiscountMgmt  │                               │         │ │ │
│  │  │  │Dashboard     │     │Dashboard     │                               │         │ │ │
│  │  │  └──────────────┘     └──────────────┘                               │         │ │ │
│  │  └──────────────────────────────────────────────────────────────────────────────────┘ │ │
│  │                                                           │               │           │ │
│  │  ┌────────────────────────────────────────────────────────┼───────────────┼─────────┐ │ │
│  │  │              SERVICES & JOBS (Background)              │               │         │ │ │
│  │  │                                                        │               │         │ │ │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐│               │         │ │ │
│  │  │  │ SmsService   ├──┘  SendSmsJob   ├──┘  Semaphore   ├┘               │         │ │ │
│  │  │  │              │  │  (Queued)     │  │  Provider    │───► SMS API ───┘         │ │ │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘                          │ │ │
│  │  │                                                                                │ │ │
│  │  │  ┌──────────────┐  ┌──────────────┐                                            │ │ │
│  │  │  │ Firebase     │  │ SendFirebase  │                                            │ │ │
│  │  │  │ Notification ├──┤ Notif. Job   ├────────────────────────► FCM API ──────────┘ │ │
│  │  │  │ Service      │  │ (Queued)     │                                              │ │
│  │  │  └──────────────┘  └──────────────┘                                              │ │
│  │  └──────────────────────────────────────────────────────────────────────────────────┘ │ │
│  │                                                                                       │ │
│  │  ┌────────────────────────────────────────────────────────────────────────────────────┐ │
│  │  │              ELOQUENT ORM (Models & Relationships)                                  │ │
│  │  │                                                                                    │ │
│  │  │  User ─── PersonalInformation                                                     │ │
│  │  │   ├──── Appointment ──── Queue ──── MedicalRecord ──── Prescription                │ │
│  │  │   ├──── DoctorSchedule                         └──── BillingTransaction            │ │
│  │  │   ├──── Admission                                        └──── BillingItem         │ │
│  │  │   └──── UserDevice                                                                 │ │
│  │  │                                                                                    │ │
│  │  │  ConsultationType ──── Service ──── ServiceCategory                                │ │
│  │  │  HospitalDrug          Discount     SystemSetting                                  │ │
│  │  └────────────────────────────────────────────────────────────────────────────────────┘ │
│  │                              │                                                         │
│  └──────────────────────────────┼─────────────────────────────────────────────────────────┘ │
│                                 │                                                          │
│  ┌──────────────────────────────▼──────────────────────┐    ┌────────────────────────────┐ │
│  │              DATABASE LAYER                          │    │    BROADCAST SERVER         │ │
│  │                                                      │    │                            │ │
│  │  DEV:  SQLite (database/database.sqlite)            │    │  Laravel Reverb            │ │
│  │  PROD: MySQL 8.0 (hqms database)                   │    │  (WebSocket ws://          │ │
│  │                                                      │    │   or wss://)              │ │
│  │  35 Tables:                                          │    │                            │ │
│  │  - Core: users, personal_information, appointments, │    │  Channels:                 │ │
│  │    queues, medical_records, prescriptions,           │    │  - queue.display.{type}    │ │
│  │    billing_transactions, billing_items,              │    │  - queue.display.all       │ │
│  │    consultation_types, doctor_schedules,             │    │  - queue.staff (private)   │ │
│  │    admissions, services, service_categories,         │    │  - queue.patient.{id}      │ │
│  │    hospital_drugs, discounts                         │    │    (private)               │ │
│  │  - Supporting: system_settings, queue_displays,     │    │                            │ │
│  │    user_devices, notification_logs, sms_logs        │    │  Event: QueueUpdated       │ │
│  │  - Auth: sessions, personal_access_tokens,          │    │  (ShouldBroadcastNow)      │ │
│  │    password_reset_tokens                             │    │                            │ │
│  │  - Jobs: jobs, job_batches, failed_jobs             │    └────────────────────────────┘ │
│  │  - Spatie: roles, permissions, model_has_roles,     │                                   │
│  │    model_has_permissions, role_has_permissions       │    ┌────────────────────────────┐ │
│  │  - Cache: cache, cache_locks                        │    │    FRONTEND BUILD           │ │
│  │  - Notifications: notifications                      │    │                            │ │
│  └──────────────────────────────────────────────────────┘    │  Vite + Tailwind CSS 4    │ │
│                                                              │  Laravel Echo 2            │ │
│                                                              │  Flux UI 2.9 (Free)       │ │
│                                                              └────────────────────────────┘ │
│                                                                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

### Technology Mapping

| Logical Process | Physical Implementation |
|-----------------|------------------------|
| **1.0 Appointment Management** | `BookAppointment.php`, `WalkInRegistration.php` (Livewire Components) |
| **2.0 Queue Management** | `TodayQueue.php`, `ActiveQueue.php`, `QueueMonitor.php` (Livewire + Reverb WebSocket) |
| **3.0 Medical Examination** | `Examination.php`, `PatientQueue.php` (Livewire Components) |
| **4.0 Billing Management** | `ProcessBilling.php`, `BillingQueue.php` (Livewire Components) |
| **5.0 System Administration** | `UserManagement.php`, `ServiceManagement.php`, `HospitalDrugManagement.php`, `DiscountManagement.php` (Livewire) |
| **Notifications** | `SmsService` + `SendSmsJob` (Semaphore API), `FirebaseNotificationService` + `SendFirebaseNotificationJob` (FCM) |
| **Real-time Updates** | `QueueUpdated` Event + Laravel Reverb (WebSocket) + Laravel Echo 2 (JS Client) |
| **Authentication** | Laravel Fortify (Web), Laravel Sanctum (API/Mobile) |
| **Authorization** | Spatie Laravel Permission (Role-based middleware) |
| **Data Access** | Eloquent ORM with eager loading |
| **Data Store** | SQLite (dev) / MySQL 8.0 (prod) |
| **Frontend Rendering** | Livewire 4.0 + Blade Templates + Flux UI 2.9 + Tailwind CSS 4.0 |
| **Asset Bundling** | Vite |

---

## 4. Entity Relationship Diagram

### Full ERD

```
┌──────────────────────────┐          ┌──────────────────────────┐
│         users            │          │   personal_information   │
├──────────────────────────┤          ├──────────────────────────┤
│ PK  id                   │──── 1:1 ──►│ PK  id                │
│     first_name           │          │ FK  user_id              │
│     middle_name          │          │     first_name           │
│     last_name            │          │     middle_name          │
│     email (unique)       │          │     last_name            │
│     phone                │          │     date_of_birth        │
│     password             │          │     gender               │
│     email_verified_at    │          │     phone                │
│     two_factor_secret    │          │     province             │
│     two_factor_recovery  │          │     municipality         │
│     is_active            │          │     barangay             │
│     deleted_at           │          │     street               │
│     created_at           │          │     occupation           │
│     updated_at           │          │     marital_status       │
└──────────┬───────────────┘          │     emergency_contact_*  │
           │                          └──────────────────────────┘
           │
           │              ┌─────────────────────────────────────────────────────────┐
           │              │                                                         │
           │ 1:N          │ 1:N                    1:N                 1:N          │
           │              │                         │                   │           │
    ┌──────▼──────┐  ┌────▼─────────────────┐ ┌────▼──────────┐ ┌─────▼─────────┐ │
    │appointments │  │  doctor_schedules    │ │  user_devices │ │  admissions   │ │
    ├─────────────┤  ├──────────────────────┤ ├───────────────┤ ├───────────────┤ │
    │PK id        │  │PK id                │ │PK id          │ │PK id          │ │
    │FK user_id ──┘  │FK user_id (doctor)──┘ │FK user_id  ───┘ │FK user_id  ───┘
    │FK consult.     │FK consultation_type_id │   device_id    │FK medical_record_id
    │   _type_id     │   schedule_type       │   device_model │FK admitted_by
    │FK doctor_id    │   day_of_week         │   platform     │   admission_number
    │FK approved_by  │   date                │   app_version  │   room_number
    │   patient_*    │   start_time          │   fcm_token    │   bed_number
    │   (inline info)│   end_time            │   is_active    │   admitted_at
    │   appointment_ │   max_patients        │   last_used_at │   discharged_at
    │     date       │   is_available        └───────────────┘│   status
    │   appointment_ │   reason              │                 │   notes
    │     time       └──────────────────────┘                 └───────────────┘
    │   chief_complaints
    │   visit_type                    ┌───────────────────────────────────┐
    │   status                        │      consultation_types          │
    │   source                        ├───────────────────────────────────┤
    │   decline_reason                │ PK  id                           │
    │   suggested_date                │     code                         │
    │   notes                         │     name                         │
    │   cancellation_reason           │     short_name                   │
    └──────┬──────────────────────────┤     description                  │
           │                          │     base_consultation_fee        │
           │ 1:1                      │     avg_duration_minutes         │
           │                          │     is_active                    │
    ┌──────▼──────┐                   └───────────────┬─────────────────┘
    │   queues    │                                    │
    ├─────────────┤                                    │ M:N (pivot: doctor_consultation_types)
    │PK id        │                                    │
    │FK appt_id   │                            ┌───────▼──────────────────────┐
    │FK user_id   │                            │  doctor_consultation_types   │
    │FK consult.  │                            ├──────────────────────────────┤
    │   _type_id  │                            │ FK  user_id (doctor)         │
    │FK doctor_id │                            │ FK  consultation_type_id     │
    │FK served_by │                            └──────────────────────────────┘
    │   queue_number
    │   queue_date
    │   session_number
    │   estimated_time
    │   priority
    │   status
    │   source
    │   called_at
    │   serving_started_at
    │   serving_ended_at
    └──────┬──────┘
           │
           │ 1:1
           │
    ┌──────▼──────────────────┐
    │    medical_records      │
    ├─────────────────────────┤
    │PK  id                   │
    │    record_number (uniq) │
    │FK  user_id              │
    │FK  consultation_type_id │
    │FK  appointment_id       │
    │FK  queue_id             │
    │FK  doctor_id            │
    │FK  nurse_id             │
    │    patient_* (inline)   │
    │    visit_date           │
    │    visit_type           │──── enum: new, old, revisit
    │    service_type         │
    │    ob_type              │
    │    chief_complaints_*   │
    │    ── Vital Signs ──    │
    │    temperature          │
    │    blood_pressure       │
    │    cardiac_rate         │
    │    respiratory_rate     │
    │    weight, height       │
    │    ── Diagnosis ──      │
    │    pertinent_hpi_pe     │
    │    diagnosis            │
    │    plan                 │
    │    procedures_done      │
    │    ── Billing Hints ──  │
    │    suggested_discount_* │
    │    status               │
    └──────┬──────────────────┘
           │
           ├──── 1:N ────┐
           │              │
    ┌──────▼──────┐  ┌────▼────────────────────┐
    │prescriptions│  │ billing_transactions     │
    ├─────────────┤  ├─────────────────────────┤
    │PK id        │  │PK  id                   │
    │FK medical_  │  │    transaction_number    │
    │  record_id  │  │FK  medical_record_id    │
    │FK prescribed│  │FK  user_id              │
    │  _by        │  │FK  processed_by         │
    │FK hospital_ │  │FK  discount_approved_by │
    │  drug_id    │  │    is_emergency          │
    │  medication │  │    emergency_fee         │
    │  _name      │  │    subtotal              │
    │  dosage     │  │    discount_type         │
    │  frequency  │  │    discount_amount       │
    │  duration   │  │    total_amount          │
    │  quantity   │  │    amount_paid           │
    │  unit       │  │    balance               │
    │  instruct.  │  │    payment_method        │
    │  is_hospital│  │    payment_status        │
    │  _drug      │  │    paid_at               │
    └─────────────┘  └──────────┬──────────────┘
                                │
                                │ 1:N
                                │
                         ┌──────▼──────────┐
                         │  billing_items   │
                         ├─────────────────┤
                         │PK  id           │
                         │FK  billing_     │
                         │    transaction_id│
                         │FK  service_id   │──── optional
                         │FK  hospital_    │
                         │    drug_id      │──── optional
                         │    item_type    │
                         │    item_descr.  │
                         │    quantity     │
                         │    unit_price   │
                         │    total_price  │
                         └────────────────┘


  ┌───────────────────────┐    ┌───────────────────────┐    ┌───────────────────────┐
  │    hospital_drugs     │    │      services         │    │    discounts          │
  ├───────────────────────┤    ├───────────────────────┤    ├───────────────────────┤
  │PK  id                 │    │PK  id                 │    │PK  id                 │
  │    name               │    │FK  service_category_id│    │    name               │
  │    generic_name       │    │    service_name       │    │    code               │
  │    category           │    │    description        │    │    percentage         │
  │    unit               │    │    base_price         │    │    description        │
  │    unit_price         │    │    is_active          │    │    is_active          │
  │    stock_quantity     │    │    display_order      │    │    sort_order         │
  │    reorder_level      │    └───────────┬───────────┘    └───────────────────────┘
  │    is_active          │                │
  └───────────────────────┘         ┌──────▼───────────────┐
                                    │  service_categories  │
  ┌───────────────────────┐         ├──────────────────────┤
  │   system_settings     │         │PK  id                │
  ├───────────────────────┤         │    name               │
  │PK  id                 │         │    code               │
  │    key (unique)       │         │    description        │
  │    value              │         │    icon               │
  └───────────────────────┘         │    is_active          │
                                    │    sort_order         │
  ┌───────────────────────┐         └──────────────────────┘
  │   queue_displays      │
  ├───────────────────────┤
  │PK  id                 │
  │    display_name       │
  │    location           │
  │    display_settings   │
  │    is_active          │
  └───────────────────────┘


  ┌───────────────────────┐    ┌───────────────────────┐
  │  notification_logs    │    │      sms_logs         │
  ├───────────────────────┤    ├───────────────────────┤
  │PK  id                 │    │PK  id                 │
  │FK  user_id            │    │FK  user_id            │
  │FK  user_device_id     │    │FK  sender_id          │
  │    type               │    │    phone_number       │
  │    title              │    │    message            │
  │    body               │    │    status             │
  │    data (JSON)        │    │    message_id         │
  │    status             │    │    attempts           │
  │    sent_at            │    │    error_message      │
  │    read_at            │    │    api_response (JSON)│
  │    error_message      │    │    sent_at            │
  └───────────────────────┘    │    context            │
                               └───────────────────────┘
```

### Relationship Summary

| Relationship | Type | Description |
|-------------|------|-------------|
| `users` → `personal_information` | 1:1 | Account owner's profile |
| `users` → `appointments` | 1:N | Patient books appointments |
| `users` → `queues` | 1:N | Patient queue entries |
| `users` → `medical_records` | 1:N | Patient visit records |
| `users` → `doctor_schedules` | 1:N | Doctor's schedule |
| `users` → `billing_transactions` | 1:N | Patient's bills |
| `users` → `admissions` | 1:N | Patient's hospital admissions |
| `users` → `user_devices` | 1:N | Mobile devices for push notifications |
| `users` ↔ `consultation_types` | M:N | Doctors' specializations (pivot) |
| `appointments` → `queues` | 1:1 | Appointment links to queue entry |
| `appointments` → `medical_records` | 1:1 | Appointment links to visit record |
| `queues` → `medical_records` | 1:1 | Queue links to visit record |
| `medical_records` → `prescriptions` | 1:N | Prescriptions per visit |
| `medical_records` → `billing_transactions` | 1:1 | Bill per visit |
| `medical_records` → `admissions` | 1:1 | Admission per visit (if applicable) |
| `billing_transactions` → `billing_items` | 1:N | Line items per bill |
| `services` → `service_categories` | N:1 | Services grouped by category |
| `consultation_types` → `appointments` | 1:N | Service type per appointment |
| `consultation_types` → `queues` | 1:N | Service type per queue entry |
| `consultation_types` → `medical_records` | 1:N | Service type per record |
| `consultation_types` → `doctor_schedules` | 1:N | Schedule per service type |

### Cardinality Notation

```
──── 1:1 ────    One-to-One
──── 1:N ────    One-to-Many
──── M:N ────    Many-to-Many (with pivot table)
FK               Foreign Key
PK               Primary Key
```

---

## Status Flow Diagrams

### Appointment Status Flow

```
    ┌─────────┐
    │ pending  │
    └────┬─────┘
         │
    ┌────▼─────┐     ┌───────────┐
    │ approved  ├────►│ no_show    │
    └────┬─────┘     └───────────┘
         │
    ┌────▼──────┐
    │checked_in │
    └────┬──────┘
         │
    ┌────▼───────┐
    │in_progress │
    └────┬───────┘
         │
    ┌────▼──────┐
    │ completed │
    └───────────┘

    * cancelled can occur from: pending, approved, checked_in
```

### Queue Status Flow

```
    ┌─────────┐
    │ waiting  │
    └────┬─────┘
         │
    ┌────▼────┐     ┌─────────┐
    │ called  ├────►│ skipped │
    └────┬────┘     └─────────┘
         │
    ┌────▼─────┐
    │ serving  │
    └────┬─────┘
         │
    ┌────▼──────┐
    │ completed │
    └───────────┘

    * cancelled can occur from: waiting, called
```

### Medical Record Status Flow

```
    ┌─────────────┐
    │ in_progress  │
    └──────┬──────┘
           │
     ┌─────▼──────┐
     │ for_billing │
     └─────┬──────┘
           │
     ┌─────▼──────┐     ┌───────────────┐
     │ completed  │     │ for_admission │ (alternative path from in_progress)
     └────────────┘     └───────────────┘
```

### Billing Status Flow

```
    ┌─────────┐
    │ pending  │
    └────┬─────┘
         │
    ┌────▼────┐     ┌───────────┐
    │ partial ├────►│   paid    │
    └─────────┘     └───────────┘

    * cancelled can occur from: pending
```
