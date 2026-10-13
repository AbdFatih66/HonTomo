#!/usr/bin/env python3
"""
Mengubah paket Simulasi JLPT (public/data/jlpt-mock/<id>.json) menjadi bank soal
SERVER untuk Tes JLPT: resources/lang-data/jlpt/packs/<id>/{mojigoi,bunpou_dokkai,chokai}.json

Perbedaan dengan berkas sumber:
  - satu berkas per sesi (isinya = objek `sections[i]` + `format: "mock"`);
  - `answer` jadi 1-BASED (1..4), termasuk pada `example`, supaya sama dengan
    kontrak API Tes JLPT (jawaban user dan kunci sama-sama 1..4);
  - field rahasia (answer, exp, transcript, audio_text) tetap ada di berkas ini,
    tetapi JlptTestService::publicTest membuangnya sebelum dikirim ke browser.

Pakai:
  python3 scripts/jlpt-mock-to-bank.py public/data/jlpt-mock/n5-test-1.json
  python3 scripts/jlpt-mock-to-bank.py <sumber.json> --out resources/lang-data/jlpt/packs

Setelah berhasil, HAPUS public/data/jlpt-mock/ dari server publik (kunci jawaban
ada di sana). Perintah `php artisan jlpt-mock:generate-audio` sekarang membaca bank ini.
"""
import argparse
import copy
import json
import sys
from pathlib import Path

SECTION_FILES = {'vocab': 'mojigoi', 'grammar': 'bunpou_dokkai', 'listening': 'chokai'}


def bump(node):
    """answer 0-based → 1-based pada node soal/contoh."""
    if isinstance(node, dict) and isinstance(node.get('answer'), int) and 'choices' in node:
        node['answer'] += 1


def convert_section(section, errors):
    out = copy.deepcopy(section)
    out['format'] = 'mock'
    out['section'] = SECTION_FILES[section['key']]

    for m in out['mondai']:
        if 'example' in m:
            bump(m['example'])
        for g in m['groups']:
            for q in g['questions']:
                if q.get('type') == 'star':
                    # ★ selalu kotak ketiga: kunci harus = kata di posisi ke-3 susunan benar
                    if q['answer'] != q['order'][2]:
                        errors.append(f"{q['id']}: answer {q['answer']} != order[2] {q['order'][2]}")
                if not 0 <= q['answer'] < len(q['choices']):
                    errors.append(f"{q['id']}: answer di luar jumlah pilihan")
                bump(q)
    return out


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('source')
    ap.add_argument('--out', default='resources/lang-data/jlpt/packs')
    args = ap.parse_args()

    data = json.loads(Path(args.source).read_text(encoding='utf-8'))
    errors = []
    dest = Path(args.out) / data['id']
    dest.mkdir(parents=True, exist_ok=True)

    seen = set()
    for s in data['sections']:
        conv = convert_section(s, errors)
        for m in conv['mondai']:
            for g in m['groups']:
                for q in g['questions']:
                    if q['id'] in seen:
                        errors.append(f"id ganda: {q['id']}")
                    seen.add(q['id'])
        conv['level'] = data['level']
        path = dest / f"{conv['section']}.json"
        path.write_text(json.dumps(conv, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
        n = sum(len(g['questions']) for m in conv['mondai'] for g in m['groups'])
        print(f"{path}  ({n} soal)")

    if errors:
        print('\nGAGAL:\n  ' + '\n  '.join(errors), file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
