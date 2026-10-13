<?php

return [
    'accepted' => ':attribute harus disetujui.',
    'array' => ':attribute harus berupa array.',
    'boolean' => 'Isian :attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'prohibited' => 'Isian :attribute tidak diizinkan.',
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute ini sudah terdaftar.',

    'password' => [
        'letters' => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute yang dimasukkan pernah bocor di kebocoran data. Silakan pilih :attribute lain.',
    ],

    'custom' => [],

    'attributes' => [
        'name' => 'Nama',
        'email' => 'Email',
        'password' => 'Kata sandi',
        'password_confirmation' => 'Konfirmasi kata sandi',
        'token' => 'Token',
        'locale' => 'Bahasa',
    ],
];
