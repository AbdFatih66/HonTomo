#!/usr/bin/env python3
"""Validasi public/data/chokai/lesson-*.json.

Pakai:  python3 scripts/chokai-lint.py            (dari root project)
        python3 scripts/chokai-lint.py path/ke/folder

Cek: jumlah soal per pelajaran (sama dengan buku), id unik & berformat lN-qM,
answer valid, pilihan tidak kembar, audio_text tanpa tanda 《》, kanji di
prompt/choices sudah diberi furigana (TERMASUK yang bercampur angka, mis.
"2階" atau "6,000円" — angka tidak mengecualikan kanji lain di kalimat yang
sama dari pemeriksaan furigana), speaker valid, plus kaiwa.questions
(maks. 2 soal, id l{L}-kq{i}, tanpa audio_text sendiri).
Keluar dengan kode 1 kalau ada masalah.
"""
import json, re, sys, glob, os

# Jumlah soal per pelajaran = jumlah soal Chokai di buku (contoh 例 tidak dihitung).
# Pelajaran 1-25 lengkap. (P4 = 18 karena ada 8 soal dikte angka; P19 = 9 karena
# bagian 1 di buku hanya 4 soal.)
# N4 (file lesson-n4-{n}.json, id "n4-{n}", id soal n4l{n}-q{i}): jumlah soal per pelajaran N4.
EXPECTED_N4 = {n: 10 for n in range(1, 26)}
EXPECTED_N4[19] = 9
EXPECTED = {1: 10, 2: 10, 3: 10, 4: 18, 5: 10, 6: 10, 7: 10, 8: 10,
            9: 10, 10: 10, 11: 10, 12: 10, 13: 10, 14: 10, 15: 10,
            16: 10, 17: 10, 18: 10, 19: 9, 20: 10, 21: 10, 22: 10,
            23: 10, 24: 10, 25: 10}
KJ = r'[\u3400-\u9FFF々]'
SPEAKERS = {'male', 'female'}


def leftover(s):
    s = re.sub(KJ + r'+《[^》]*》', '', s)
    return re.findall(KJ + '+', s)


def turns(a):
    return [{'speaker': 'male', 'text': a}] if isinstance(a, str) else a


def main():
    folder = sys.argv[1] if len(sys.argv) > 1 else 'public/data/chokai'
    errors, warns = [], []
    files = sorted(glob.glob(os.path.join(folder, 'lesson-*.json')),
                   key=lambda p: (1 if 'lesson-n4-' in p else 0,
                                  int(re.search(r'lesson-(?:n4-)?(\d+)', p).group(1))))
    for path in files:
        d = json.load(open(path, encoding='utf-8'))
        raw = d['id']
        n4 = isinstance(raw, str) and raw.startswith('n4-')
        # L dipakai sebagai awalan id: N5 -> 'l{n}', N4 -> 'n4l{n}'
        n = int(raw.split('-')[1]) if n4 else raw
        prefix = f'n4l{n}' if n4 else f'l{raw}'
        expected = EXPECTED_N4 if n4 else EXPECTED
        qs = d['questions']
        if n in expected and len(qs) != expected[n]:
            errors.append(f'L{raw}: {len(qs)} soal, seharusnya {expected[n]}')
        if n not in expected:
            warns.append(f'L{raw}: belum ada di EXPECTED (tambahkan jumlah soal dari buku)')
        for t in turns(d['kaiwa']['audio_text']):
            if t['speaker'] not in SPEAKERS or '《' in t['text']:
                errors.append(f'L{raw} kaiwa: speaker/teks tidak valid')
        # kaiwa.questions: 0-2 soal ringan berdasarkan isi kaiwa (tidak
        # dinilai, tidak masuk EXPECTED, tidak punya audio_text sendiri —
        # jawabannya harus ada di kaiwa.audio_text yang sudah didengar).
        kqs = d['kaiwa'].get('questions', [])
        if len(kqs) > 2:
            errors.append(f'L{raw} kaiwa.questions: {len(kqs)} soal, maksimal 2')
        kseen = set()
        for i, kq in enumerate(kqs, 1):
            kid = kq['id']
            if kid != f'{prefix}-kq{i}':
                errors.append(f'{kid}: id tidak sesuai urutan (harus {prefix}-kq{i})')
            if kid in kseen:
                errors.append(f'{kid}: id ganda')
            kseen.add(kid)
            if not (0 <= kq['answer'] < len(kq['choices'])):
                errors.append(f'{kid}: answer di luar jangkauan')
            if len(set(kq['choices'])) != len(kq['choices']):
                errors.append(f'{kid}: pilihan kembar')
            if len(kq['choices']) == 2 and kq['choices'] != ['○', '×']:
                errors.append(f'{kid}: soal 2 pilihan harus ["○","×"]')
            for s in [kq['prompt']] + kq['choices']:
                lo = leftover(s)
                if lo:
                    errors.append(f'{kid}: kanji tanpa furigana {lo} di "{s}"')
            tr = kq.get('translate', {})
            if not tr.get('id') or not tr.get('en'):
                errors.append(f'{kid}: translate.id / translate.en wajib diisi')
        seen = set()
        for i, q in enumerate(qs, 1):
            qid = q['id']
            if qid != f'{prefix}-q{i}':
                errors.append(f'{qid}: id tidak sesuai urutan (harus {prefix}-q{i})')
            if qid in seen:
                errors.append(f'{qid}: id ganda')
            seen.add(qid)
            if not (0 <= q['answer'] < len(q['choices'])):
                errors.append(f'{qid}: answer di luar jangkauan')
            if len(set(q['choices'])) != len(q['choices']):
                errors.append(f'{qid}: pilihan kembar')
            if len(q['choices']) not in (2, 3, 4):
                errors.append(f'{qid}: jumlah pilihan tidak lazim')
            if len(q['choices']) == 2 and q['choices'] != ['○', '×']:
                errors.append(f'{qid}: soal 2 pilihan harus ["○","×"]')
            for t in turns(q['audio_text']):
                if t['speaker'] not in SPEAKERS:
                    errors.append(f'{qid}: speaker "{t["speaker"]}" tidak valid')
                if '《' in t['text']:
                    errors.append(f'{qid}: audio_text tidak boleh berisi 《》')
            for s in [q['prompt']] + q['choices']:
                lo = leftover(s)
                if lo:
                    errors.append(f'{qid}: kanji tanpa furigana {lo} di "{s}"')
            tr = q.get('translate', {})
            if not tr.get('id') or not tr.get('en'):
                errors.append(f'{qid}: translate.id / translate.en wajib diisi')
    print(f'{len(files)} file diperiksa.')
    for w in warns:
        print('PERINGATAN:', w)
    for e in errors:
        print('ERROR:', e)
    if errors:
        sys.exit(1)
    print('OK, tidak ada masalah.')


if __name__ == '__main__':
    main()
