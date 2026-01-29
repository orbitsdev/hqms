<p align="center">
  <img src="public/images/logo.png" alt="CareTime Logo" width="120" height="120">
</p>

<h1 align="center">CareTime</h1>

<p align="center">
  <strong>Hospital Queue Management System</strong><br>
  A modern, real-time queue management solution for healthcare facilities
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#installation">Installation</a> •
  <a href="#usage">Usage</a> •
  <a href="#documentation">Documentation</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-4.0-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 4">
  <img src="https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/PHP-8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
</p>

---

## 📋 Overview

**CareTime** is a comprehensive Hospital Queue Management System developed for **Guardiano Maternity and Children Clinic and Hospital**. It digitizes the entire patient journey from appointment booking to payment processing with real-time queue tracking.

### The Problem

Traditional hospital queue management faces challenges:
- ⏰ Long waiting times with no visibility
- 📝 Manual paper-based records prone to errors
- 🔄 Difficulty tracking patient flow
- 📵 No real-time communication with patients

### The Solution

CareTime provides:
- ✅ **Online Appointment Booking** - Book from anywhere, anytime
- ✅ **Real-Time Queue Tracking** - Live updates on queue position
- ✅ **Digital Medical Records** - Complete visit history accessible instantly
- ✅ **Streamlined Workflow** - Seamless handoff between staff
- ✅ **Integrated Billing** - Automatic fee calculation with discounts

---

## 🎯 Features

### 👨‍👩‍👧 Patient Portal
| Feature | Description |
|---------|-------------|
| 📅 Online Booking | Book appointments for self or dependents |
| 📍 Queue Tracking | Real-time position updates with estimated wait |
| 📋 Medical Records | View complete visit history and prescriptions |
| 🔔 Notifications | Instant alerts on appointment and queue status |

### 👩‍⚕️ Nurse Station
| Feature | Description |
|---------|-------------|
| ✅ Appointment Management | Approve, decline, or reschedule requests |
| 🚶 Walk-In Registration | Quick registration for walk-in patients |
| 📊 Queue Management | Call patients, manage flow, handle skips |
| 🩺 Triage | Record vital signs and chief complaints |

### 👨‍⚕️ Doctor Station
| Feature | Description |
|---------|-------------|
| 👥 Patient Queue | View assigned patients waiting for examination |
| 📝 Medical Examination | Record history and physical exam findings |
| 💊 Prescriptions | Prescribe hospital drugs or external medications |
| 📆 Schedule Management | Set regular availability and exceptions |

### 💰 Cashier Desk
| Feature | Description |
|---------|-------------|
| 🧾 Billing Queue | View patients ready for payment |
| 💵 Payment Processing | Multiple methods (Cash, GCash, Card) |
| 🏷️ Discounts | Senior, PWD, Employee discount support |
| 🖨️ Receipt Generation | Print or digital receipts |

### ⚙️ Admin Console
| Feature | Description |
|---------|-------------|
| 👤 User Management | Create and manage all user accounts |
| 🏥 Services | Configure billable services and fees |
| 💊 Drug Inventory | Manage hospital drug catalog |
| ⚡ System Settings | Configure booking rules and clinic hours |

---

## 🛠️ Tech Stack

<table>
<tr>
<td valign="top" width="50%">

### Backend
- **PHP 8.4+**
- **Laravel 12** - Web framework
- **Laravel Fortify** - Authentication
- **Laravel Sanctum** - API tokens
- **Laravel Reverb** - WebSockets
- **Spatie Permission** - Role-based access

</td>
<td valign="top" width="50%">

### Frontend
- **Livewire 4.0** - Reactive components
- **Flux UI 2.9** - UI components
- **Tailwind CSS 4.0** - Styling
- **Alpine.js** - JavaScript
- **Vite** - Build tool

</td>
</tr>
<tr>
<td valign="top">

### Database
- **SQLite** (Development)
- **MySQL 8.0+** (Production)

</td>
<td valign="top">

### Testing
- **Pest PHP 4** - Testing framework
- **Laravel Pint** - Code formatting

</td>
</tr>
</table>

---

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- NPM 9.x or higher

### Quick Start

