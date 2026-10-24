<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk screenshot laporan. Jalankan:
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Akun Demo', 'password' => Hash::make('password123')],
        );

        if ($user->serviceLogs()->exists()) {
            return;
        }

        $rows = [
            ['2026-03-02', 18420, 'Ganti oli mesin', 55000, 'AHASS Sleman', null],
            ['2026-04-18', 19950, 'Ganti kampas rem depan', 70000, 'Bengkel Pak Min', 'Kampas sudah tipis'],
            ['2026-06-09', 21800, 'Servis rutin', 185000, 'AHASS Sleman', 'Bersihkan CVT, ganti busi'],
            ['2026-08-21', 23400, 'Ganti oli mesin', 55000, 'AHASS Sleman', null],
            ['2026-10-12', 24310, 'Ganti ban belakang', 260000, 'Bengkel Pak Min', 'Ban lama retak'],
        ];

        foreach ($rows as [$date, $km, $type, $cost, $workshop, $notes]) {
            $user->serviceLogs()->create([
                'motor' => 'Vario 125',
                'serviced_at' => $date,
                'odometer_km' => $km,
                'service_type' => $type,
                'cost' => $cost,
                'workshop' => $workshop,
                'notes' => $notes,
            ]);
        }
    }
}
