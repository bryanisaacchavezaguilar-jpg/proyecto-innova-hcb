<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $specialties = collect(['Medicina general', 'Pediatría', 'Enfermería'])
            ->mapWithKeys(fn (string $name) => [$name => Specialty::firstOrCreate(['name' => $name])]);

        $professionals = collect([
            ['Dra. Lucía Herrera', 'DEMO-MED-101', 'Medicina general'],
            ['Dr. Mateo Ríos', 'DEMO-MED-102', 'Pediatría'],
            ['Enf. Camila Soto', 'DEMO-NUR-103', 'Enfermería'],
        ])->map(fn (array $entry) => Professional::firstOrCreate(
            ['license_number' => $entry[1]],
            ['full_name' => $entry[0], 'specialty_id' => $specialties[$entry[2]]->id, 'is_active' => true],
        ));

        $patients = collect([
            ['Ana', 'Torres', 'DEMO-1001', '555-0101'],
            ['Luis', 'Ortega', 'DEMO-1002', '555-0102'],
            ['Valentina', 'Pardo', 'DEMO-1003', '555-0103'],
            ['Samuel', 'Vega', 'DEMO-1004', '555-0104'],
        ])->map(fn (array $entry) => Patient::firstOrCreate(
            ['document_number' => $entry[2]],
            ['first_name' => $entry[0], 'last_name' => $entry[1], 'document_type' => 'DEMO', 'phone' => $entry[3]],
        ));

        foreach ([
            [$patients[0], $professionals[0], today()->setTime(9, 0), 'Control general', 'confirmed'],
            [$patients[1], $professionals[1], today()->setTime(10, 30), 'Consulta pediátrica', 'scheduled'],
            [$patients[2], $professionals[0], today()->setTime(14, 0), 'Seguimiento', 'scheduled'],
        ] as [$patient, $professional, $startsAt, $reason, $status]) {
            Appointment::firstOrCreate(
                ['patient_id' => $patient->id, 'professional_id' => $professional->id, 'starts_at' => $startsAt],
                ['ends_at' => $startsAt->copy()->addMinutes(30), 'reason' => $reason, 'status' => $status],
            );
        }
    }
}
