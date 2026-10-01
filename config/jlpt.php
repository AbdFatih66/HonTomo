<?php

/**
 * Tes JLPT (simulasi ujian) — konfigurasi per level.
 *
 * SOAL DAN KUNCI JAWABAN ada dalam satu berkas JSON per sesi, dikelompokkan per
 * PACK (lihat `packs` di bawah): resources/lang-data/<packs.*.data_dir>/<sesi>.json
 * (field `answer` tiap soal; `script`/`transcript`/`audio_text` = naskah audio
 * chokai, juga hanya untuk server).
 *
 * Dua format bank (field `format` pada pack):
 *   - classic : soal datar + markup ⟦…⟧ (bank soal asli, dirender JlptText dkk.)
 *   - mock    : soal bersarang mondai→groups→questions (paket orisinal HonTomo,
 *               hasil scripts/jlpt-mock-to-bank.py; dirender JlptMock*)
 * Kunci di KEDUA format 1-based (1..4), sama dengan jawaban user.
 * Berkas itu hanya dibaca server: soal dikirim ke browser SETELAH sesi
 * dimulai dan field `answer` selalu dibuang (JlptTestService::publicTest).
 * Jangan menaruh kunci di frontend atau di endpoint mana pun.
 *
 * Untuk sesi/level baru: buat JSON-nya (bentuk sama dengan mojigoi.json atau
 * chokai.json), isi field `answer` tiap soal dari 正答表, lalu ubah `enabled`
 * jadi true. Selama masih ada sesi yang belum aktif, rekap bersifat SEMENTARA
 * (tanpa keputusan lulus/tidak); begitu semuanya aktif, rekap otomatis memberi
 * keputusan.
 *
 * Skala nilai mengikuti struktur resmi JLPT N5 (total 0–180,
 * jlpt.jp/e/guideline/results.html):
 *   - 言語知識（文字・語彙・文法）・読解  0–120, batas minimal 38
 *   - 聴解                               0–60,  batas minimal 19
 *   - lulus bila total ≥ 80 DAN kedua batas minimal terpenuhi.
 * Batas-batas di atas resmi, tetapi JLPT tidak memublikasikan pembagian poin
 * per sesi dan memakai skor ekuivalen (scaled). Di sini nilai grup dihitung
 * proporsional dari jumlah benar, jadi hasilnya ESTIMASI, bukan skor resmi.
 */
return [
    // Akar bank soal; berkas tiap sesi = {data_path}/{pack.data_dir}/{sesi.file}.
    'data_path' => resource_path('lang-data'),

    // Audio pack bergaya classic: lihat `packs.*.audio_path`. Field `audio` di bank
    // soal = NOMOR TREK (berkas berawalan "NN-"), nama berkas, atau path absolut
    // ("/audio/…", dipakai pack mock); lihat JlptTestService::resolveAudio.

    // Toleransi (detik) untuk kiriman jawaban akhir setelah waktu habis —
    // menutup jeda jaringan antara timer klien dan server.
    'submit_grace_seconds' => 20,

    // XP per pack, HANYA mode strict (mode latihan tidak memberi XP):
    //  - xp_completed : pertama kali user menyelesaikan satu pack;
    //  - xp_passed    : tambahan, pertama kali user LULUS pack itu (bisa di attempt berikutnya).
    'xp_completed' => 30,
    'xp_passed' => 20,

    // Pack = satu paket soal. `enabled` pack 'private' dibaca dari env supaya
    // build komersil bisa mematikannya (JLPT_PRIVATE_PACK=false): pack yang mati
    // tidak muncul di API dan tidak bisa dimulai. `admin_only` = hanya admin
    // (User::isAdmin) yang boleh mengaksesnya.
    'packs' => [
        // Bank soal asli (salinan) — HANYA untuk pemakaian pribadi, jadi `admin_only`:
        // hanya user ber-role admin yang bisa melihat/memulai/membuka paket ini
        // (non-admin mendapat 404, seolah paketnya tidak ada). Berlaku juga untuk
        // attempt lama milik user yang kini bukan admin.
        'private' => [
            'enabled' => (bool) env('JLPT_PRIVATE_PACK', true),
            'admin_only' => true,
            'level' => 'N5',
            'format' => 'classic',
            'data_dir' => 'jlpt/n5',
            // folder audio di public/: {audio_path}/{level}/{berkas|NN-…}
            'audio_path' => '/audio/jlpt',
            'title' => [
                'id' => 'Tes JLPT N5 (soal asli)',
                'en' => 'JLPT N5 Test (original questions)',
            ],
        ],

        // Paket orisinal HonTomo (dulu "Simulasi JLPT"). Tambah paket baru dengan
        // menjalankan scripts/jlpt-mock-to-bank.py lalu menambah entri seperti ini.
        'n5-test-1' => [
            'enabled' => true,
            'level' => 'N5',
            'format' => 'mock',
            'data_dir' => 'jlpt/packs/n5-test-1',
            // audio/gambar pack ini memakai path absolut di dalam bank soalnya
            'audio_path' => null,
            'title' => [
                'id' => 'Simulasi JLPT N5 — Paket 1',
                'en' => 'JLPT N5 Mock Test — Set 1',
            ],
        ],
    ],

    'levels' => [
        'N5' => [
            'pass_total' => 80,
            'total_max' => 180,

            'groups' => [
                'language_knowledge' => [
                    'max' => 120,
                    'min' => 38,
                    'sections' => ['mojigoi', 'bunpou_dokkai'],
                ],
                'listening' => [
                    'max' => 60,
                    'min' => 19,
                    'sections' => ['chokai'],
                ],
            ],

            // `questions` = jumlah soal resmi. Untuk sesi yang aktif, jumlah
            // sebenarnya dibaca dari bank soal; angka ini dipakai untuk sesi
            // yang belum aktif (rekap sementara). `file` = nama berkas bank di
            // folder pack.
            'sections' => [
                // 言語知識（文字・語彙）— 25 menit, 33 soal
                'mojigoi' => [
                    'order' => 1,
                    'enabled' => true,
                    'minutes' => 25,
                    'questions' => 33,
                    'file' => 'mojigoi.json',
                ],

                // 言語知識（文法）・読解 — 50 menit, 32 soal
                'bunpou_dokkai' => [
                    'order' => 2,
                    'enabled' => true,
                    'minutes' => 50,
                    'questions' => 32,
                    'file' => 'bunpou_dokkai.json',
                ],

                // 聴解 — 30 menit, 24 soal. Nomor soal restart tiap もんだい, jadi
                // id-nya "{mondai}-{no}" (contoh "2-4"). Diputar berurutan oleh
                // pemutar audio (tanpa mundur/ulang), lihat JlptListeningExam.vue.
                'chokai' => [
                    'order' => 3,
                    'enabled' => true,
                    'minutes' => 30,
                    'questions' => 24,
                    'file' => 'chokai.json',
                ],
            ],
        ],
    ],
];
