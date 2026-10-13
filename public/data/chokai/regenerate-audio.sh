#!/usr/bin/env bash
# Jalankan dari root project Laravel, setelah lesson-1..5.json baru disalin ke public/data/chokai/
# Hapus mp3 lama q1-q5 (audionya sudah berubah), lalu generate ulang.
# Tanpa --force, hanya file yang hilang yang dibuat (hemat kuota Google TTS).
set -e
for n in 1 2 3 4 5; do
  for q in 1 2 3 4 5; do
    rm -f "public/audio/chokai/lesson-$n/l$n-q$q.mp3"
  done
  php artisan chokai:generate-audio --lesson=$n
done
