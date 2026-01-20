<?php

use App\Livewire\Patient\ActiveQueue;
use App\Livewire\Patient\AppointmentShow;
use App\Livewire\Patient\Appointments;
use App\Livewire\Patient\BookAppointment;
use App\Livewire\Patient\Dashboard;
use App\Livewire\Patient\MedicalRecordShow;
use App\Livewire\Patient\MedicalRecords;
use App\Livewire\Patient\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Patient Portal Routes
Route::prefix('patient')->name('patient.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::redirect('/dashboard', '/patient')->name('dashboard.redirect');
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/appointments', Appointments::class)->name('appointments');
    Route::get('/appointments/book', BookAppointment::class)->name('appointments.book');
    Route::get('/appointments/{appointment}', AppointmentShow::class)->name('appointments.show');
    Route::get('/queue', ActiveQueue::class)->name('queue');
    Route::get('/records', MedicalRecords::class)->name('records');
    Route::get('/records/{medicalRecord}', MedicalRecordShow::class)->name('records.show');
});

require __DIR__.'/settings.php';
