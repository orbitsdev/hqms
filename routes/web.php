<?php

use App\Livewire\Patient\Profile;
use App\Livewire\Patient\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Livewire\Patient\Appointments;
use App\Livewire\Patient\AppointmentShow;
use App\Livewire\Patient\BookAppointment;
use App\Livewire\Nurse\Dashboard as NurseDashboard;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', function(){
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
});

require __DIR__.'/settings.php';
