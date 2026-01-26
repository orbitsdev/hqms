<?php

use App\Livewire\Nurse\Appointments as NurseAppointments;
use App\Livewire\Nurse\AppointmentShow as NurseAppointmentShow;
use App\Livewire\Nurse\Dashboard as NurseDashboard;
use App\Livewire\Nurse\DoctorSchedules as NurseDoctorSchedules;
use App\Livewire\Nurse\MedicalRecords as NurseMedicalRecords;
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

Route::get('dashboard', function () {
    // redire base on role
    if (auth()->user()->isPatient()) {
        return redirect()->route('patient.dashboard');
    }

    if (auth()->user()->isNurse()) {
        return redirect()->route('nurse.dashboard');
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
});

require __DIR__.'/settings.php';
