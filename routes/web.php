<?php

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\HospitalDrugManagement as AdminHospitalDrugManagement;
use App\Livewire\Admin\ServiceManagement as AdminServiceManagement;
use App\Livewire\Admin\UserManagement as AdminUserManagement;
use App\Livewire\Cashier\BillingQueue as CashierBillingQueue;
use App\Livewire\Cashier\Dashboard as CashierDashboard;
use App\Livewire\Cashier\PaymentHistory as CashierPaymentHistory;
use App\Livewire\Cashier\ProcessBilling as CashierProcessBilling;
use App\Livewire\Display\QueueMonitor;
use App\Livewire\Doctor\Admissions as DoctorAdmissions;
use App\Livewire\Doctor\Dashboard as DoctorDashboard;
use App\Livewire\Doctor\Examination as DoctorExamination;
use App\Livewire\Doctor\MySchedule as DoctorMySchedule;
use App\Livewire\Doctor\PatientHistory as DoctorPatientHistory;
use App\Livewire\Doctor\PatientQueue as DoctorPatientQueue;
use App\Livewire\Nurse\Admissions as NurseAdmissions;
use App\Livewire\Nurse\Appointments as NurseAppointments;
use App\Livewire\Nurse\AppointmentShow as NurseAppointmentShow;
use App\Livewire\Nurse\Dashboard as NurseDashboard;
use App\Livewire\Nurse\DoctorSchedules as NurseDoctorSchedules;
use App\Livewire\Nurse\MedicalRecords as NurseMedicalRecords;
use App\Livewire\Nurse\PatientHistory as NursePatientHistory;
use App\Livewire\Nurse\TodayQueue as NurseTodayQueue;
use App\Livewire\Nurse\WalkInRegistration as NurseWalkIn;
use App\Livewire\Patient\Appointments;
use App\Livewire\Patient\AppointmentShow;
use App\Livewire\Patient\BookAppointment;
use App\Livewire\Patient\Dashboard;
use App\Livewire\Patient\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Queue Display Routes (Public - No Auth Required)
Route::prefix('display')->name('display.')->group(function () {
    Route::get('/', QueueMonitor::class)->name('all');
    Route::get('/{type}', QueueMonitor::class)->name('type');
});

Route::get('dashboard', function () {
    // redirect based on role
    if (auth()->user()->isPatient()) {
        return redirect()->route('patient.dashboard');
    }

    if (auth()->user()->isDoctor()) {
        return redirect()->route('doctor.dashboard');
    }

    if (auth()->user()->isNurse()) {
        return redirect()->route('nurse.dashboard');
    }

    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->isCashier()) {
        return redirect()->route('cashier.dashboard');
    }

    return redirect()->route('nurse.dashboard');
})
    ->middleware(['auth'])
    ->name('dashboard');

// Patient Portal Routes
Route::prefix('patient')->name('patient.')->middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/profile', Profile::class)->name('profile');

    Route::middleware('personal-info-complete')->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::redirect('/dashboard', '/patient')->name('dashboard.redirect');
        Route::get('/appointments', Appointments::class)->name('appointments');
        Route::get('/appointments/book', BookAppointment::class)->name('appointments.book');
        Route::get('/appointments/{appointment}', AppointmentShow::class)->name('appointments.show');
        Route::get('/records', \App\Livewire\Patient\MedicalRecords::class)->name('records');
        Route::get('/records/{medicalRecord}', \App\Livewire\Patient\MedicalRecordShow::class)->name('records.show');
        Route::get('/queue', \App\Livewire\Patient\ActiveQueue::class)->name('queue');
    });
});
// nurse portal routes
Route::prefix('nurse')->name('nurse.')->middleware(['auth', 'role:nurse'])->group(function () {
    Route::get('/', NurseDashboard::class)->name('dashboard');
    Route::redirect('/dashboard', '/nurse')->name('dashboard.redirect');
    Route::get('/doctor-schedules', NurseDoctorSchedules::class)->name('doctor-schedules');
    Route::get('/appointments', NurseAppointments::class)->name('appointments');
    Route::get('/appointments/{appointment}', NurseAppointmentShow::class)->name('appointments.show');
    Route::get('/queue', NurseTodayQueue::class)->name('queue');
    Route::get('/walk-in', NurseWalkIn::class)->name('walk-in');
    Route::get('/medical-records', NurseMedicalRecords::class)->name('medical-records');
    Route::get('/patient-history', NursePatientHistory::class)->name('patient-history');
    Route::get('/admissions', NurseAdmissions::class)->name('admissions');
});

// doctor portal routes
Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/', DoctorDashboard::class)->name('dashboard');
    Route::redirect('/dashboard', '/doctor')->name('dashboard.redirect');
    Route::get('/queue', DoctorPatientQueue::class)->name('queue');
    Route::get('/examine/{medicalRecord}', DoctorExamination::class)->name('examine');
    Route::get('/patient-history', DoctorPatientHistory::class)->name('patient-history');
    Route::get('/admissions', DoctorAdmissions::class)->name('admissions');
    Route::get('/schedule', DoctorMySchedule::class)->name('schedule');
});

// admin portal routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::redirect('/dashboard', '/admin')->name('dashboard.redirect');
    Route::get('/users', AdminUserManagement::class)->name('users');
    Route::get('/services', AdminServiceManagement::class)->name('services');
    Route::get('/drugs', AdminHospitalDrugManagement::class)->name('drugs');
});

// cashier portal routes
Route::prefix('cashier')->name('cashier.')->middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/', CashierDashboard::class)->name('dashboard');
    Route::redirect('/dashboard', '/cashier')->name('dashboard.redirect');
    Route::get('/queue', CashierBillingQueue::class)->name('queue');
    Route::get('/process/{medicalRecord}', CashierProcessBilling::class)->name('process');
    Route::get('/history', CashierPaymentHistory::class)->name('history');
});

require __DIR__.'/settings.php';
