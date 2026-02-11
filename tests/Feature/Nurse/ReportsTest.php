<?php

use App\Livewire\Nurse\Reports;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\Queue;
use App\Models\User;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('nurse', 'web');

    $this->nurse = User::factory()->create();
    $this->nurse->assignRole('nurse');

    PersonalInformation::factory()->create([
        'user_id' => $this->nurse->id,
        'first_name' => 'Test',
        'last_name' => 'Nurse',
    ]);

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'ob',
        'name' => 'Obstetrics',
        'short_name' => 'OB',
    ]);
});

describe('Reports Page', function () {
    it('renders the reports component', function () {
        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->assertSuccessful()
            ->assertSee('Reports');
    });

    it('defaults to daily census report type', function () {
        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->assertSet('reportType', 'daily_census')
            ->assertSee('Daily Census');
    });

    it('can switch to appointment statistics tab', function () {
        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'appointment_stats')
            ->assertSee('Appointment Statistics')
            ->assertSee('Total Appointments');
    });

    it('can switch to service utilization tab', function () {
        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'service_utilization')
            ->assertSee('Service Utilization')
            ->assertSee('Total Services');
    });

    it('can switch to queue performance tab', function () {
        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'queue_performance')
            ->assertSee('Queue Performance')
            ->assertSee('Patients Served');
    });
});

describe('Daily Census Data', function () {
    it('returns census data for selected date', function () {
        MedicalRecord::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'visit_type' => 'new',
            'created_at' => today(),
        ]);

        $component = Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportDate', today()->format('Y-m-d'));

        $reportData = $component->viewData('reportData');

        expect($reportData['total_patients'])->toBe(1)
            ->and($reportData['by_visit_type']['new'])->toBe(1)
            ->and($reportData['by_consultation_type'])->toHaveKey('Obstetrics');
    });
});

describe('Appointment Statistics Data', function () {
    it('calculates appointment stats by status and source', function () {
        Appointment::factory()->count(3)->create([
            'consultation_type_id' => $this->consultationType->id,
            'appointment_date' => today(),
            'status' => 'pending',
            'source' => 'online',
        ]);

        Appointment::factory()->count(2)->create([
            'consultation_type_id' => $this->consultationType->id,
            'appointment_date' => today(),
            'status' => 'completed',
            'source' => 'walk-in',
        ]);

        $reports = new Reports;
        $reports->dateFrom = today()->startOfMonth()->format('Y-m-d');
        $reports->dateTo = today()->format('Y-m-d');
        $data = $reports->appointmentStatsData();

        expect($data['total'])->toBe(5)
            ->and($data['completed'])->toBe(2)
            ->and($data['by_status']['pending'])->toBe(3)
            ->and($data['by_status']['completed'])->toBe(2)
            ->and($data['by_source']['online'])->toBe(3)
            ->and($data['by_source']['walk-in'])->toBe(2)
            ->and($data['by_consultation_type'])->toHaveKey('Obstetrics');
    });

    it('groups appointments by consultation type', function () {
        $pedType = ConsultationType::factory()->create([
            'code' => 'ped',
            'name' => 'Pediatrics',
            'short_name' => 'PED',
        ]);

        Appointment::factory()->count(2)->create([
            'consultation_type_id' => $this->consultationType->id,
            'appointment_date' => today(),
        ]);

        Appointment::factory()->count(3)->create([
            'consultation_type_id' => $pedType->id,
            'appointment_date' => today(),
        ]);

        $reports = new Reports;
        $reports->dateFrom = today()->startOfMonth()->format('Y-m-d');
        $reports->dateTo = today()->format('Y-m-d');
        $data = $reports->appointmentStatsData();

        expect($data['by_consultation_type']['Obstetrics'])->toBe(2)
            ->and($data['by_consultation_type']['Pediatrics'])->toBe(3);
    });
});