```bash
# Clone the repository
git clone https://github.com/your-username/caretime.git
cd caretime

# Install dependencies and setup
composer setup

# Start development server
composer dev
```

The application will be available at `http://localhost:8000`

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate --seed

# Build assets
npm run build

# Start servers (in separate terminals)
php artisan serve
php artisan queue:listen
php artisan reverb:start
npm run dev
```

---

## 👤 Default Accounts

After seeding, use these accounts to explore the system:

| Role | Email | Password |
|------|-------|----------|
| 👑 Admin | `admin@caretime.local` | `password` |
| 👩‍⚕️ Nurse | `nurse@caretime.local` | `password` |
| 👨‍⚕️ Doctor | `doctor@caretime.local` | `password` |
| 💰 Cashier | `cashier@caretime.local` | `password` |
| 👤 Patient | `patient@caretime.local` | `password` |

---

## 📁 Project Structure

```
caretime/
├── app/
│   ├── Livewire/           # Livewire components by role
│   │   ├── Admin/          # Admin module
│   │   ├── Cashier/        # Cashier module
│   │   ├── Doctor/         # Doctor module
│   │   ├── Nurse/          # Nurse module
│   │   └── Patient/        # Patient module
│   ├── Models/             # Eloquent models
│   └── Traits/Models/      # Relationship traits
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── documents/
│   ├── project/            # Project documentation
│   └── productionsetup/    # Deployment guides
├── resources/views/
│   └── livewire/           # Blade templates
└── tests/                  # Pest tests
```

---

## 🧪 Testing

```bash
# Run full test suite
composer test

# Run Pest tests only
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/Auth

# Run with filter
./vendor/bin/pest --filter="test name"

# Code formatting
composer lint
```

---

## 📖 Documentation

Comprehensive documentation is available in the `documents/` folder:

### Project Documentation
| Document | Description |
|----------|-------------|
| [PROJECT.md](documents/project/PROJECT.md) | Main project overview |
| [TECHNICAL.md](documents/project/TECHNICAL.md) | Technical architecture |
| [DATABASE.md](documents/project/DATABASE.md) | Database schema |
| [CLIENT-GUIDE.md](documents/project/CLIENT-GUIDE.md) | User guide with scenarios |

### Module Logic
| Document | Description |
|----------|-------------|
| [LOGIC-PATIENT.md](documents/project/LOGIC-PATIENT.md) | Patient module |
| [LOGIC-NURSE.md](documents/project/LOGIC-NURSE.md) | Nurse module |
| [LOGIC-DOCTOR.md](documents/project/LOGIC-DOCTOR.md) | Doctor module |
| [LOGIC-CASHIER.md](documents/project/LOGIC-CASHIER.md) | Cashier module |
| [LOGIC-ADMIN.md](documents/project/LOGIC-ADMIN.md) | Admin module |

### Deployment
| Document | Description |
|----------|-------------|
| [Production Setup](documents/productionsetup/README.md) | DigitalOcean + Nginx deployment |

---

## 🔄 Workflow

```
┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   PATIENT    │    │    NURSE     │    │   DOCTOR     │    │   CASHIER    │
│              │    │              │    │              │    │              │
│  1. Book     │───▶│  2. Approve  │    │              │    │              │
│  Appointment │    │  3. Check-in │───▶│  5. Examine  │───▶│  6. Process  │
│              │    │  4. Triage   │    │  6. Diagnose │    │     Payment  │
│              │    │              │    │  7. Prescribe│    │              │
└──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘
       │                   │                   │                   │
       └───────────────────┴───────────────────┴───────────────────┘
                           Real-time Updates via WebSocket
```

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is developed as a thesis/capstone project for **Guardiano Maternity and Children Clinic and Hospital**.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Livewire](https://livewire.laravel.com) - Full-stack framework
- [Flux UI](https://fluxui.dev) - UI component library
- [Tailwind CSS](https://tailwindcss.com) - CSS framework
- [Spatie](https://spatie.be) - Laravel packages

---

<p align="center">
  Made with ❤️ for Guardiano Maternity and Children Clinic
</p>

<p align="center">
  <a href="#caretime">Back to top ⬆️</a>
</p>
