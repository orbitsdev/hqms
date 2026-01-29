# CareTime - Hospital Queue Management System

## Project Documentation

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Features](#2-features)
3. [Technology Stack](#3-technology-stack)
4. [System Requirements](#4-system-requirements)
5. [User Roles](#5-user-roles)
6. [System Modules](#6-system-modules)
7. [Installation](#7-installation)
8. [Configuration](#8-configuration)
9. [Project Structure](#9-project-structure)
10. [Documentation Index](#10-documentation-index)
11. [Screenshots](#11-screenshots)
12. [License](#12-license)

---

## 1. Project Overview

### 1.1 Introduction

**CareTime** is a comprehensive Hospital Queue Management System developed for **Guardiano Maternity and Children Clinic and Hospital**. The system digitizes and streamlines the entire patient journey from appointment booking to payment processing.

### 1.2 Problem Statement

Traditional hospital queue management faces several challenges:
- Long waiting times with no visibility
- Manual paper-based records prone to errors
- Difficulty tracking patient flow
- No real-time communication with patients
- Inefficient billing processes
- Limited access to medical history

### 1.3 Solution

CareTime addresses these challenges by providing:
- **Online Appointment Booking** - Patients book from anywhere, anytime
- **Real-Time Queue Tracking** - Live updates on queue position via web/mobile
- **Digital Medical Records** - Complete visit history accessible instantly
- **Streamlined Workflow** - Seamless handoff between nurse, doctor, and cashier
- **Automated Notifications** - SMS/push notifications at every step
- **Integrated Billing** - Automatic fee calculation with discount support

### 1.4 Target Users

| User Type | Description |
|-----------|-------------|
| **Patients/Parents** | Book appointments, track queue, view medical records |
| **Nurses** | Manage appointments, triage patients, record vitals |
| **Doctors** | Examine patients, diagnose, prescribe medications |
| **Cashiers** | Process billing, receive payments, issue receipts |
| **Administrators** | Manage users, services, drugs, system settings |

### 1.5 Key Benefits

- **For Patients**: Reduced waiting time, transparency, convenient booking
- **For Staff**: Organized workflow, reduced paperwork, better coordination
- **For Management**: Real-time analytics, audit trails, efficient operations

---

## 2. Features

### 2.1 Patient Portal

| Feature | Description |
|---------|-------------|
| Online Registration | Create account with email verification |
| Profile Management | Complete personal and emergency contact information |
| Appointment Booking | Book for self or dependents (children, spouse, parents) |
| Queue Tracking | Real-time position updates with estimated wait time |
| Medical Records | View complete visit history, diagnoses, prescriptions |
| Notifications | Receive updates on appointment status and queue calls |

### 2.2 Nurse Station

| Feature | Description |
|---------|-------------|
| Appointment Management | Approve, decline, or reschedule appointment requests |
| Walk-In Registration | Register patients who arrive without appointments |
| Check-In System | Check in patients and generate queue numbers |
| Triage | Record vital signs and update chief complaints |
| Queue Management | Call patients, manage queue flow, handle skips |
| Doctor Assignment | Forward patients to available doctors |

### 2.3 Doctor Station

| Feature | Description |
|---------|-------------|
| Patient Queue | View assigned patients waiting for examination |
| Medical Examination | Record history, physical exam findings |
| Diagnosis Entry | Document diagnosis and treatment plan |
| Prescription Management | Prescribe hospital drugs or external medications |
| Schedule Management | Set regular availability and exceptions |
| Patient History | Access previous visit records |

### 2.4 Cashier Desk

| Feature | Description |
|---------|-------------|
| Billing Queue | View patients ready for payment |
| Invoice Generation | Automatic calculation of fees and drugs |
| Discount Application | Support for Senior, PWD, Employee discounts |
| Payment Processing | Multiple payment methods (Cash, GCash, Card) |
| Receipt Generation | Print or digital receipts |
| Payment History | View and search past transactions |

### 2.5 Admin Console

| Feature | Description |
|---------|-------------|
| User Management | Create, edit, deactivate user accounts |
| Role Assignment | Assign roles (patient, nurse, doctor, cashier, admin) |
| Service Management | Configure billable services and fees |
| Drug Inventory | Manage hospital drug catalog and pricing |
| Consultation Types | Configure service categories (Pediatrics, OB, etc.) |
| System Settings | Configure booking rules, clinic hours |

### 2.6 Real-Time Features

| Feature | Technology |
|---------|------------|
| Queue Updates | Laravel Reverb WebSockets |
| Live Notifications | Browser push + in-app notifications |
| TV Display | Queue number display for waiting areas |
| Status Broadcasting | Instant updates across all connected devices |

---

## 3. Technology Stack

### 3.1 Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.4+ | Server-side programming language |
| Laravel | 12 | PHP web application framework |
| Laravel Fortify | 1.x | Authentication backend |
| Laravel Sanctum | 4.x | API authentication (for mobile app) |
| Laravel Reverb | 1.x | WebSocket server for real-time features |
| Spatie Permission | 6.x | Role-based access control |

### 3.2 Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Livewire | 4.0 | Full-stack reactive components |
| Flux UI | 2.9 | UI component library |
| Tailwind CSS | 4.0 | Utility-first CSS framework |
| Alpine.js | 3.x | Lightweight JavaScript framework |
| Vite | 6.x | Frontend build tool |

### 3.3 Database

| Environment | Database |
|-------------|----------|
| Development | SQLite |
| Production | MySQL 8.0+ / PostgreSQL 15+ |

### 3.4 Infrastructure (Production)

| Component | Technology |
|-----------|------------|
| Web Server | Nginx |
| Process Manager | Supervisor |
| Queue Worker | Laravel Queue (database driver) |
| WebSocket Server | Laravel Reverb |
| SSL | Let's Encrypt / Certbot |

### 3.5 Development Tools

| Tool | Purpose |
|------|---------|
| Composer | PHP dependency management |
| NPM | JavaScript dependency management |
| Laravel Pint | Code formatting (PSR-12) |
| Pest PHP | Testing framework |
| Git | Version control |

---

## 4. System Requirements

### 4.1 Server Requirements (Production)

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| CPU | 1 vCPU | 2+ vCPU |
| RAM | 2 GB | 4+ GB |
| Storage | 25 GB SSD | 50+ GB SSD |
| OS | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |

### 4.2 Software Requirements

| Software | Version |
|----------|---------|
| PHP | 8.2 or higher |
| Composer | 2.x |
| Node.js | 18.x or higher |
| NPM | 9.x or higher |
| MySQL | 8.0+ (production) |
| Nginx | 1.18+ |
| Supervisor | 4.x |

### 4.3 PHP Extensions Required

```
BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring,
OpenSSL, PCRE, PDO, PDO_MySQL, PDO_SQLite, Tokenizer, XML, Zip
```

### 4.4 Browser Support

| Browser | Version |
|---------|---------|
| Chrome | 90+ |
| Firefox | 90+ |
| Safari | 14+ |
| Edge | 90+ |

---

## 5. User Roles

### 5.1 Role Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│                         ADMIN                                │
│  Full system access, user management, configuration         │
└─────────────────────────────────────────────────────────────┘
          │
          ├──────────────────────────────────────────┐
          │                                          │
          ▼                                          ▼
┌─────────────────────┐                  ┌─────────────────────┐
│       NURSE         │                  │       DOCTOR        │
│ Appointments, Queue │                  │ Examine, Prescribe  │
│ Triage, Vitals      │                  │ Diagnose            │
└─────────────────────┘                  └─────────────────────┘
          │
          ▼
┌─────────────────────┐                  ┌─────────────────────┐
│      CASHIER        │                  │      PATIENT        │
│ Billing, Payments   │                  │ Book, View Records  │
└─────────────────────┘                  └─────────────────────┘
```

### 5.2 Role Permissions

| Permission | Patient | Nurse | Doctor | Cashier | Admin |
|------------|:-------:|:-----:|:------:|:-------:|:-----:|
| Book appointments | ✓ | ✓ | - | - | ✓ |
| View own records | ✓ | - | - | - | - |
| Manage appointments | - | ✓ | - | - | ✓ |
| Check-in patients | - | ✓ | - | - | - |
| Record vitals | - | ✓ | - | - | - |
| Examine patients | - | - | ✓ | - | - |
| Write prescriptions | - | - | ✓ | - | - |
| Process billing | - | - | - | ✓ | - |
| Manage users | - | - | - | - | ✓ |
| System settings | - | - | - | - | ✓ |

---

## 6. System Modules

### 6.1 Module Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        CARETIME SYSTEM                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │   PATIENT    │  │    NURSE     │  │   DOCTOR     │          │
│  │    PORTAL    │  │   STATION    │  │   STATION    │          │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤          │
│  │ • Dashboard  │  │ • Dashboard  │  │ • Dashboard  │          │
│  │ • Book Appt  │  │ • Appointments│ │ • Queue      │          │
│  │ • Queue View │  │ • Today Queue│  │ • Examine    │          │
│  │ • Records    │  │ • Triage     │  │ • Prescribe  │          │
│  │ • Profile    │  │ • Check-in   │  │ • Schedule   │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐                             │
│  │   CASHIER    │  │    ADMIN     │                             │
│  │     DESK     │  │   CONSOLE    │                             │
│  ├──────────────┤  ├──────────────┤                             │
│  │ • Dashboard  │  │ • Dashboard  │                             │
│  │ • Bill Queue │  │ • Users      │                             │
│  │ • Process    │  │ • Services   │                             │
│  │ • History    │  │ • Drugs      │                             │
│  │ • Reports    │  │ • Settings   │                             │
│  └──────────────┘  └──────────────┘                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Data Flow

```
Patient Books  →  Nurse Approves  →  Patient Checks In  →  Nurse Triages
     │                  │                    │                   │
     ▼                  ▼                    ▼                   ▼
appointments      appointments           queues            medical_records
(created)         (approved)            (created)            (created)
                                                                 │
                                                                 ▼
           Cashier Processes  ←  Doctor Examines  ←  Nurse Forwards
                  │                     │                   │
                  ▼                     ▼                   ▼
        billing_transactions      prescriptions          queues
             (created)              (created)           (updated)
```

---

## 7. Installation

### 7.1 Quick Start (Development)

```bash
# Clone repository
git clone https://github.com/your-repo/hqms.git
cd hqms

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate --seed

# Start development server
composer dev
```

### 7.2 Default Accounts (After Seeding)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@caretime.local | password |
| Nurse | nurse@caretime.local | password |
| Doctor | doctor@caretime.local | password |
| Cashier | cashier@caretime.local | password |
| Patient | patient@caretime.local | password |

### 7.3 Available Commands

```bash
# Full project setup
composer setup

# Development server (Laravel + Vite + Queue)
composer dev

# Code formatting
composer lint

# Run tests
composer test

# Run specific tests
./vendor/bin/pest tests/Feature/Auth
```

### 7.4 Production Deployment

For production deployment, refer to:
- [Production Setup Guide](../productionsetup/README.md)

---

## 8. Configuration

### 8.1 Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | CareTime |
| `APP_ENV` | Environment | local/production |
| `APP_DEBUG` | Debug mode | true/false |
| `APP_URL` | Application URL | https://caretime.example.com |
| `DB_CONNECTION` | Database driver | mysql/sqlite |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_DATABASE` | Database name | hqms |
| `REVERB_APP_ID` | WebSocket app ID | caretime |
| `REVERB_HOST` | WebSocket host | localhost |
| `REVERB_PORT` | WebSocket port | 8080 |

### 8.2 System Settings

Configurable via Admin Console:

| Setting | Default | Description |
|---------|---------|-------------|
| `max_advance_booking_days` | 30 | How far ahead patients can book |
| `allow_same_day_booking` | true | Allow booking for current day |
| `clinic_open_time` | 08:00 | Clinic opening time |
| `clinic_close_time` | 17:00 | Clinic closing time |

### 8.3 Consultation Types

Pre-configured services:

| Name | Code | Short Name | Base Fee |
|------|------|------------|----------|
| Pediatrics | pedia | PED | ₱500.00 |
| Obstetrics | ob | OB | ₱600.00 |
| General Medicine | gen | GEN | ₱450.00 |

---

## 9. Project Structure

### 9.1 Directory Overview

```
hqms/
├── app/
│   ├── Actions/              # Fortify authentication actions
│   ├── Concerns/             # Shared traits for validation
│   ├── Livewire/             # Livewire components by module
│   │   ├── Admin/            # Admin module components
│   │   ├── Auth/             # Authentication components
│   │   ├── Cashier/          # Cashier module components
│   │   ├── Doctor/           # Doctor module components
│   │   ├── Nurse/            # Nurse module components
│   │   ├── Patient/          # Patient module components
│   │   └── Settings/         # User settings components
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Notification classes
│   ├── Providers/            # Service providers
│   └── Traits/               # Model traits
│       └── Models/           # Relationship traits
├── bootstrap/
│   ├── app.php               # Application bootstrap
│   └── providers.php         # Service provider registration
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── documents/
│   ├── project/              # Project documentation
│   └── productionsetup/      # Production deployment guides
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
│       ├── layouts/          # Layout templates
│       └── livewire/         # Livewire component views
├── routes/
│   ├── web.php               # Web routes
│   ├── api.php               # API routes
│   └── channels.php          # Broadcast channels
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── .env.example              # Environment template
├── composer.json             # PHP dependencies
├── package.json              # Node dependencies
└── vite.config.js            # Vite configuration
```

### 9.2 Key Files

| File | Purpose |
|------|---------|
| `app/Models/User.php` | User model with roles and relationships |
| `app/Models/Appointment.php` | Appointment model |
| `app/Models/Queue.php` | Queue management model |
| `app/Models/MedicalRecord.php` | Medical records model |
| `app/Models/BillingTransaction.php` | Billing model |
| `routes/web.php` | Main application routes |
| `bootstrap/app.php` | Middleware and exception handling |

### 9.3 Model Traits Architecture

```
app/Traits/Models/
├── User/
│   ├── HasAppointments.php       # User → Appointments
│   ├── HasMedicalRecords.php     # User → Medical Records
│   ├── HasQueues.php             # User → Queues
│   └── HasConsultationTypes.php  # Doctor → Specializations
├── Appointment/
│   ├── HasQueue.php              # Appointment → Queue
│   └── HasMedicalRecord.php      # Appointment → Record
├── Queue/
│   ├── BelongsToAppointment.php
│   └── HasMedicalRecord.php
└── MedicalRecord/
    ├── HasPrescriptions.php
    └── HasBillingTransaction.php
```

---

## 10. Documentation Index

### 10.1 Technical Documentation

| Document | Description |
|----------|-------------|
| [TECHNICAL.md](./TECHNICAL.md) | Complete technical documentation |
| [DATABASE.md](./DATABASE.md) | Database schema and relationships |

### 10.2 Logic Documentation

| Document | Description |
|----------|-------------|
| [LOGIC-OVERVIEW.md](./LOGIC-OVERVIEW.md) | System-wide data flow and concepts |
| [LOGIC-PATIENT.md](./LOGIC-PATIENT.md) | Patient module logic |
| [LOGIC-NURSE.md](./LOGIC-NURSE.md) | Nurse module logic |
| [LOGIC-DOCTOR.md](./LOGIC-DOCTOR.md) | Doctor module logic |
| [LOGIC-CASHIER.md](./LOGIC-CASHIER.md) | Cashier module logic |
| [LOGIC-ADMIN.md](./LOGIC-ADMIN.md) | Admin module logic |

### 10.3 User Documentation

| Document | Description |
|----------|-------------|
| [CLIENT-GUIDE.md](./CLIENT-GUIDE.md) | Real-world scenario walkthrough |

### 10.4 Deployment Documentation

| Document | Description |
|----------|-------------|
| [Production Setup](../productionsetup/README.md) | Production deployment overview |
| [Server Setup](../productionsetup/01-SERVER-SETUP.md) | DigitalOcean server setup |
| [Nginx Config](../productionsetup/02-NGINX-CONFIG.md) | Web server configuration |
| [Laravel Deploy](../productionsetup/03-LARAVEL-DEPLOYMENT.md) | Application deployment |
| [Supervisor](../productionsetup/04-SUPERVISOR.md) | Queue worker setup |
| [Environment](../productionsetup/05-ENV-PRODUCTION.md) | Production environment |
| [SSL Setup](../productionsetup/06-SSL-SETUP.md) | HTTPS configuration |
| [Maintenance](../productionsetup/07-MAINTENANCE.md) | Maintenance commands |

---

## 11. Screenshots

### 11.1 Patient Portal

```
┌─────────────────────────────────────────────────────────────────┐
│  PATIENT DASHBOARD                                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Welcome, Maria!                                                 │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │ 📅          │  │ 🏥          │  │ 📋          │             │
│  │ Appointments│  │ Queue Status│  │ Records     │             │
│  │ 2 upcoming  │  │ PED-004     │  │ 5 visits    │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
│  [Book New Appointment]                                          │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 11.2 Nurse Station

```
┌─────────────────────────────────────────────────────────────────┐
│  TODAY'S QUEUE - January 30, 2026                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  PEDIATRICS        OBSTETRICS        GENERAL                    │
│  ──────────        ──────────        ───────                    │
│  Waiting: 5        Waiting: 3        Waiting: 2                 │
│  Serving: 1        Serving: 1        Serving: 0                 │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ PED-004 │ Juan Jr. Santos │ 5 yrs │ Waiting │ [Call]   │    │
│  │ PED-005 │ Anna Garcia     │ 3 yrs │ Waiting │ [Call]   │    │
│  │ PED-006 │ Pedro Reyes     │ 7 yrs │ Waiting │ [Call]   │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 11.3 Doctor Station

```
┌─────────────────────────────────────────────────────────────────┐
│  MY PATIENTS - Dr. Elena Cruz (Pediatrics)                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  WAITING FOR EXAMINATION (2):                                   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ PED-003 │ Anna Garcia │ Cough, cold                     │    │
│  │ Vitals: T:37.5 BP:90/60 HR:88                           │    │
│  │ [Start Examination]                                      │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ PED-004 │ Juan Jr. Santos │ Fever for 2 days            │    │
│  │ Vitals: T:38.2 BP:100/70 HR:95                          │    │
│  │ [Start Examination]                                      │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 11.4 Cashier Desk

```
┌─────────────────────────────────────────────────────────────────┐
│  PROCESS BILLING - MR-2026-00089                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Patient: Juan Jr. Santos                                        │
│  Service: Pediatrics | Doctor: Dr. Cruz                         │
│                                                                  │
│  ITEMS                                              AMOUNT       │
│  ─────────────────────────────────────────────────────────────  │
│  Professional Fee - Pediatrics                     ₱ 500.00     │
│  Paracetamol Syrup 250mg/5ml × 1                  ₱  85.00     │
│  Ascorbic Acid 100mg Syrup × 1                    ₱  65.00     │
│  ─────────────────────────────────────────────────────────────  │
│                                          TOTAL:    ₱ 650.00     │
│                                                                  │
│  [Process Payment]                                               │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 12. License

This project is developed as a thesis/capstone project for **Guardiano Maternity and Children Clinic and Hospital**.

### 12.1 Project Information

| Field | Value |
|-------|-------|
| Project Name | CareTime - Hospital Queue Management System |
| Client | Guardiano Maternity and Children Clinic and Hospital |
| Project Type | Thesis / Capstone Project |
| Year | 2026 |

### 12.2 Technologies Used (Open Source)

- Laravel - MIT License
- Livewire - MIT License
- Tailwind CSS - MIT License
- Flux UI - Commercial License
- Spatie Permission - MIT License

---

## Contact & Support

For technical support or inquiries about this system, please contact the development team.

---

*Documentation last updated: January 2026*