describe('Service Utilization Data', function () {
    it('calculates visit type distribution', function () {
        MedicalRecord::factory()->count(3)->create([
            'consultation_type_id' => $this->consultationType->id,
            'visit_type' => 'new',
            'visit_date' => today(),
        ]);

        MedicalRecord::factory()->count(2)->create([
            'consultation_type_id' => $this->consultationType->id,
            'visit_type' => 'old',
            'visit_date' => today(),
        ]);

        MedicalRecord::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'visit_type' => 'revisit',
            'visit_date' => today(),
        ]);

        $reports = new Reports;
        $reports->dateFrom = today()->startOfMonth()->format('Y-m-d');
        $reports->dateTo = today()->format('Y-m-d');
        $data = $reports->serviceUtilizationData();

        expect($data['total'])->toBe(6)
            ->and($data['by_visit_type']['new'])->toBe(3)
            ->and($data['by_visit_type']['old'])->toBe(2)
            ->and($data['by_visit_type']['revisit'])->toBe(1)
            ->and($data['by_consultation_type'])->toHaveKey('Obstetrics');
    });
});

describe('Queue Performance Data', function () {
    it('calculates average wait and service times', function () {
        $user = User::factory()->create();

        Queue::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 1,
            'queue_date' => today()->subDay(),
            'status' => 'completed',
            'created_at' => now()->subMinutes(40),
            'serving_started_at' => now()->subMinutes(30),
            'serving_ended_at' => now()->subMinutes(10),
        ]);

        Queue::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 2,
            'queue_date' => today()->subDay(),
            'status' => 'completed',
            'created_at' => now()->subMinutes(50),
            'serving_started_at' => now()->subMinutes(30),
            'serving_ended_at' => now()->subMinutes(20),
        ]);

        $component = Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'queue_performance')
            ->set('dateFrom', today()->subDay()->format('Y-m-d'))
            ->set('dateTo', today()->format('Y-m-d'));

        $data = $component->instance()->queuePerformanceData();

        expect($data['total_served'])->toBe(2)
            ->and($data['avg_wait'])->toBeGreaterThan(0)
            ->and($data['avg_service'])->toBeGreaterThan(0)
            ->and($data['by_consultation_type'])->toHaveKey('Obstetrics');
    });

    it('returns zero averages when no completed queues', function () {
        $component = Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'queue_performance')
            ->set('dateFrom', today()->format('Y-m-d'))
            ->set('dateTo', today()->format('Y-m-d'));

        $data = $component->instance()->queuePerformanceData();

        expect($data['total_served'])->toBe(0)
            ->and($data['avg_wait'])->toBe(0)
            ->and($data['avg_service'])->toBe(0)
            ->and($data['avg_patients_per_day'])->toBe(0.0);
    });
});

describe('Excel Downloads', function () {
    it('downloads daily census excel', function () {
        Excel::fake();

        $filename = 'daily-patient-census-'.today()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'daily_census')
            ->call('downloadExcel');

        Excel::assertDownloaded($filename);
    });

    it('downloads appointment statistics excel', function () {
        Excel::fake();

        $dateFrom = today()->startOfMonth()->format('Y-m-d');
        $dateTo = today()->format('Y-m-d');
        $filename = 'appointment-statistics-'.$dateFrom.'-to-'.$dateTo.'.xlsx';

        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'appointment_stats')
            ->call('downloadExcel');

        Excel::assertDownloaded($filename);
    });

    it('downloads service utilization excel', function () {
        Excel::fake();

        $dateFrom = today()->startOfMonth()->format('Y-m-d');
        $dateTo = today()->format('Y-m-d');
        $filename = 'service-utilization-'.$dateFrom.'-to-'.$dateTo.'.xlsx';

        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'service_utilization')
            ->call('downloadExcel');

        Excel::assertDownloaded($filename);
    });

    it('downloads queue performance excel', function () {
        Excel::fake();

        $dateFrom = today()->startOfMonth()->format('Y-m-d');
        $dateTo = today()->format('Y-m-d');
        $filename = 'queue-performance-'.$dateFrom.'-to-'.$dateTo.'.xlsx';

        Livewire::actingAs($this->nurse)
            ->test(Reports::class)
            ->set('reportType', 'queue_performance')
            ->call('downloadExcel');

        Excel::assertDownloaded($filename);
    });
});
