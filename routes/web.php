<?php

use App\Livewire\Patient\AppointmentShow;
use App\Livewire\Patient\Appointments;
use App\Livewire\Patient\BookAppointment;
use App\Livewire\Patient\Dashboard;
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
    Route::get('/profile', Profile::class)->name('profile');

    Route::middleware('personal-info-complete')->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::redirect('/dashboard', '/patient')->name('dashboard.redirect');
        Route::get('/appointments', Appointments::class)->name('appointments');
        Route::get('/appointments/book', BookAppointment::class)->name('appointments.book');
        Route::get('/appointments/{appointment}', AppointmentShow::class)->name('appointments.show');
    });
});

require __DIR__.'/settings.php';
