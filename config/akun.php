<?php

// Konfigurasi akun login untuk area internal.
// Skema database Fase 3 tidak punya tabel akun/kredensial, sehingga akun
// disimpan di sini. Nama & departemen tetap diambil dari tabel `karyawan`.
// Semua password demo: demo1234 (sudah di-hash dengan Hash::make).

return [

    // id_karyawan => hash password
    'akun' => [
        'K-008' => '$2y$12$VjjBCg/3i9VLAtNN8h/S8e6PqINPtFnI388V7XQv.qQaMA30FpXTC', // Hadi Nurdin
        'K-007' => '$2y$12$VjjBCg/3i9VLAtNN8h/S8e6PqINPtFnI388V7XQv.qQaMA30FpXTC', // Ezha Vitriyani
        'K-009' => '$2y$12$VjjBCg/3i9VLAtNN8h/S8e6PqINPtFnI388V7XQv.qQaMA30FpXTC', // Riffan Arriandi
        'K-002' => '$2y$12$VjjBCg/3i9VLAtNN8h/S8e6PqINPtFnI388V7XQv.qQaMA30FpXTC', // Eli Hernawati
    ],

    // Pemetaan id_departemen -> kode peran aplikasi.
    'peran_departemen' => [
        'DEP-09' => 'fo',   // Front Office
        'DEP-19' => 'tix',  // Ticketing Admin
        'DEP-06' => 'fin',  // Finance
        'DEP-01' => 'mgr',  // Direksi / Manajer
    ],

    // Label tampilan untuk setiap kode peran.
    'label_peran' => [
        'fo'  => 'Front Office',
        'tix' => 'Ticketing Admin',
        'fin' => 'Finance',
        'mgr' => 'Manajer',
    ],

    // Password sama untuk semua akun demo (dipakai tombol isi otomatis di halaman login).
    'password_demo' => 'demo1234',
];
