<?php

/**
 * Tes JLPT (simulasi ujian) — konfigurasi per level.
 *
 * SOAL DAN KUNCI JAWABAN ada dalam satu berkas JSON per sesi, dikelompokkan per
 * PACK (lihat `packs` di bawah): resources/lang-data/<packs.*.data_dir>/<sesi>.json
 * (field `answer` tiap soal; `script`/`transcript`/`audio_text` = naskah audio
 * chokai, juga hanya untuk server).
 *
 * Format bank: SEMUA pack memakai `classic` (soal datar + markup ⟦…⟧, dirender
 * JlptText dkk.), sehingga tampilan ujian sama untuk soal asli dan paket HonTomo.
 * Paket orisinal ditulis dulu dalam format sumber (docs/jlpt-mock.md), lalu diubah
 * dengan scripts/jlpt-mock-to-bank.py → scripts/jlpt-mock-to-classic.py.
 * (Kode untuk format `mock` masih dikenali server, tetapi tidak dipakai lagi.)
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

        // Bank soal asli JLPT N4 (salinan) — sama seperti 'private' (N5): HANYA admin.
        // Bank soal: resources/lang-data/jlpt/n4/. Audio chokai N4:
        // public/audio/jlpt/n4/NN-….mp3. Ikut dimatikan oleh JLPT_PRIVATE_PACK=false.
        'private-n4' => [
            'enabled' => (bool) env('JLPT_PRIVATE_PACK', true),
            'admin_only' => true,
            'level' => 'N4',
            'format' => 'classic',
            'data_dir' => 'jlpt/n4',
            'audio_path' => '/audio/jlpt',
            'title' => [
                'id' => 'Tes JLPT N4 (soal asli)',
                'en' => 'JLPT N4 Test (original questions)',
            ],
        ],

        // Bank soal asli JLPT N3 (salinan) — sama seperti 'private' (N5) dan 'private-n4': HANYA admin.
        // Bank soal: resources/lang-data/jlpt/n3/. Audio chokai N3: public/audio/jlpt/n3/NN-….mp3 (40 trek, istirahat trek 18),
        // gambar: public/images/jlpt/n3/ — lihat docs/jlpt-real-images-n3.md.
        // Ikut dimatikan oleh JLPT_PRIVATE_PACK=false.
        'private-n3' => [
            'enabled' => (bool) env('JLPT_PRIVATE_PACK', true),
            'admin_only' => true,
            'level' => 'N3',
            'format' => 'classic',
            'data_dir' => 'jlpt/n3',
            'audio_path' => '/audio/jlpt',
            'title' => [
                'id' => 'Tes JLPT N3 (soal asli)',
                'en' => 'JLPT N3 Test (original questions)',
            ],
        ],

        // Bank soal asli JLPT N2 (salinan) — sama seperti 'private' (N5), 'private-n4', dan 'private-n3': HANYA admin.
        // Ketiga sesi sudah ada (resources/lang-data/jlpt/n2/{mojigoi,bunpou_dokkai,chokai}.json). Audio chokai N2
        // = LIMA berkas utuh (satu per もんだい) di public/audio/jlpt/n2/N2-chokai-1…5; lihat docs/JLPT-CHOKAI-AUDIO-N2.md.
        // Ikut dimatikan oleh JLPT_PRIVATE_PACK=false.
        'private-n2' => [
            'enabled' => (bool) env('JLPT_PRIVATE_PACK', true),
            'admin_only' => true,
            'level' => 'N2',
            'format' => 'classic',
            'data_dir' => 'jlpt/n2',
            'audio_path' => '/audio/jlpt',
            'title' => [
                'id' => 'Tes JLPT N2 (soal asli)',
                'en' => 'JLPT N2 Test (original questions)',
            ],
        ],

        // Bank soal asli JLPT N1 (salinan) — sama seperti 'private' (N5), 'private-n4', 'private-n3', dan 'private-n2': HANYA admin.
        // Ketiga sesi sudah ada (resources/lang-data/jlpt/n1/{mojigoi,bunpou_dokkai,chokai}.json). Audio chokai N1
        // = LIMA berkas utuh (satu per もんだい) di public/audio/jlpt/n1/N1-chokai-1…5; lihat docs/JLPT-CHOKAI-AUDIO-N1.md.
        // Ikut dimatikan oleh JLPT_PRIVATE_PACK=false.
        'private-n1' => [
            'enabled' => (bool) env('JLPT_PRIVATE_PACK', true),
            'admin_only' => true,
            'level' => 'N1',
            'format' => 'classic',
            'data_dir' => 'jlpt/n1',
            'audio_path' => '/audio/jlpt',
            'title' => [
                'id' => 'Tes JLPT N1 (soal asli)',
                'en' => 'JLPT N1 Test (original questions)',
            ],
        ],

        // Paket orisinal HonTomo (dulu "Simulasi JLPT"). Tambah paket baru dengan
        // menjalankan scripts/jlpt-mock-to-bank.py lalu menambah entri seperti ini.
        'n5-test-1' => [
            'enabled' => true,
            'level' => 'N5',
            'format' => 'classic',
            'data_dir' => 'jlpt/packs/n5-test-1',
            // audio/gambar pack ini memakai path absolut di dalam bank soalnya
            'audio_path' => null,
            'title' => [
                'id' => 'Simulasi JLPT N5 — Paket 1',
                'en' => 'JLPT N5 Mock Test — Set 1',
            ],
        ],

        // Paket orisinal HonTomo N4 — ketiga sesi sudah ada (bank:
        // resources/lang-data/jlpt/packs/n4-test-1/{mojigoi,bunpou_dokkai,chokai}.json, sumber naskah:
        // resources/lang-data/jlpt/sources/n4-test-1.json, gambar: public/images/jlpt-mock/n4/,
        // audio TTS: public/audio/jlpt-mock/n4-test-1/). Level N4 mewajibkan ketiga sesi aktif;
        // set `false` bila audio/gambar belum ada di server (test JlptMockN4ChokaiTest menjaga
        // agar pack aktif selalu punya semua bank-nya). Panduan: docs/jlpt-mock-n4.md.
        'n4-test-1' => [
            'enabled' => true,
            'level' => 'N4',
            'format' => 'classic',
            'data_dir' => 'jlpt/packs/n4-test-1',
            'audio_path' => null,
            'title' => [
                'id' => 'Simulasi JLPT N4 — Paket 1',
                'en' => 'JLPT N4 Mock Test — Set 1',
            ],
        ],

        // Paket orisinal HonTomo N3 — ketiga sesi sudah ada (bank:
        // resources/lang-data/jlpt/packs/n3-test-1/{mojigoi,bunpou_dokkai,chokai}.json, sumber naskah:
        // resources/lang-data/jlpt/sources/n3-test-1.json, gambar chokai: public/images/jlpt-mock/n3/,
        // audio TTS: public/audio/jlpt-mock/n3-test-1/). Level N3 mewajibkan ketiga sesi aktif (test
        // JlptMockN3{Mojigoi,Bunpou,Chokai}Test menjaga agar pack aktif selalu punya semua bank-nya, dan
        // mengingatkan bila semua bank ada tetapi pack masih nonaktif). Set `false` bila audio/gambar chokai
        // belum ada di server — tes tetap jalan, tetapi pemutar menampilkan "File audio tidak ditemukan".
        // Panduan: docs/jlpt-mock-n3.md.
        'n3-test-1' => [
            'enabled' => true,
            'level' => 'N3',
            'format' => 'classic',
            'data_dir' => 'jlpt/packs/n3-test-1',
            'audio_path' => null,
            'title' => [
                'id' => 'Simulasi JLPT N3 — Paket 1',
                'en' => 'JLPT N3 Mock Test — Set 1',
            ],
        ],

        // Paket orisinal HonTomo N2 — ketiga sesi sudah ada (bank:
        // resources/lang-data/jlpt/packs/n2-test-1/{chokai,mojigoi,bunpou_dokkai}.json, sumber naskah:
        // resources/lang-data/jlpt/sources/n2-test-1.json, audio TTS: public/audio/jlpt-mock/n2-test-1/;
        // N2 tidak memakai gambar). Level N2 mewajibkan ketiga sesi aktif; paket ini `enabled => false` sampai
        // audio dibuat di server (JlptMockN2ChokaiTest/JlptMockN2MojigoiTest menjaga agar pack aktif selalu
        // punya semua bank-nya). Panduan: docs/jlpt-mock-n2.md.
        'n2-test-1' => [
            'enabled' => true,
            'level' => 'N2',
            'format' => 'classic',
            'data_dir' => 'jlpt/packs/n2-test-1',
            'audio_path' => null,
            'title' => [
                'id' => 'Simulasi JLPT N2 — Paket 1',
                'en' => 'JLPT N2 Mock Test — Set 1',
            ],
        ],

        // Paket orisinal HonTomo N1 — tiga sesi lengkap: mojigoi, bunpou_dokkai, chokai
        // (bank: resources/lang-data/jlpt/packs/n1-test-1/{mojigoi,bunpou_dokkai,chokai}.json,
        // naskah audio chokai: resources/lang-data/jlpt/sources/n1-test-1.json, audio TTS: public/audio/jlpt-mock/n1-test-1/
        // n1-chokai-1…5.mp3 — satu rekaman utuh per もんだい, sama seperti pack 'private-n1').
        // `enabled => true` karena ketiga berkas bank ada; audio perlu dibuat dulu. Panduan: docs/jlpt-mock-n1.md.
        'n1-test-1' => [
            'enabled' => true,
            'level' => 'N1',
            'format' => 'classic',
            'data_dir' => 'jlpt/packs/n1-test-1',
            'audio_path' => null,
            'title' => [
                'id' => 'Simulasi JLPT N1 — Paket 1',
                'en' => 'JLPT N1 Mock Test — Set 1',
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
        // JLPT N4 — total 0–180, lulus ≥ 90; batas minimal bahasa 38/120 dan
        // mendengarkan 19/60 (jlpt.jp/e/guideline/results.html). Sama seperti N5,
        // nilai grup di sini proporsional (ESTIMASI). Ketiga sesi (mojigoi,
        // bunpou_dokkai, chokai) aktif, jadi rekap langsung memberi keputusan
        // lulus/tidak. Audio chokai: public/audio/jlpt/n4/NN-….mp3 (39 trek, termasuk istirahat trek 21),
        // gambar: public/images/jlpt/n4/ — lihat docs/jlpt-real-images-n4.md.
        'N4' => [
            'pass_total' => 90,
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

            'sections' => [
                // 言語知識（文字・語彙）— 30 menit (sesuai sampul soal), 34 soal
                'mojigoi' => [
                    'order' => 1,
                    'enabled' => true,
                    'minutes' => 30,
                    'questions' => 34,
                    'file' => 'mojigoi.json',
                ],

                // 言語知識（文法）・読解 — 60 menit (sesuai sampul soal), 35 soal
                'bunpou_dokkai' => [
                    'order' => 2,
                    'enabled' => true,
                    'minutes' => 60,
                    'questions' => 35,
                    'file' => 'bunpou_dokkai.json',
                ],

                // 聴解 — 35 menit (sesuai sampul soal), 28 soal (8 + 7 + 5 + 8);
                // id soal "{mondai}-{no}", diputar berurutan (39 trek, trek 21 = istirahat) oleh
                // JlptListeningExam.vue.
                'chokai' => [
                    'order' => 3,
                    'enabled' => true,
                    'minutes' => 35,
                    'questions' => 28,
                    'file' => 'chokai.json',
                ],
            ],
        ],
        // JLPT N3 — total 0–180, lulus ≥ 95; batas minimal tiap bagian 19 (jlpt.jp/e/guideline/results.html).
        // Resminya ada TIGA bagian berskor 0–60 (bahasa [moji-goi + tata bahasa], membaca, mendengarkan), tetapi
        // sesi di sini menggabungkan tata bahasa dan membaca (bunpou_dokkai), sehingga bahasa + membaca
        // dihitung sebagai satu grup 0–120 dengan batas minimal 38 (= 19 + 19). Seperti N5/N4, nilai grup
        // proporsional (ESTIMASI). Ketiga sesi (mojigoi, bunpou_dokkai, chokai) aktif, jadi rekap langsung
        // memberi keputusan lulus/tidak.
        'N3' => [
            'pass_total' => 95,
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

            'sections' => [
                // 言語知識（文字・語彙）— 30 menit (sesuai sampul soal), 33 soal (8 + 6 + 9 + 5 + 5)
                'mojigoi' => [
                    'order' => 1,
                    'enabled' => true,
                    'minutes' => 30,
                    'questions' => 33,
                    'file' => 'mojigoi.json',
                ],

                // 言語知識（文法）・読解 — 70 menit (sesuai sampul soal), 39 soal (13 + 5 + 5 + 4 + 6 + 4 + 2)
                'bunpou_dokkai' => [
                    'order' => 2,
                    'enabled' => true,
                    'minutes' => 70,
                    'questions' => 39,
                    'file' => 'bunpou_dokkai.json',
                ],

                // 聴解 — 27 soal (6 + 6 + 3 + 4 + 8); 40 menit (sampul soal); id soal "{mondai}-{no}";
                // 40 trek audio (trek 18 = istirahat sebelum もんだい 3)
                'chokai' => [
                    'order' => 3,
                    'enabled' => true,
                    'minutes' => 40,
                    'questions' => 27,
                    'file' => 'chokai.json',
                ],
            ],
        ],
        // JLPT N2 — total 0–180, lulus ≥ 90; batas minimal tiap bagian 19 (jlpt.jp/e/guideline/results.html).
        // Seperti N3: resminya tiga bagian berskor 0–60 (bahasa [moji-goi + tata bahasa], membaca, mendengarkan),
        // sesi di sini menggabungkan tata bahasa dan membaca (bunpou_dokkai), jadi bahasa + membaca satu grup
        // 0–120 dengan batas minimal 38 (= 19 + 19). Nilai grup proporsional (ESTIMASI).
        // Lembar soal N2 hanya punya SATU sampul untuk 言語知識（文字・語彙・文法）・読解 (105 menit, 75 soal =
        // moji-goi 32 + tata bahasa/membaca 43), jadi pembagian waktu ke dua sesi aplikasi adalah ASUMSI:
        // moji-goi 30 menit + bunpou_dokkai 75 menit (total tetap 105). Ubah di sini bila ingin pembagian lain.
        // Ketiga sesi aktif, jadi rekap langsung memberi keputusan lulus/tidak.
        'N2' => [
            'pass_total' => 90,
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

            'sections' => [
                // 言語知識（文字・語彙）— 32 soal (5 + 5 + 5 + 7 + 5 + 5), nomor 1–32 di 正答表 (問題1–6)
                'mojigoi' => [
                    'order' => 1,
                    'enabled' => true,
                    'minutes' => 30,
                    'questions' => 32,
                    'file' => 'mojigoi.json',
                ],

                // 言語知識（文法）・読解 — 43 soal (12 + 5 + 5 + 5 + 9 + 2 + 3 + 2), nomor 33–75 (問題7–14);
                // id soal = nomor di 正答表, もんだい diberi nomor cetak 7–14.
                'bunpou_dokkai' => [
                    'order' => 2,
                    'enabled' => true,
                    'minutes' => 75,
                    'questions' => 43,
                    'file' => 'bunpou_dokkai.json',
                ],

                // 聴解 — 50 menit (sampul soal), 31 soal (5 + 6 + 5 + 11 + 4); もんだい 5 nomor 3 punya dua
                // pertanyaan (id "5-3-1", "5-3-2"). Audio: satu berkas utuh per もんだい (`whole_audio`).
                'chokai' => [
                    'order' => 3,
                    'enabled' => true,
                    'minutes' => 50,
                    'questions' => 31,
                    'file' => 'chokai.json',
                ],
            ],
        ],
        // JLPT N1 — total 0–180, lulus ≥ 100; batas minimal tiap bagian 19 (jlpt.jp/e/guideline/results.html).
        // Seperti N3/N2: resminya tiga bagian berskor 0–60 (bahasa [moji-goi + tata bahasa], membaca, mendengarkan),
        // sesi di sini menggabungkan tata bahasa dan membaca (bunpou_dokkai), jadi bahasa + membaca satu grup
        // 0–120 dengan batas minimal 38 (= 19 + 19). Nilai grup proporsional (ESTIMASI).
        // Lembar soal N1 hanya punya SATU sampul untuk 言語知識（文字・語彙・文法）・読解 (110 menit, 69 soal =
        // moji-goi 25 + tata bahasa/membaca 44), jadi pembagian waktu ke dua sesi aplikasi adalah ASUMSI:
        // moji-goi 25 menit + bunpou_dokkai 85 menit (total tetap 110). Ubah di sini bila ingin pembagian lain.
        // Ketiga sesi aktif, jadi rekap langsung memberi keputusan lulus/tidak.
        'N1' => [
            'pass_total' => 100,
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

            'sections' => [
                // 言語知識（文字・語彙）— 25 soal (6 + 7 + 6 + 6), nomor 1–25 di 正答表 (問題1–4)
                'mojigoi' => [
                    'order' => 1,
                    'enabled' => true,
                    'minutes' => 25,
                    'questions' => 25,
                    'file' => 'mojigoi.json',
                ],

                // 言語知識（文法）・読解 — 44 soal, nomor 26–69 (問題5–13; 10 + 5 + 5 + 3 + 9 + 4 + 2 + 4 + 2).
                // id soal = nomor di lembar soal / 正答表.
                'bunpou_dokkai' => [
                    'order' => 2,
                    'enabled' => true,
                    'minutes' => 85,
                    'questions' => 44,
                    'file' => 'bunpou_dokkai.json',
                ],

                // 聴解 — 60 menit (sampul soal), 36 soal (6 + 7 + 6 + 13 + 4); もんだい 5 nomor 3 punya dua
                // pertanyaan (id "5-3-1", "5-3-2"). Audio: satu berkas utuh per もんだい (`whole_audio`), N1-chokai-1…5.
                'chokai' => [
                    'order' => 3,
                    'enabled' => true,
                    'minutes' => 60,
                    'questions' => 36,
                    'file' => 'chokai.json',
                ],
            ],
        ],
    ],
];
