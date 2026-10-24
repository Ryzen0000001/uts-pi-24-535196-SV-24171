<?php

namespace App\Support;

/**
 * Pesan validasi berbahasa Indonesia, dipakai bersama oleh controller dan FormRequest.
 */
final class ValidationMessages
{
    public static function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'email' => 'Format :attribute tidak valid.',
            'unique' => ':attribute sudah terdaftar.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'date_format' => ':attribute harus berformat tahun-bulan-tanggal.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
            'min.numeric' => ':attribute tidak boleh kurang dari :min.',
            'max.numeric' => ':attribute tidak boleh lebih dari :max.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'password' => 'Kata sandi',
            'motor' => 'Motor',
            'serviced_at' => 'Tanggal servis',
            'odometer_km' => 'Kilometer',
            'service_type' => 'Jenis servis',
            'cost' => 'Biaya',
            'workshop' => 'Bengkel',
            'notes' => 'Catatan',
        ];
    }
}
