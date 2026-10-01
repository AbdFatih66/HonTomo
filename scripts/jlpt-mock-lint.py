#!/usr/bin/env python3
"""Validasi public/data/jlpt-mock/*.json (Simulasi JLPT).

Pakai:  python3 scripts/jlpt-mock-lint.py            (dari root project)

Cek: jumlah soal per bagian/mondai (sama dengan struktur JLPT N5: 33 / 32 / 24),
id unik, answer valid, pilihan tidak kembar, kanji di teks soal sudah diberi
furigana (kecuali teks yang digarisbawahi __x__ dan pilihan kanji mondai 2),
audio_text tanpa tanda 《》, speaker valid, gambar & audio yang belum ada
(hanya peringatan). Keluar dengan kode 1 kalau ada error.
"""
import json, re, sys, glob, os

KJ = r'[\u3400-\u9FFF々〇]'
EXPECTED = {'vocab': [10, 8, 10, 5], 'grammar': [16, 5, 5, 3, 2, 1], 'listening': [7, 6, 5, 6]}
SPEAKERS = {'narrator', 'male', 'female'}
errors, warns = [], []


def leftover(s):
    s = re.sub(KJ + r'+《[^》]*》', '', s)
    s = re.sub(r'__.+?__', '', s)
    return re.findall(KJ + '+', s)


def texts(q):
    for k in ('stem', 'prefix', 'suffix'):
        if q.get(k):
            yield k, q[k]
    for c in q.get('choices', []):
        if isinstance(c, str):
            yield 'choice', c


def main():
    root = sys.argv[1] if len(sys.argv) > 1 else '.'
    files = [f for f in glob.glob(os.path.join(root, 'public/data/jlpt-mock/*.json')) if not f.endswith('index.json')]
    if not files:
        print('Tidak ada file tes.'); return 1
    for path in files:
        d = json.load(open(path, encoding='utf-8'))
        tid = d['id']
        seen = set()
        for sec in d['sections']:
            counts = []
            for m in sec['mondai']:
                qs = [q for g in m['groups'] for q in g['questions']]
                counts.append(len(qs))
                for g in m['groups']:
                    if 'passage' in g and g['passage'].get('kind') == 'text':
                        if leftover(g['passage']['text']):
                            errors.append(f"{m['id']}: kanji tanpa furigana di teks bacaan: {leftover(g['passage']['text'])[:5]}")
                items = qs + ([m['example']] if m.get('example') else [])
                for q in items:
                    qid = q['id']
                    if qid in seen: errors.append(f'{qid}: id ganda')
                    seen.add(qid)
                    n = len(q['choices'])
                    if not (0 <= q['answer'] < n): errors.append(f'{qid}: answer di luar pilihan')
                    if q['type'] == 'star':
                        if len(q['words']) != 4 or sorted(q['order']) != [0, 1, 2, 3]: errors.append(f'{qid}: star words/order tidak valid')
                        if q['answer'] != q['order'][2]: errors.append(f'{qid}: answer harus order[2] (posisi ★)')
                    strs = [c if isinstance(c, str) else c['image'] for c in q['choices']]
                    if len(set(strs)) != len(strs): errors.append(f'{qid}: pilihan kembar')
                    if not q.get('exp'): errors.append(f'{qid}: exp kosong')
                    if sec['kind'] == 'reading':
                        for kind, t in texts(q):
                            if kind == 'choice' and re.fullmatch(KJ + r'+.*', t) and sec['key'] == 'vocab' and m['id'] == 'v2':
                                continue
                            if leftover(t): errors.append(f"{qid} ({kind}): kanji tanpa furigana {leftover(t)[:4]}")
                    else:
                        for t in q['audio_text']:
                            if t['speaker'] not in SPEAKERS or '《' in t['text']: errors.append(f'{qid}: speaker/teks audio tidak valid')
                        if not q.get('hidden_choices'):
                            for c in q['choices']:
                                if isinstance(c, str) and leftover(c): errors.append(f'{qid}: pilihan tanpa furigana {leftover(c)[:3]}')
                        if q.get('audio') and not os.path.exists(os.path.join(root, 'public', q['audio'].lstrip('/'))):
                            warns.append(f"{qid}: file audio {q['audio']} belum ada")
                        if not q.get('audio'): warns.append(f'{qid}: audio belum dibuat (php artisan jlpt-mock:generate-audio)')
                    for c in q['choices']:
                        if isinstance(c, dict) and not os.path.exists(os.path.join(root, 'public', c['image'].lstrip('/'))):
                            warns.append(f"{qid}: gambar {c['image']} belum ada")
                    if isinstance(q.get('image'), dict) and not os.path.exists(os.path.join(root, 'public', q['image']['image'].lstrip('/'))):
                        warns.append(f"{qid}: gambar {q['image']['image']} belum ada")
            if counts != EXPECTED[sec['key']]:
                errors.append(f"{tid}/{sec['key']}: jumlah soal per mondai {counts}, seharusnya {EXPECTED[sec['key']]}")
        print(f'{tid}: {len(seen)} item diperiksa')
    for w in sorted(set(warns))[:400]: print('WARN', w)
    if len(warns) > 400: print(f'... (+{len(warns) - 400} peringatan lain)')
    for e in errors: print('ERROR', e)
    print(f'\n{len(errors)} error, {len(set(warns))} peringatan')
    return 1 if errors else 0


if __name__ == '__main__':
    sys.exit(main())
